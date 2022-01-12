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
 * Manage OAuth consent
 * 
 * @package   local_archiver
 * @copyright 2022 Bryce Yoder
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../config.php');

use local_archiver\google_oauth_manager;

require_login();
require_capability('moodle/site:config', context_system::instance());

$client = google_oauth_manager::get_client($code=$_GET['code'], $fail_on_error=false);
redirect('/admin/settings.php?section=managearchiver');

// $PAGE->set_context(context_system::instance());
// $PAGE->set_url(new moodle_url('/local/archiver/index.php'));
// $PAGE->set_title(get_string('pluginname', 'local_archiver'));
// $PAGE->set_heading(get_string('pluginname', 'local_archiver'));

// $PAGE->navbar->add(get_string('archivecourses', 'local_archiver'), new moodle_url('/local/archiver/index.php'));