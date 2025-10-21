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
 * @package    assignsubmission_externalserver
 * @author     Stefan Weber <stefan.weber@think-modular.com>
 * @copyright  2025 think-modular
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Upgrade code for the external server submission plugin.
 *
 * @param int $oldversion the version we are upgrading from
 * @return bool true if success
 * @throws dml_exception
 */
function xmldb_assignsubmission_externalserver_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();


    if ($oldversion < 2025102001) {

        // Add fields for OAuth2 client secret, auth endpoint and token endpoint.
        $table = new xmldb_table('assignsubmission_externalserver_servers');

        $field = new xmldb_field('oauth2_client_secret', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'oauth2_client_id');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('oauth2_auth_endpoint', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'oauth2_client_secret');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('oauth2_token_endpoint', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'oauth2_auth_endpoint');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Remove obsolete fields for jwt_issuer and jwt_audience.
        $field = new xmldb_field('jwt_issuer');
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }
        $field = new xmldb_field('jwt_audience');
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2025102001, 'assignsubmission', 'externalserver');

    }

    return true;
}
