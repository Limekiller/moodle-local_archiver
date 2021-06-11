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

require_once($CFG->libdir . "/formslib.php");

class archive_form extends moodleform {

    // Add elements to form.
    public function definition() {
        global $CFG;

        $mform = $this->_form;

        $options = core_course_category::make_categories_list();
        $mform->addElement('select', 'categoryid', get_string('coursecategory'), $options);
        $mform->setDefault('categoryid', 1);
        $mform->setType('categoryid', PARAM_INT);

        $this->add_action_buttons();
    }

    // Custom validation should be added here.
    public function validation($data, $files) {
        return array();
    }
}