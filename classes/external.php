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
 * @package    assignsubmission_externalserver
 * @author     Stefan Weber
 * @copyright  2025 think-modular
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace assignsubmission_externalserver;

defined('MOODLE_INTERNAL') || die();
require_once("$CFG->libdir/externallib.php");

use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;

/**
 * External service used by AJAX.
 *
 * @package    assignsubmission_externalserver
 * @author     Stefan Weber
 * @copyright  2025 think-modular
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class external extends external_api {

    /**
     * Returns the parameters for the set_toggle_state function.
     *
     * @return external_function_parameters
     */
    public static function set_toggle_state_parameters(): external_function_parameters {
        return new external_function_parameters([
            'state' => new external_value(PARAM_BOOL, 'Expanded state'),
        ]);
    }

    /**
     * Set the expanded/collapsed state of the submission area toggle.
     *
     * @param bool $state The new state
     * @return array status
     */
    public static function set_toggle_state($state): array {
        global $USER;
        set_user_preference('assignsubmission_externalserver_expanded', $state, $USER);
        return ['status' => true];
    }

    /**
     * Returns the description of the return value of the set_toggle_state function.
     *
     * @return external_description
     */
    public static function set_toggle_state_returns(): external_single_structure {
        return new external_single_structure([
            'status' => new external_value(PARAM_BOOL),
        ]);
    }
}
