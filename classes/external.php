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
 * External service used by AJAX.
 *
 * @package    assignsubmission_external_server
 * @author     Stefan Weber
 * @copyright  2025 think-modular
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace assignsubmission_external_server;

defined('MOODLE_INTERNAL') || die();
require_once("$CFG->libdir/externallib.php");

use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;

class external extends external_api {

    public static function set_toggle_state_parameters() {
        return new external_function_parameters([
            'state' => new external_value(PARAM_BOOL, 'Expanded state'),
        ]);
    }

    public static function set_toggle_state($state) {
        global $USER;
        set_user_preference('assignsubmission_external_server_expanded', $state, $USER);
        return ['status' => true];
    }

    public static function set_toggle_state_returns() {
        return new external_single_structure([
            'status' => new external_value(PARAM_BOOL),
        ]);
    }
}
