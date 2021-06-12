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

use local_archiver\archive_controller;
use local_archiver\archive_form;

require_login();
require_capability('moodle/site:config', context_system::instance());

$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/local/archiver/index.php'));
$PAGE->set_title(get_string('pluginname', 'local_archiver'));
$PAGE->set_heading(get_string('pluginname', 'local_archiver'));

echo $OUTPUT->header();

$mform = new archive_form();
if ($mform->is_cancelled()) {
    $mform->display();
} else if ($fromform = $mform->get_data()) {
    $formdata = $mform->get_data();
    $controller = new archive_controller($formdata->categoryid, 'adhoc');

    redirect('/local/archiver/jobs.php', 'Your backup is being created!', null, \core\output\notification::NOTIFY_SUCCESS);

} else {
    $mform->display();
}

echo $OUTPUT->footer();