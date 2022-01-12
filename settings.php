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
 * @copyright   2021 Bryce Yoder
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use local_archiver\google_oauth_manager;

if ($hassiteconfig) {
    $ADMIN->add('localplugins', new admin_category('archiversettings', get_string('pluginname', 'local_archiver')));
    $settingspage = new admin_settingpage('managearchiver', get_string('manage', 'local_archiver'));

    $saved_token = google_oauth_manager::fetch_access_token();
    $google_oauth_access_token = json_decode($saved_token, true)['access_token'];
    if (!$saved_token) {
        $google_oauth_access_token = 'Not set';
    }

    if ($ADMIN->fulltree) {

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

        $settingspage->add(new admin_setting_heading(
            'local_archiver/drive',
            get_string('googledriveheading', 'local_archiver'),
            ''
        ));

        $settingspage->add(new admin_setting_configtext(
            'local_archiver/googleoauthclientid',
            get_string('googleoauthclientid', 'local_archiver'),
            '',
            ''
        ));

        $settingspage->add(new admin_setting_configpasswordunmask(
            'local_archiver/googleoauthclientsecret',
            get_string('googleoauthclientsecret', 'local_archiver'),
            '',
            ''
        ));

        $settingspage->add(new admin_setting_configempty(
            'local_archiver/googleoauthaccesstoken',
            get_string('generategoogleoauthaccesstoken', 'local_archiver', $CFG->wwwroot),
            '',
            ''
        ));

        $settingspage->add(new admin_setting_description(
            'local_archiver/viewgoogleoauthaccesstoken',
            get_string('googleoauthaccesstoken', 'local_archiver', $google_oauth_access_token),
            ''
        ));
    }

    $ADMIN->add('localplugins', $settingspage);

    $ADMIN->add(
        'courses',
        new admin_externalpage(
            'archiver',
            get_string('archivecourses', 'local_archiver'),
            new \moodle_url('/local/archiver')
        )
    );

    $ADMIN->add(
        'localplugins',
        new admin_externalpage(
            'archiverjobs',
            get_string('logs', 'local_archiver'),
            new \moodle_url('/local/archiver/log.php')
        )
    );
}