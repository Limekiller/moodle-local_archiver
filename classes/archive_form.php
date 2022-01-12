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

require_once($CFG->libdir . "/formslib.php");
use moodleform;
use core_course_category;

class archive_form extends moodleform {

    // Add elements to form.
    public function definition() {
        global $CFG;

        $mform = $this->_form;
        $archivetype = optional_param('archivetype',  'category',  PARAM_TEXT);
        $mform->addElement('select', 'archivetype', get_string('archivetype', 'local_archiver'), [
            'sftp' => 'SFTP',
            'drive' => 'Google Drive',
        ]);
        $mform->setDefault('archivetype', $courseselectiontype);
        $mform->setType('archivetype', PARAM_TEXT);

        $courseselectiontype = optional_param('courseselectiontype',  'category',  PARAM_TEXT);
        $mform->addElement('select', 'selectiontype', get_string('courseselectiontype', 'local_archiver'), [
            'category' => 'Category',
            'matchingstring' => 'Matching String',
            'date' => 'Date'
        ]);
        $mform->setDefault('selectiontype', $courseselectiontype);
        $mform->setType('selectiontype', PARAM_TEXT);

        // Each selection type corresponds to an input criteria
        // Input criteria MUST be named like `$selectiontype . 'criteria'`
        // See examples below
        $options = core_course_category::make_categories_list();
        $mform->addElement('select', 'categorycriteria', get_string('coursecategory', 'local_archiver'), $options);
        $mform->setDefault('criteria', 1);
        $mform->setType('criteria', PARAM_INT);
        $mform->hideIf('categorycriteria', 'selectiontype', 'neq', 'category');

        $mform->addElement('text', 'matchingstringcriteria', get_string('matchingstring', 'local_archiver'), []);
        $mform->setType('matchingstringcriteria', PARAM_TEXT);
        $mform->hideIf('matchingstringcriteria', 'selectiontype', 'neq', 'matchingstring');

        $mform->addElement('date_selector', 'datecriteria', get_string('date', 'local_archiver'));
        $mform->setType('datecriteria', PARAM_TEXT);
        $mform->hideIf('datecriteria', 'selectiontype', 'neq', 'date');
        $mform->addElement('select', 'dateoperator', get_string('dateoperator', 'local_archiver'), [
            '<' => '<',
            '>' => '>'
        ]);
        $mform->setType('dateoperator', PARAM_RAW);
        $mform->hideIf('dateoperator', 'selectiontype', 'neq', 'date');

        $this->add_action_buttons();
    }

    // Custom validation should be added here.
    public function validation($data, $files) {
        return array();
    }
}