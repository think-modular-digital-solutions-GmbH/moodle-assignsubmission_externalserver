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
 * Language strings for the external server submission plugin.
 *
 * @package    assignsubmission_external_server
 * @author     Stefan Weber (stefan.weber@think-modular.com)
 * @copyright  2025 think-modular
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Basics.
$string['pluginname'] = 'External server';
$string['privacy:metadata'] = 'The External server submission plugin does not store any personal data.';

// Strings.
$string['defaultsettings'] = 'Default settings';
$string['defaultsettings_help'] = 'Defaults that can be changed in every instance.';
$string['maxbytes'] = 'Maximum size';
$string['maxbytes_help'] = 'Maximum assignment size for all assignments on the site (subject to course limits and other local settings)';
$string['servers'] = 'Servers';
$string['addserver'] = 'Add external server';
$string['editserver'] = 'Edit external server "{$a}"';
$string['noservers'] = 'No external servers configured';
$string['filetypes'] = 'Allowed file types';
$string['filetypes_help'] = 'Accepted file types can be restricted by entering a list of file extensions. If the field is left empty, then all file types are allowed.';
$string['uploads'] = 'Number of uploads';
$string['uploads_help'] = 'Defines how many times a student can upload a file. Only the last uploaded file will be saved on the Moodle server';
$string['addexternalserver'] = 'Add external server';
$string['editexternalserver'] = 'Edit external server "{$a}"';
$string['deleteexternalserver'] = 'Delete external server "{$a}"';
$string['confirmdeleting'] = 'Are you sure you want to delete the external server "{$a}"? This action cannot be undone.';
$string['unknownserver'] = 'Invalid server ID';
$string['delete:disabled'] = 'Server is in use and cannot be deleted';
$string['delete:success'] = 'Server deleted successdeletingfully';
$string['delete:error'] = 'Error deleting server';

// Server form.
$string['server:name'] = 'Name';
$string['server:name_help'] = 'This name will be displayed for this external server.';
$string['server:name_missing'] = 'You have to enter a name for the external server.';
$string['server:name_duplicate'] = 'This name is already used';
$string['server:url'] = 'URL';
$string['server:url_help'] = 'Enter the URL to the external Server. For example: http://yoursite.com/moodle_external_assignment.php';
$string['server:url_invalid'] = 'The URL of the external server is invalid.';
$string['server:form_url'] = 'Upload URL';
$string['server:form_url_help'] = 'Enter the URL to the File-Upload Script. For example: http://yoursite.com/moodle_external_assignment_upload.php';
$string['server:auth_type'] = 'Authentification type';
$string['server:auth_type_help'] = 'Select the authentification type for the external server.';
$string['server:auth_api_key'] = 'API key';
$string['server:auth_jwt'] = 'JWT';
$string['server:auth_oauth2'] = 'OAUTH2';
$string['server:auth_secret'] = 'Key/secret:';
$string['server:auth_secret_help'] = 'Depending on the authentification type, you may have to enter a secret key or token here. This is used to verify the authenticity of requests from Moodle to the external server.';
$string['server:contact'] = 'Contact';
$string['server:contact_name'] = 'Contact name';
$string['server:contact_name_help'] = 'Enter the name of the contact.';
$string['server:contact_email'] = 'E-Mail';
$string['server:contact_email_help'] = 'Enter the E-Mail adress of the contact.';
$string['server:contact_email_invalid'] = 'The contact E-Mail address is invalid.';
$string['server:contact_phone'] = 'Telephone number';
$string['server:contact_phone_help'] = 'Enter the telephone number of the contact.';
$string['server:contact_org'] = 'Organization';
$string['server:contact_org_help'] = 'Enter the name of the organization.';
$string['server:info'] = 'Comment';
$string['server:info_help'] = 'If you have any comment, please enter it here.';
$string['server:hashalgorithm'] = 'Hash algorithm';
$string['server:hashalgorithm_help'] = 'Choose the correct hash algorithm for the external server, it has to be available on both systems. Don\'t change this, if you don\'t know, what you\'re doing. Pre Moodle 3.1 external servers used "sha1" by default. We now switched to "sha256" by default.';
$string['server:sslverification'] = 'Verify SSL-Certificates/Identities';
$string['server:sslverification_help'] = 'Controls the SSL-Certitifacte verification for external server connections.<ul><li><strong>Yes, verify identity</strong> - the host\'s name will be checked against host certificate\'s hostname. The Peer-Certificate will be verified.</li><li><strong>Yes, if name is existent</strong> - the host certificate will be checked for an available name entry. The peer certificate will be verified.</li><li><strong>No verification</strong> - none of the certificates will be verified.</li></ul>';
$string['server:sslverification_identity'] = 'Yes, verify identity';
$string['server:sslverification_ifnameexists'] = 'Yes, if name exists';
$string['server:sslverification_none'] = 'No verification';
$string['server:groupinfo'] = 'Group informations';
$string['server:groupinfo_help'] = 'Setting defines if external server requires group information to work properly. Set to \'required\' if the external server needs group information. Set to \'not required\' if group information is not used.';
$string['server:groupinfo_not_needed'] = 'not required';
$string['server:groupinfo_must_be_sent'] = 'required';

// Servertest.
$string['checkconnection'] = 'Check Connection';
$string['createassignment'] = 'Create Assignment';
$string['studentview'] = 'Student View';
$string['teacherview'] = 'Teacher View';
$string['loadgrades'] = 'Load Grades';