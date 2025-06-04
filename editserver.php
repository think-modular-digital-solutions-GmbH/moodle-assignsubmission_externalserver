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

require_login();
require_capability('moodle/site:config', context_system::instance());

$context = null;
$PAGE->set_context($context);

$id = optional_param('id', 0, PARAM_INT);
$PAGE->set_url('/mod/extserver/editserver.php', ['id' => $id]);

if (!empty($id)) {
    admin_externalpage_setup('modextserver'.$id);
} else {
    admin_externalpage_setup('modextserver_add');
}

$strtitle = get_string('modulename', 'extserver');

$show    = optional_param('show', '', PARAM_INT);
$hide    = optional_param('hide', '', PARAM_INT);
$delete  = optional_param('delete', '', PARAM_INT);
$confirm = optional_param('confirm', '', PARAM_BOOL);

// If data submitted, then process and store.

if (!empty($hide) && confirm_sesskey()) {

    if (!$server = $DB->get_record('extserver_servers', ['id' => $hide])) {
        throw new moodle_exception('unknownserver', 'extserver');
    }

    $DB->set_field('extserver_servers', 'visible', '0', ['id' => $server->id]);
    redirect($CFG->wwwroot . '/admin/settings.php?section=modsettingextserver');
}

if (!empty($show) && confirm_sesskey()) {
    if (!$server = $DB->get_record('extserver_servers', ['id' => $show])) {
        throw new moodle_exception('unknownserver', 'extserver');
    }

    $DB->set_field('extserver_servers', 'visible', '1', ['id' => $server->id]);
    redirect($CFG->wwwroot . '/admin/settings.php?section=modsettingextserver');
}

if (!empty($delete) && confirm_sesskey()) {
    echo $OUTPUT->header();
    echo $OUTPUT->heading($strtitle);

    $entry = $DB->get_record('extserver_servers', ['id' => $delete]);

    if (!$entry) {
        throw new moodle_exception('unknownserver', 'extserver');

    } else if (!$confirm) {

        echo $OUTPUT->confirm(get_string('confirmdeleting', 'extserver', format_string($entry->service_name)),
                'editserver.php?delete=' . $delete . '&confirm=1', '../../admin/settings.php?section=modsettingextserver');
        echo $OUTPUT->footer();
        exit;

    } else {

        if ($DB->count_records('extserver', ['extservid' => $delete]) > 0) {
            // Deleteing not allowed, server already in use.
            throw new moodle_exception('deletedisabled', 'extserver');
        }

        // Delete server.
        $ret = $DB->delete_records('extserver_servers', ['id' => $delete]);

        if ($ret) {
            echo $OUTPUT->notification(get_string('successdeleting', 'extserver', format_string($entry->service_name)),
                    'notifysuccess');
        } else {
            echo $OUTPUT->notification(get_string('errordeleteing', 'extserver', format_string($entry->service_name)));
        }
        echo $OUTPUT->continue_button('../../admin/settings.php?section=modsettingextserver');
        echo $OUTPUT->footer();
        exit;
    }
}

$server = new stdClass();

if ($id && isset($_GET['id'])) {
    // Edit existing connection.
    if (!$server = $DB->get_record('extserver_servers', ['id' => $id])) {
        throw new moodle_exception('unknownserver', 'extserver');
    }
}

// Preperation for editor.
if (!isset($server->server_info)) {
    $server->server_info = '';
}
$server->server_infoformat = '1';

$editorcontext = context_system::instance();
$editoroptions = [
    'maxfiles' => EDITOR_UNLIMITED_FILES, 'maxbytes' => $CFG->maxbytes,
    'trusttext' => true, 'context' => $editorcontext,
];

$server = file_prepare_standard_editor($server, 'server_info', $editoroptions, $editorcontext, 'coursecat', 'server_info', 0);

$mform = new editserver_form('editserver.php', compact('server', 'editoroptions'));

if ($mform->is_cancelled()) {
    // Canceled.
    redirect($CFG->wwwroot . '/admin/settings.php?section=modsettingextserver');

} else if ($data = $mform->get_data()) {
    // Submitted and valid.
    $newserver = new stdClass();
    $newserver->service_name = $data->servicename;
    $newserver->server_url = $data->serverurl;
    $newserver->serverform_url = $data->serverformurl;
    $newserver->server_secret = $data->serversecret;
    $newserver->hash = $data->hash;
    $newserver->sslverification = $data->sslverification;
    $newserver->groupinfo = $data->groupinfo;
    $newserver->contact_name = $data->contactname;
    $newserver->contact_email = $data->contactemail;
    $newserver->contact_phone = $data->contactphone;
    $newserver->contact_org = $data->contactorg;
    $newserver->server_info = $data->server_info_editor['text'];

    if ($id) {
        $newserver->timemodified = time();
        $newserver->id = $id;
        $DB->update_record('extserver_servers', $newserver);

    } else {
        // Create a new server.
        $newserver->visible = '0';
        $newserver->timecreated = time();
        $newserver->usercreated = $USER->id;
        $newserver->timemodified = '';

        $newserver->id = $DB->insert_record('extserver_servers', $newserver);
    }

    redirect($CFG->wwwroot . '/admin/settings.php?section=modsettingextserver');
}

echo $OUTPUT->header();
echo $OUTPUT->heading($strtitle);

echo $OUTPUT->box(get_string('configwarning', 'extserver'));

$mform->display();
echo $OUTPUT->footer();
