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

namespace local_archiver;

/**
 * Archiver plugin archive form.
 *
 * @package     local_archiver
 * @copyright   2021 Bryce Yoder
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
require_once($CFG->dirroot . '/backup/moodle2/backup_plan_builder.class.php');
require_once($CFG->dirroot . '/course/externallib.php');

use local_archiver\google_oauth_manager;

/**
 * The adhoc flavor of the archive task
 */
class adhoc_archive_task extends \core\task\adhoc_task {

    private $courses;
    private $tempfolderdest;
    private $archivetype;

    /**
     * Run the archival job
     */
    public function execute() {
        global $CFG, $DB;

        $data = $this->get_custom_data();
        $this->courses = json_decode($data->courses, true); // An array of course ids
        $this->tempfolderdest = $CFG->dataroot . '/archiver-backup-' . date('YmdHis');
        $this->archivetype = $data->archivetype;

        foreach ($this->courses as $course) {
            try {
                $this->make_backup($course);
                $fileinfo = $this->get_file_info_from_db($course);
                $this->move_file($fileinfo, $course);
            } catch (\Exception $e) {
                # Skip this course for archival.
                # TODO: Log error to some persistent log
                continue;
            }
        }

        try {
            $this->zip_and_delete_temp_dir();

            // Dynamically call upload method based on archive type
            $upload_method = "upload_via_$this->archivetype" . "_and_delete_zip";
            $this->$upload_method();

            $this->log_job_in_db('Success.');
            \core_course_external::delete_courses($this->courses);
        } catch (\Exception $e) {
            unlink($this->tempfolderdest . '.zip');
            $DB->delete_records('task_adhoc', ['id' => $this->get_id()]);
            $this->log_job_in_db($e->getMessage());
        }
    }

    /**
     * Make a backup of a course
     * @param int $courseid The ID of the course we want to back up
     * @return bool
     */
    private function make_backup($courseid) {

        $bc = new \backup_controller(\backup::TYPE_1COURSE,
            $courseid,
            \backup::FORMAT_MOODLE,
            \backup::INTERACTIVE_NO,
            \backup::MODE_GENERAL,
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
    private function move_file($fileinfo, $courseid) {
        global $DB;

        $courseinfo = $DB->get_record('course', ['id' => $courseid]);
        $filename = "$courseid-$courseinfo->fullname.mbz";

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
        $file->copy_content_to("$this->tempfolderdest/$filename");
    }

    /**
     * Zip the temp folder and delete the folder
     */
    private function zip_and_delete_temp_dir() {
        $zipname = $this->tempfolderdest . '.zip';
        $zip = new \ZipArchive();
        $mbzfiles = glob($this->tempfolderdest . '/*.mbz');

        if ($zip->open($zipname, \ZipArchive::CREATE) === true) {
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
        global $DB;
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
        $sftp = new \Net_SFTP($sftphostname, $sftpport);
        if (!$sftp->login($sftpusername, $sftppassword)) {
            throw new \Exception(get_string('sftperror', 'local_archiver'));
        }
        $sftp->put('archiver-backup-' . date('YmdHis') .'.zip', $filename, NET_SFTP_LOCAL_FILE);
        unlink($this->tempfolderdest . '.zip');
    }

    private function upload_via_drive_and_delete_zip() {
        require __DIR__ . '/../lib/google-api-php-client/vendor/autoload.php';
        $client = google_oauth_manager::get_client();

        $filename = $this->tempfolderdest . '.zip';
        $service = new \Google_Service_Drive($client);
        $fileMetadata = new \Google_Service_Drive_DriveFile(
            array('name' => 'archiver-backup-' . date('YmdHis'))
        );
        $content = file_get_contents($filename);
        $mimeType = mime_content_type($filename);
        $file = $service->files->create($fileMetadata, array(
            'data' => $content,
            'mimeType' => $mimeType,
            'fields' => 'id'));

        unlink($this->tempfolderdest . '.zip');
    }

    private function log_job_in_db($message) {
        global $DB;

        $job = new \stdClass();
        $job->courses = json_encode($this->courses);
        $job->type = 'adhoc';
        $job->time = time();
        $job->message = $message;
        $DB->insert_record('archiver_log', $job);
    }

}