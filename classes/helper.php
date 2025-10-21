<?php
// This file is part of mod_extserver for Moodle - http://moodle.org/
//
// It is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// It is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Helper methods for the external server submission plugin.
 *
 * @package    assignsubmission_externalserver
 * @author     Stefan Weber <stefan.weber@think-modular.com>
 * @copyright  2025 think-modular
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace assignsubmission_externalserver;

use html_table;
use html_writer;
use moodle_url;

/**
 * This class contains helper methods for the external server submission plugin.
 *
 * @package    assignsubmission_externalserver
 * @author     Stefan Weber <stefan.weber@think-modular.com>
 * @copyright  2025 think-modular
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class helper {
    /**
     * Returns html for the table of servers in the settings.
     *
     * @param array $servers Array of server objects to display.
     * @return string HTML string containing the server list table.
     */
    public static function print_table_of_servers($servers) {

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
            $row[] = self::edit_icons($server);
            $table->data[] = $row;
        }

        // Add the table to the HTML.
        return html_writer::table($table);
    }
    /**
     * Returns edit icons for a server in the server list.
     *
     * @param obj $server the server object containing the server details.
     * @return string HTML string containing the edit and delete icons.
     */
    public static function edit_icons($server): string {

        global $DB, $OUTPUT;
        $id = $server->id;
        $icons = [];

        // Edit.
        $url = new \moodle_url(
            '/mod/assign/submission/externalserver/editserver.php',
            ['id' => $id, 'sesskey' => sesskey()]
        );
        $icons[] = $OUTPUT->action_icon(
            $url,
            new \pix_icon('t/edit', get_string('edit'))
        );

        // Show/hide.
        if ($server->visible) {
            $url = new \moodle_url(
                '/mod/assign/submission/externalserver/editserver.php',
                ['id' => $id, 'hide' => 1, 'sesskey' => sesskey()]
            );
            $icons[] = $OUTPUT->action_icon(
                $url,
                new \pix_icon('t/hide', get_string('hide'))
            );
        } else {
            $url = new \moodle_url(
                '/mod/assign/submission/externalserver/editserver.php',
                ['id' => $id, 'show' => 1, 'sesskey' => sesskey()]
            );
            $icons[] = $OUTPUT->action_icon(
                $url,
                new \pix_icon('t/show', get_string('show'))
            );
        }

        // Check if the server is used in any assignment.
        $assignments = self::get_assignments_using_server($id);

        // Delete.
        $url = new \moodle_url(
            '/mod/assign/submission/externalserver/editserver.php',
            ['id' => $id, 'delete' => $id, 'sesskey' => sesskey()]
        );
        if ($assignments) {
            $icons[] = $OUTPUT->action_icon(
                $url,
                new \pix_icon('t/delete', get_string('delete')),
                null,
                ['class' => 'disabled'],
            );
        } else {
            $icons[] = $OUTPUT->action_icon(
                $url,
                new \pix_icon('t/delete', get_string('delete')),
            );
        }

        // Test.
        $url = new \moodle_url(
            '/mod/assign/submission/externalserver/servertest.php',
            ['id' => $id, 'sesskey' => sesskey()]
        );
        $icons[] = html_writer::link(
            $url,
            get_string('checkconnection', 'assignsubmission_externalserver')
        );

        $html = implode('&nbsp;', $icons);
        return $html;
    }

    /**
     * Get the options for number of uploads.
     *
     * @param int $submissioncount the current number of submissions
     *
     * @return array
     */
    public static function get_upload_options(int $submissioncount = 0): array {
        $maxuploads = [];
        $maxuploads[ASSIGNSUBMISSION_EXTERNALSERVER_NOUPLOADS] = get_string('nouploads', 'assignsubmission_externalserver');
        $maxuploads[ASSIGNSUBMISSION_EXTERNALSERVER_UNLIMITED] = get_string('unlimited', 'assignsubmission_externalserver');
        for ($i = $submissioncount; $i <= 100; $i++) {
            $maxuploads[$i] = $i;
        }
        return $maxuploads;
    }

    /**
     * Gets all assignments that are using a specific external server.
     *
     * @param int $id The ID of the external server.
     * @return array|false Array of assignments using the server, or false if none found
     */
    public static function get_assignments_using_server($id): array|false {
        global $DB;

        $sql = "SELECT *
                FROM {assign_plugin_config}
                WHERE plugin = :plugin
                AND subtype = :subtype
                AND name = :name
                AND " . $DB->sql_compare_text('value') . " = :value";
        $params = [
            'plugin' => 'externalserver',
            'subtype' => 'assignsubmission',
            'name' => 'server',
            'value' => (string) $id,
        ];
        return $DB->get_records_sql($sql, $params);
    }
}
