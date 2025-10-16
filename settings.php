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
 * @package    assignsubmission_externalserver
 * @author     Stefan Weber (stefan.weber@think-modular.com)
 * @copyright  2025 think-modular
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/mod/assign/submission/externalserver/locallib.php');

use assignsubmission_externalserver\helper;

// Default settings header.
$settings->add(new admin_setting_heading('default_settings_header',
    get_string('defaultsettings', 'assignsubmission_externalserver'),
    get_string('defaultsettings_help', 'assignsubmission_externalserver')
));

// Maximum size setting.
$settings->add(new admin_setting_configselect('assignsubmission_externalserver/maxbytes',
    get_string('maxbytes', 'assignsubmission_externalserver'),
    get_string('maxbytes_help', 'assignsubmission_externalserver'),
    1048576,
    get_max_upload_sizes($CFG->maxbytes)
));

// File types setting.
$settings->add(new admin_setting_filetypes('assignsubmission_externalserver/filetypes',
    new lang_string('filetypes', 'assignsubmission_externalserver'),
    new lang_string('filetypes_help', 'assignsubmission_externalserver'),
    ''
));

// Maximum uploads setting.
$options = helper::get_upload_options();
$settings->add(new admin_setting_configselect('assignsubmission_externalserver/uploads',
    get_string('uploads', 'assignsubmission_externalserver'),
    get_string('uploads_help', 'assignsubmission_externalserver'),
    100,
    $options
));

// Add server button.
$html = html_writer::link(
            new moodle_url('/mod/assign/submission/externalserver/editserver.php', ['sesskey' => sesskey()]),
            get_string('addserver', 'assignsubmission_externalserver'),
            ['class' => 'btn btn-primary mt-2']
);

// Get the existing servers.
$servers = $DB->get_records('assignsubmission_externalserver_servers', [], 'name ASC');

// Server list.
if (!$servers) {
    $html .= html_writer::div(
        get_string('noservers', 'assignsubmission_externalserver'),
        'alert alert-info'
    );
} else {

    // Create table.
    $table = new html_table();
    $table->head = [
        get_string('server:name', 'assignsubmission_externalserver'),
        get_string('server:contact_name', 'assignsubmission_externalserver'),
        get_string('actions'),
    ];
    $table->attributes['class'] = 'generaltable';
    $table->id = 'externalserver-list';
    $table->data = [];

    // Add each server to the table.
    foreach ($servers as $server) {
        if (!$server->visible) {
            $rowclass = 'text-muted';
        } else {
            $rowclass = '';
        }

        $row = [];
        $row[] = '<span class="' . $rowclass . '">' . format_string($server->name) . '</span>';
        $row[] = '<span class="' . $rowclass . '">' . format_string($server->contact_name) . '</span>';
        $row[] = helper::edit_icons($server);
        $table->data[] = $row;
    }

    // Add the table to the HTML.
    $html .= html_writer::table($table);
}

// Servers header including the list.
$settings->add(new admin_setting_heading('servers_header',
    get_string('servers', 'assignsubmission_externalserver'),
    $html
));
