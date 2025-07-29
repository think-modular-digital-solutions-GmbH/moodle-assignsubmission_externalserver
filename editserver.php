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
 * Global logic for the external server submission plugin.
 *
 * @package    assignsubmission_external_server
 * @author     Stefan Weber (stefan.weber@think-modular.com)
 * @copyright  2025 think-modular
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../../config.php');
require_once($CFG->libdir.'/adminlib.php');

defined('MOODLE_INTERNAL') || die;

use assignsubmission_external_server\editserver_form;
use assignsubmission_external_server\external_server;
use assignsubmission_external_server\helper;

// Security.
require_login();
$context = \context_system::instance();
require_capability('moodle/site:config', $context);
if (!confirm_sesskey()) {
    throw new moodle_exception('invalidsesskey', 'error');
}

// Parameters for server and action taken.
$id      = optional_param('id', 0, PARAM_INT);
$show    = optional_param('show', '', PARAM_INT);
$hide    = optional_param('hide', '', PARAM_INT);
$delete  = optional_param('delete', '', PARAM_INT);
$confirm = optional_param('confirm', '', PARAM_BOOL);

// Set up the page.
$PAGE->set_url('/mod/assign/submission/external_server/editserver.php', ['id' => $id]);
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$title = get_string('pluginname', 'assignsubmission_external_server');
$PAGE->set_title($title);
$PAGE->set_heading($title);
$redirecturl = new moodle_url('/admin/settings.php', ['section' => 'assignsubmission_external_server']);

// Get specific server if id is set.
if ($id) {
    if (!$server = $DB->get_record('assignsubmission_external_server_servers', ['id' => $id])) {
        throw new moodle_exception('unknownserver', 'extserver');
    }
    $heading = get_string('editserver', 'assignsubmission_external_server', format_string($server->name));
} else {
    $heading = get_string('addserver', 'assignsubmission_external_server');
    $server = new stdClass();
}

// Hide a server.
if (!empty($hide)) {
    $DB->set_field('assignsubmission_external_server_servers', 'visible', '0', ['id' => $server->id]);
    redirect($redirecturl);

// Show a server.
} elseif (!empty($show)) {
    $DB->set_field('assignsubmission_external_server_servers', 'visible', '1', ['id' => $server->id]);
    redirect($redirecturl);

// Delete a server.
} elseif (!empty($delete)) {

    $assignments = helper::get_assignments_using_server($delete);

    if (!$entry = $DB->get_record('assignsubmission_external_server_servers', ['id' => $delete])) {
        throw new moodle_exception('unknownserver', 'assignsubmission_external_server');

    // Check if server is in use.
    } else if ($assignments) {
        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('error'));
        echo $OUTPUT->notification(get_string('cannotdelete', 'assignsubmission_external_server', count($assignments)), 'notifyproblem');
        echo $OUTPUT->continue_button($redirecturl);
        die();

    // Delete confirmation modal.
    } else if (!$confirm) {

        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('deleteexternalserver', 'assignsubmission_external_server', format_string($server->name)));

        $confirmurl = new moodle_url('/mod/assign/submission/external_server/editserver.php', [
            'delete' => $delete,
            'confirm' => 1,
            'sesskey' => sesskey()
        ]);
        echo $OUTPUT->confirm(get_string('confirmdeleting', 'assignsubmission_external_server',
            format_string($entry->name)), $confirmurl, $redirecturl);
        die();

    // Delete the server.
    } else {

        echo $OUTPUT->header();

        // Deleting not allowed, server already in use.
        if ($DB->count_records('assignsubmission_external_server', ['extservid' => $delete]) > 0) {
            throw new moodle_exception('delete:disabled', 'extserver');
        }

        // Delete server.
        if ($DB->delete_records('assignsubmission_external_server_servers', ['id' => $delete])) {
            echo $OUTPUT->notification(get_string('delete:success', 'assignsubmission_external_server', format_string($entry->name)),
                'notifysuccess');
        } else {
            echo $OUTPUT->notification(get_string('delete:error', 'assignsubmission_external_server', format_string($entry->name)));
        }
        echo $OUTPUT->continue_button($redirecturl);
        die();
    }
}

// Create form.
$mform = new editserver_form('editserver.php', $server);

// Cancelled.
if ($mform->is_cancelled()) {
    redirect($CFG->wwwroot . '/admin/settings.php?section=assignsubmission_external_server');

} else if ($data = $mform->get_data()) {

    // Submitted and valid.
    $newserver = new stdClass();
    $newserver->name = $data->name;
    $newserver->url = $data->url;
    $newserver->form_url = $data->form_url;
    $newserver->auth_type = $data->auth_type;
    $newserver->auth_secret = $data->auth_secret;
    $newserver->oauth2_client_id = $data->oauth2_client_id ?? '';
    $newserver->oauth2_endpoint = $data->oauth2_endpoint ?? '';
    $newserver->jwt_issuer = $data->jwt_issuer ?? '';
    $newserver->jwt_audience = $data->jwt_audience ?? '';
    $newserver->hash = $data->hash;
    $newserver->sslverification = $data->sslverification;
    $newserver->groupinfo = $data->groupinfo;
    $newserver->contact_name = $data->contact_name;
    $newserver->contact_email = $data->contact_email;
    $newserver->contact_phone = $data->contact_phone;
    $newserver->contact_org = $data->contact_org;
    $newserver->info = $data->info;

    // Update server.
    if ($id) {
        $newserver->timemodified = time();
        $newserver->id = $id;
        $DB->update_record('assignsubmission_external_server_servers', $newserver);

    } else {
        // Create a new server.
        $newserver->visible = '0';
        $newserver->timecreated = time();
        $newserver->usercreated = $USER->id;
        $newserver->timemodified = '';
        $newserver->id = $DB->insert_record('assignsubmission_external_server_servers', $newserver);
    }

    redirect($redirecturl);
}

// Page output.
echo $OUTPUT->header();
echo $OUTPUT->heading($heading);
$mform->display();
echo $OUTPUT->footer();
