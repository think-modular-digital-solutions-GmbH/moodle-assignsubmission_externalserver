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
 * Upgrade hooks for the external server submission plugin.
 *
 * @package    assignsubmission_external_server
 * @author     Stefan Weber (stefan.weber@think-modular.com)
 * @copyright  2025 think-modular
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
function xmldb_assignsubmission_external_server_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    // Add OAuth2 client ID, endpoint and JWT issuer fields.
    if ($oldversion < 2025072803) {

        $table = new xmldb_table('assignsubmission_external_server_servers');

        $field = new xmldb_field('oauth2_client_id', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'auth_secret');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('oauth2_endpoint', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'oauth2_client_id');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('jwt_issuer', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'oauth2_endpoint');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2025072803, 'assignsubmission', 'external_server');
    }

    // Add JWT audience field.
    if ($oldversion < 2025072804) {

        $table = new xmldb_table('assignsubmission_external_server_servers');

        $field = new xmldb_field('jwt_audience', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'jwt_issuer');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2025072804, 'assignsubmission', 'external_server');
    }

    return true;
}
