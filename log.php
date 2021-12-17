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
require "$CFG->libdir/tablelib.php";

require_login();
require_capability('moodle/site:config', context_system::instance());

$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/local/archiver/log.php'));
$PAGE->set_title(get_string('pluginname', 'local_archiver'));
$PAGE->set_heading(get_string('pluginname', 'local_archiver'));

$PAGE->navbar->add(get_string('archivecourses', 'local_archiver'), new moodle_url('/local/archiver/index.php'));
$PAGE->navbar->add(get_string('logs', 'local_archiver'), new moodle_url('/local/archiver/log.php'));

echo $OUTPUT->header();

$inprogresstasks = \local_archiver\log_table::get_current_tasks();

if ($inprogresstasks) {
    echo html_writer::tag('h2', 'In progress jobs');
    $current_jobs_table = new html_table();
    $current_jobs_table->head = ['Courses backed up', 'Time started'];
    foreach ($inprogresstasks as $task) {
        $customdata = json_decode($task->customdata, true);
        $courses = $customdata['courses'];
        $current_jobs_table->data[] = [$courses, date("Y-m-d H:i:s", $task->timecreated)];
    }

    echo html_writer::table($current_jobs_table);
    echo '<br /><br />';
}

echo html_writer::tag('h2', 'Completed jobs');
$table = new \local_archiver\log_table('uniqueid');
$table->set_sql('*', "{archiver_log}", '1=1');
$table->define_baseurl("$CFG->wwwroot/local/archiver/log.php");
$table->out(10, true);

echo $OUTPUT->footer();