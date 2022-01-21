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
 * Language strings for the plugin.
 *
 * @package   local_archiver
 * @copyright 2021 Bryce Yoder
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Archiver';
$string['manage'] = 'Manage Archiver';
$string['archivecourses'] = 'Archive courses';
$string['logs'] = 'Archiver logs';

$string['archivemethod'] = 'Archival method';
$string['sftpheading'] = 'SFTP Settings';
$string['sftphostname'] = 'Hostname';
$string['sftpusername'] = 'Username';
$string['sftppassword'] = 'Password';
$string['sftpport'] = 'Port';
$string['googledriveheading'] = 'Google Drive settings';
$string['googleoauthclientid'] = 'Client ID';
$string['googleoauthclientsecret'] = 'Client Secret';
$string['googleoauthaccesstoken'] = '<br />Google OAuth access token';
$string['generategoogleoauthaccesstoken'] = '
    <br />
    <a href="{$a}/local/archiver/oauth.php" class="btn btn-primary">Click here to authorize Google</a>
    <br /><br />
    <i class="icon fa fa-exclamation-circle fa-fw" style="color:red;"></i>
    <h6 style="font-size:12px;display:inline;">
        You must add <br /><span style="font-weight:bold;">{$a}/local/archiver/oauth.php</span><br /> as an authorized redirect URI in your OAuth client settings
    </h6>
';
$string['archivetype'] = 'Archival method';
$string['courseselectiontype'] = 'Course selection type';
$string['coursecategory'] = 'Course category';
$string['matchingstring'] = 'String to match';
$string['date'] = 'Date';
$string['dateoperator'] = 'Operator';

$string['nocourseerror1'] = 'No courses were found from the given criteria! ';
$string['nocourseerror2'] = 'Try using fuzzy matching by adding a % before and after the course name. (Ex: %Chemistry%)';
$string['sftperror'] = 'Could not connect to SFTP server. Please check your SFTP credentials.';
$string['googleoautherror'] = 'Google access token does not exist or could not be refreshed. Please re-grant account access in plugin settings.';