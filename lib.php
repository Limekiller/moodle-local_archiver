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
 * Archiver lib
 *
 * @package    local_archiver
 * @copyright  2022 Bryce Yoder <me@bryceyoder.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Given a list of course IDs, return a list of course titles
 *
 * @param int[] $courses List of course IDs
 * @return string[] List of course titles
 */
function local_archiver_get_course_name_array($courses) {
    $course_names = [];
    foreach ($courses as $course) {
        $name = local_archiver_get_course_name($course);
        if ($name !== NULL) {
            array_push($course_names, $name);
        }
    }
    return $course_names;
}

/**
 * Given a course ID, return the title of the course
 *
 * @param int $id Internal course ID
 * @return string The title of the course
 */
function local_archiver_get_course_name($id) {
    global $DB;

    $course = $DB->get_record('course', ['id' => $id]);
    return $course->fullname;
}
