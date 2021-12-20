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

defined('MOODLE_INTERNAL') || die;

use core_course_category;
use local_archiver\adhoc_archive_task;

/**
 * Controller class for starting backup jobs
 */
class archive_controller {

    private $tasktype;
    private $data;

    /**
     * @param int $selectiontype The method for selecting courses to backup (category|matchingstring)
     * @param mixed $criteria The criteria to use to select courses
     * @param string $tasktype Is this a cron run or an adhoc job? (scheduled|adhoc)
     */
    public function __construct($data, $tasktype) {
        $this->tasktype = $tasktype;
        $this->data = $data;

        if ($tasktype == 'adhoc') {
            $this->run_adhoc_task();
        }
    }

    /**
     * Run an adhoc task
     */
    public function run_adhoc_task() {
        if ($this->data->selectiontype === 'category') {
            $coursearray = $this->get_courses_in_category($this->data->categorycriteria);
        } elseif ($this->data->selectiontype === 'matchingstring') {
            $coursearray = $this->get_courses_by_string($this->data->matchingstringcriteria);
        } else {
            $coursearray = $this->get_courses_by_date($this->data->datecriteria, $this->data->dateoperator);
        }

        // Throw an error if no courses were found
        if (count($coursearray) === 0) {
            $errormessage = get_string('nocourseerror1', 'local_archiver');
            if ($this->data->selectiontype === 'matchingstring') {
                $errormessage .= get_string('nocourseerror2', 'local_archiver');
            }
            throw new \moodle_exception(
                'nocoursesfound', 
                'local_archiver',
                '', '',
                $errormessage
            );
        }

        $archivetask = new adhoc_archive_task($coursearray);
        $archivetask->set_custom_data([
            'courses' => json_encode($coursearray)
        ]);
        \core\task\manager::queue_adhoc_task($archivetask);
    }

    /**
     * Get all the courses in a category
     * @param int $categoryid The ID of the category we want to get courses from
     * @return array $coursearray The list of courses in the category
     */
    private function get_courses_in_category($categoryid) {
        $cat = core_course_category::get($categoryid);
        $courses = $cat->get_courses();
        $coursearray = [];
        foreach ($courses as $course) {
            array_push($coursearray, $course->id);
        }
        return $coursearray;
    }

    /**
     * Get all courses matching a SQL-syntax string
     * @param string $matchingstring The string to search for
     * @return array $coursearray The list of courses matching that string
     */
    private function get_courses_by_string($matchingstring) {
        global $DB;

        $courses = $DB->get_records_select(
            'course', 
            "fullname like :matchingstring", 
            ['matchingstring' => $matchingstring]
        );

        $coursearray = [];
        foreach ($courses as $course) {
            array_push($coursearray, $course->id);
        }
        return $coursearray;
    }

    /**
     * Get all courses before or after a given date
     * @param string $matchingstring The string to search for
     * @return array $coursearray The list of courses matching that string
     */
    private function get_courses_by_date($date, $dateoperator) {
        global $DB;

        $courses = $DB->get_records_select(
            'course', 
            "timecreated $dateoperator :matchingstring AND id != 1", 
            ['matchingstring' => $date]
        );

        $coursearray = [];
        foreach ($courses as $course) {
            array_push($coursearray, $course->id);
        }
        return $coursearray;
    }
}