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
 * @copyright   2021 Moonami
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

use core_course_category;
use local_archiver\adhoc_archive_task;

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

        $coursearray = [];
        foreach ($courses as $course) {
            array_push($coursearray, $course->id);
        }

        $archivetask->set_custom_data([
            'courses' => json_encode($coursearray)
        ]);
        \core\task\manager::queue_adhoc_task($archivetask);
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