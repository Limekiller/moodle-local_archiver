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
 *
 * @package   local_archiver
 * @copyright 2021 Moonami
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../config.php');

require_login();
require_capability('moodle/site:config', context_system::instance());

$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/local/archiver/jobs.php'));
$PAGE->set_title(get_string('pluginname', 'local_archiver'));
$PAGE->set_heading(get_string('pluginname', 'local_archiver'));

echo $OUTPUT->header();

$inprogresstasks = \local_archiver\tasks_table::get_current_tasks();
$tasks = \local_archiver\tasks_table::get_previous_tasks();

if ($inprogresstasks) {
    echo html_writer::tag('h2', 'In progress jobs');
    echo "
    <table class='archiver'>
        <thead>
            <tr>
                <th>Courses backed up</th>
                <th>Time started</th>
            </tr>
        </thead>
        <tbody>";
            foreach ($inprogresstasks as $task) {
                $customdata = json_decode($task->customdata, true);
                $courses = $customdata['courses'];
                echo "
                <tr>
                    <td>$courses</td>
                    <td>" . date("Y-m-d H:i:s", $task->timecreated) . "</td>
                </tr>";
            }
        echo "
        </tbody>
    </table>";
    echo html_writer::tag('br', '');
}

echo html_writer::tag('h2', 'Previous jobs');
echo "
<table class='archiver'>
    <thead>
        <tr>
            <th>Status</th>
            <th>Courses backed up</th>
            <th>Archive type</th>
            <th>Time completed</th>
            <th>Message</th>
        </tr>
    </thead>
    <tbody>";
        foreach ($tasks as $task) {
            $pix_icon_vals = $task->message == 'Success.' ? ['i/valid', 'Success'] : ['i/invalid', 'Failure'];
            echo "
            <tr>
                <td>".$OUTPUT->pix_icon($pix_icon_vals[0], $pix_icon_vals[1])."</td>
                <td>$task->courses</td>
                <td>$task->type</td>
                <td>" . date("Y-m-d H:i:s", $task->time) . "</td>
                <td>$task->message</td>
            </tr>";
        }
    echo "
    </tbody>
</table>";

echo $OUTPUT->footer();