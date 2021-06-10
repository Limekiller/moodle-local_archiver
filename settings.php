<?php
// This file is part of Moodle - https://moodle.org/
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
 * Adds admin settings for the plugin.
 *
 * @package     local_archiver
 * @copyright   2021 Moonami
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $ADMIN->add('localplugins', new admin_category('local_archiver_settings', get_string('pluginname', 'local_archiver')));
    $settingspage = new admin_settingpage('managelocalarchiver', get_string('manage', 'local_archiver'));

    if ($ADMIN->fulltree) {

        $archivemethodoptions = [
            'SFTP'
        ];
        $settingspage->add(new admin_setting_configselect(
            'local_archiver/archive_method',
            get_string('archivemethod', 'local_archiver'),
            '',
            'SFTP',
            $archivemethodoptions
        ));

        $settingspage->add(new admin_setting_heading(
            'local_archiver/SFTP',
            get_string('sftpheading', 'local_archiver'),
            ''
        ));
        $settingspage->add(new admin_setting_configtext(
            'local_archiver/sftphostname',
            get_string('sftphostname', 'local_archiver'),
            '',
            ''
        ));
        $settingspage->add(new admin_setting_configtext(
            'local_archiver/sftpusername',
            get_string('sftpusername', 'local_archiver'),
            '',
            ''
        ));
        $settingspage->add(new admin_setting_configpasswordunmask(
            'local_archiver/sftppassword',
            get_string('sftppassword', 'local_archiver'),
            '',
            ''
        ));
        $settingspage->add(new admin_setting_configtext(
            'local_archiver/sftpport',
            get_string('sftpport', 'local_archiver'),
            '',
            '22'
        ));
    }

    $ADMIN->add('localplugins', $settingspage);
}