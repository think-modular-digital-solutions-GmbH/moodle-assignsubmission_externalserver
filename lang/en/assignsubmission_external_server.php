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
$string['pluginname'] = 'Submission to external server';
$string['privacy:metadata'] = 'The Submission to external server plugin does not store any personal data.';

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
$string['file_uploaded'] = 'File uploaded successfully to external server.';
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
$string['server:auth_oauth2'] = 'OAuth2';
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
$string['server:oauth2_endpoint'] = 'OAuth2 endpoint';
$string['server:oauth2_client_id'] = 'OAuth2 client ID';
$string['server:jwt_issuer'] = 'JWT issuer';
$string['server:jwt_audience'] = 'JWT audience';

// Servertest.
$string['checkconnection'] = 'Check connection';
$string['testing'] = 'Testing external server "{$a->name}" on {$a->site}';
$string['createassignment'] = 'Create assignment';
$string['studentview'] = 'Student view';
$string['teacherview'] = 'Teacher view';
$string['loadgrades'] = 'Load grades';
$string['sslerror'] = 'The connection has been shut down before any output was made.
Maybe there is a problem with the SSL certificate.
Please install the trusted CA\'s certificates on your server!';
$string['unknownerror'] = 'HTTP error code {$a}. Please check the server settings and try again.';

// Assignment form.
$string['enabled'] = 'Enabled';
$string['enabled_help'] = 'Participants can send a submission to a preconfigured external server that sends back a response.';
$string['externalserver'] = 'Server';
$string['externalserver_help'] = 'Select the external server to use for this assignment. External servers can be configured in the site administration.';
$string['selectserver'] = ' --- select external server ---';
$string['submissionswarning'] = 'There are already {$a} submission(s) for this assignment. Some settings may not be changed anymore.';

// Other new.
$string['unlimiteduploads'] = 'unlimited';
$string['connectionstatus'] = 'Connection to external server';
$string['quickupload'] = 'Quick upload';
$string['quickupload_help'] = 'Upload a new file to the external server directly. Will still count as an upload attempt.';
$string['upload'] = 'Upload';
$string['needselectfile'] = 'Please provide a valid file to upload.';
$string['lastupload'] = 'Last upload';
$string['getgrades'] = 'Get grades and feedback from external server';
$string['externalservertitle'] = 'External server "{$a}"';
$string['uploadattempts'] = 'Uploads';
$string['viewsubmissionat'] = 'View submission';
$string['quickgradinginfo'] = '<ul>
    <li>You can update the grades and feedback for all participants with a specific status at once.</li>
    <li>You can update individual participants on the {$a} page.</li>
    <li>Existing grades and feedback will be overwritten.</li></ul>';
$string['start'] = 'Start';
$string['downloadgradesfor'] = 'Download grades for';
$string['all'] = 'all participants';
$string['ungraded'] = 'all ungraded';
$string['submitted'] = 'all submitted';
$string['confirmgrading'] = 'Load grades and feedback for <strong>{$a->for}</strong> from the external server <strong>{$a->server}</strong>. <p><strong>Existing grades and feedback will be overwritten!</strong></p>';
$string['nonnumericgrade'] = 'Assignment needs to be set to a numeric grade. Please check the grading settings.';
$string['nothingtograde'] = 'No submissions found - nothing to grade.';
$string['couldnotgetgrades'] = 'Could not get grades from external server. Please check the server settings and try again.';
$string['gradesupdated'] = 'Grades updated successfully. {$a} grade(s) updated.';
$string['nouploads'] = 'No uploads';
$string['unlimited'] = 'Unlimited';
$string['nouploadsleft'] = 'You do not have any uploads left for this assignment.';
$string['cannotdelete'] = 'This server cannot be deleted, it is in use in {$a} assignment(s).';
$string['noneselected'] = 'No external server selected. Please select an external server in the settings to use for this assignment.';
$string['noneselectedstudent'] = 'No external server has been selected by the teacher. Please contact your teacher to configure an external server for this assignment.';

// Errors.
$string['error:couldnotgetjwttoken'] = 'Could not get JWT token from external server. HTTP status code: {$a}. Please check the server settings and try again.';
$string['error:couldnotgetoauth2token'] = 'Could not get OAuth2 token from external server. HTTP status code: {$a}. Please check the server settings and try again.';
$string['error:requestfailed'] = 'Request to external server failed. HTTP status code: {$a}. Please check the server settings and try again.';

$string['expandresponse'] = 'View the response from the external server';