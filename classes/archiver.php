<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Archiver plugin archive form.
 *
 * @package     local_archiver
 * @copyright   2021 Moonami
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;
require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
require_once($CFG->dirroot . '/backup/moodle2/backup_plan_builder.class.php');

/**
 * Controller class for starting backup jobs
 */
class archive_controller {

    private $tasktype;
    private $categoryid;

    /**
     * @param int $categoryid The category that we want to back up
     * @param string $tasktype Is this a cron run or an adhoc job? (scheduled|adhoc)
     */
    public function __construct($categoryid, $tasktype) {
        $this->tasktype = $tasktype;
        $this->categoryid = $categoryid;

        if ($tasktype == 'adhoc') {
            $this->run_adhoc_task();
        }
    }

    /**
     * Run an adhoc task
     */
    public function run_adhoc_task() {
        $courses = $this->get_courses_in_category();
        $archivetask = new adhoc_archive_task($courses);
        $archivetask->execute();
    }

    /**
     * Get all the courses in a category
     * @return array $courses The list of courses in the category
     */
    private function get_courses_in_category() {
        $cat = core_course_category::get($this->categoryid);
        $courses = $cat->get_courses();
        return $courses;
    }
}

/**
 * The adhoc flavor of the archive task
 */
class adhoc_archive_task extends \core\task\adhoc_task {

    private $courses;
    private $tempfolderdest;

    /**
     * @param array $courses The courses that we are going to archive in this task
     */
    public function __construct($courses) {
        global $CFG;

        $this->courses = $courses;
        $this->tempfolderdest = $CFG->dataroot . '/archiver-backup-' . date('Ymdhms');
    }

    /**
     * Run the archival job
     */
    public function execute() {
        foreach ($this->courses as $course) {
            $this->make_backup($course->id);
            $fileinfo = $this->get_file_info_from_db($course->id);
            $this->move_file($fileinfo);
        }

        $this->zip_and_delete_temp_dir();
        $this->upload_via_sftp_and_delete_zip();
    }

    /**
     * Make a backup of a course
     * @param int $courseid The ID of the course we want to back up
     * @return bool
     */
    private function make_backup($courseid) {
        $bc = new backup_controller(backup::TYPE_1COURSE,
            $courseid,
            backup::FORMAT_MOODLE,
            backup::INTERACTIVE_NO,
            backup::MODE_GENERAL,
            2);

        $bc->execute_plan();
        $bc->destroy();
        $bc = null;
        return true;
    }

    /**
     * Get the information for the backup file from the database
     * @param int $courseid The ID of the course we want to get the backup file for
     * @return object $fileinfo
     */
    private function get_file_info_from_db($courseid) {
        global $DB;

        $fileinfo = $DB->get_record_SQL("
            SELECT
                mf.contextid,
                mf.component,
                mf.filearea,
                mf.itemid,
                mf.filepath,
                TRIM(BOTH FROM mf.filename) AS filename,
                mf.contenthash
            FROM
                {files} mf
                INNER JOIN {context} mctx ON (mf.contextid = mctx.id)
                INNER JOIN {course} mc ON (mctx.instanceid = mc.id)
            WHERE
                (mc.id = :id)
                AND (mf.component = 'backup')
                AND (mf.filename LIKE '%.mbz')",
            ['id' => $courseid]
        );
        return $fileinfo;
    }

    /**
     * Move the backup file to a temp directory in moodledata
     * @param object $fileinfo The file information
     */
    private function move_file($fileinfo) {
        $fs = get_file_storage();
        $file = $fs->get_file(
            $fileinfo->contextid,
            $fileinfo->component,
            $fileinfo->filearea,
            $fileinfo->itemid,
            $fileinfo->filepath,
            $fileinfo->filename
        );

        if (!file_exists($this->tempfolderdest)) {
            mkdir($this->tempfolderdest);
        }
        $file->copy_content_to($this->tempfolderdest . '/' . $fileinfo->contextid . $file->get_filename());
    }

    /**
     * Zip the temp folder and delete the folder
     */
    private function zip_and_delete_temp_dir() {
        $zipname = $this->tempfolderdest . '.zip';
        $zip = new ZipArchive();
        $mbzfiles = glob($this->tempfolderdest . '/*.mbz');

        if ($zip->open($zipname, ZipArchive::CREATE) === true) {
            foreach ($mbzfiles as $mbzfile) {
                $downloadfile = file_get_contents($mbzfile);
                $zip->addFromString(basename($mbzfile), $downloadfile);
                unlink($mbzfile);
            }
            $zip->close();
        }

        rmdir($this->tempfolderdest);
    }

    /**
     * Upload the .zip file via SFTP and then delete the .zip file
     */
    private function upload_via_sftp_and_delete_zip() {
        set_include_path(__DIR__ . '/../lib/phpseclib');
        require_once('Net/SFTP.php');

        $sftphostname = get_config('local_archiver', 'sftphostname');
        $sftpusername = get_config('local_archiver', 'sftpusername');
        $sftppassword = get_config('local_archiver', 'sftppassword');

        $sftpport = get_config('local_archiver', 'sftpport');
        if ($sftpport === '') {
            $sftpport = 22;
        }

        $filename = $this->tempfolderdest . '.zip';

        // TODO: Move connection check to beginning so we don't waste resources on a big job before discovering we can't upload.
        $sftp = new Net_SFTP($sftphostname, $sftpport);
        if (!$sftp->login($sftpusername, $sftppassword)) {
            unlink($this->tempfolderdest . '.zip');
            throw new Exception('Login failed! Please check your SFTP credentials.');
        }
        $sftp->put('archiver-backup-' . date('Ymdhms') .'.zip', $filename, NET_SFTP_LOCAL_FILE);
        unlink($this->tempfolderdest . '.zip');
    }

}

