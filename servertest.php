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
 * Settings for the external server submission plugin.
 *
 * @package    assignsubmission_external_server
 * @author     Stefan Weber (stefan.weber@think-modular.com)
 * @copyright  2025 think-modular
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../../config.php');

defined('MOODLE_INTERNAL') || die;

use assignsubmission_external_server\external_server;

// Capability check.
require_login();
require_capability('moodle/site:config', context_system::instance());
require_sesskey();

// Get server.
$id = required_param('id', PARAM_INT); // Server ID.
$server = $DB->get_record('assignsubmission_external_server_servers', ['id' => $id]);
if (!$server) {
    throw new moodle_exception('unknownserver', 'assignsubmission_external_server');
    redirect('servers.php');
}
$extserver = new external_server($id);

// Set up the page.
$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url($CFG->wwwroot.'/mod/assign/submission/external_server/servertest.php', ['id' => $id, 'sesskey' => sesskey()]));
$PAGE->set_heading(get_string('testing', 'assignsubmission_external_server',
    ['name' => $extserver->obj->name, 'site' => $SITE->fullname]));
$PAGE->requires->css(new moodle_url('/mod/assign/submission/external_server/styles.css'));

// Start output.
echo $OUTPUT->header();

// Check Connection.
$result = $extserver->check_connection();
$content = $extserver->get_debuginfo();
$extserver->print_response(get_string('checkconnection', 'assignsubmission_external_server'), $content, $result, $extserver);

// Create Assignment.
$assignment = new stdClass();
$assignment->id = 1;
$assignment->name = 'ExternalServerTest';
$assignment->course = '0';

// Student View.
$extviewurl = $extserver->url_studentview($assignment);
$result = $extserver->http_request([], 'GET', $extviewurl);
$extserver->print_response(get_string('studentview', 'assignsubmission_external_server'),
    $result, $extserver->get_httpcode(), $extserver);

// Submit file.
$tmpfilename = 'uploadtest.zip';
$tmpfilepath = $CFG->dirroot . '/mod/assign/submission/external_server/fixtures/'  . $tmpfilename;
$fs = get_file_storage();
$fileinfo = [
    'contextid' => context_system::instance()->id,
    'component' => 'assignsubmission_extserver',
    'filearea'  => 'submission_files',
    'itemid'    => 0,
    'filepath'  => '/',
    'filename'  => $tmpfilename,
];
$file = $fs->get_file(
    $fileinfo['contextid'],
    $fileinfo['component'],
    $fileinfo['filearea'],
    $fileinfo['itemid'],
    $fileinfo['filepath'],
    $fileinfo['filename']
);
if (!$file) {
    $file = $fs->create_file_from_pathname($fileinfo, $tmpfilepath);
}
$result = $extserver->upload_file($file, $assignment, false);
$content = $extserver->get_debuginfo();
$extserver->print_response(get_string('submit'), $content, $result, $extserver);

// Teacher View.
$extviewurl = $extserver->build_teacherview($assignment, '');
$result = $extserver->http_request([], 'GET', $extviewurl);
$extserver->print_response(get_string('studentview', 'assignsubmission_external_server'),
    $result, $extserver->get_httpcode(), $extserver);

// Get grades.
list($testuser, $params) = $DB->get_in_or_equal([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);
$userlist = $DB->get_fieldset_select('user', 'username', " id ".$testuser, $params);
$res = $extserver->load_grades($assignment, $userlist);
$content = $extserver->get_debuginfo();
if ($extserver->get_httpcode() == 501) {
    $result = true;
} else if ($extserver->get_httpcode() == 200) {
    $result = $res;
} else {
    $result = false;
}
echo "<pre>";
var_dump($content);
die();
if ($content && !empty($content)) {
    $pretty = new DOMDocument();
    $pretty->preserveWhiteSpace = false;
    $pretty->loadXML($content);
    $pretty->formatOutput = true;
    $content = '<code>' . htmlentities($pretty->saveXML(), ENT_COMPAT) . '</code>';
}
$extserver->print_response(get_string('loadgrades', 'assignsubmission_external_server'), $content, $result, $extserver);

// Link back.
echo html_writer::link(
    new moodle_url('/admin/settings.php', ['section' => 'assignsubmission_external_server']),
    get_string('back'),
    ['class' => 'btn btn-primary']
);

echo $OUTPUT->footer();