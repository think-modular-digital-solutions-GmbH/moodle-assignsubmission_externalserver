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
 * Behat tests for the external server submission plugin.
 *
 * @package    assignsubmission_external_server
 * @author     Stefan Weber (stefan.weber@think-modular.com)
 * @copyright  2025 think-modular
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(__DIR__ . '/../../../../../../lib/behat/behat_base.php');

/**
 * Behat steps for the external server submission plugin.
 *
 * @package    assignsubmission_external_server
 * @author     Stefan Weber (stefan.weber@think-modular.com)
 * @copyright  2025 think-modular
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
class behat_assignsubmission_external_server extends behat_base {

    /**
     * Adds an external server entry pointing to the demopackage.
     *
     * @Given I add an external server pointing to this Moodle site
     */
    public function i_add_external_server_pointing_to_this_site(): void {
        global $CFG, $DB;
        $url = "https://moodle-4.think-modular.com/moodle-5.0/mod/assign/submission/external_server/tests/demo/moodle_external_assignment.php";
        $formurl = "https://moodle-4.think-modular.com/moodle-5.0/mod/assign/submission/external_server/tests/demo/moodle_external_assignment_upload.php";
        $record = (object)[
            'name' => 'behat_test',
            'url' => $url,
            'form_url' => $formurl,
            'auth_type' => 'api_key',
            'auth_secret' => '2345678987654',
            'hash' => 'sha256',
            'sslverification' => 2,
            'groupinfo' => 1,
            'timecreated' => time(),
            'usercreated' => 1,
        ];
        $DB->insert_record('assignsubmission_external_server_servers', $record);
    }
}
