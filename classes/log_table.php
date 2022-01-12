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
 * Archiver plugin table class.
 *
 * @package     local_archiver
 * @copyright   2021 Bryce Yoder
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

class log_table extends \table_sql {

    function __construct($uniqueid) {
        parent::__construct($uniqueid);
        // Define the list of columns to show.
        $columns = array('status', 'courses', 'time', 'message');
        $this->define_columns($columns);

        // Define the titles of columns to show in header.
        $headers = array('Status', 'Courses backed up', 'Time completed', 'Message');
        $this->define_headers($headers);
    }

    public function col_time($task) {
        return date("Y-m-d H:i:s", $task->time);
    }

    public function other_cols($colname, $task) {
        // For security reasons we don't want to show the password hash.
        if ($colname == 'status') {
            $fa_icon = 'fa-check';
            $icon_color = 'green';
            if ($task->message !== 'Success.') {
                $fa_icon = 'fa-times';
                $icon_color = 'red';
            }
            return "<i class='icon fa $fa_icon fa-fw' style='color:$icon_color;'></i>";
        }
    }

    public static function get_current_tasks() {
        global $DB;

        $tasks = $DB->get_records_sql("
            SELECT id, customdata, timecreated
            FROM {task_adhoc}
            WHERE classname LIKE '%adhoc_archive_task%'"
        );
        return $tasks;
        return array_values($tasks);
    }

}