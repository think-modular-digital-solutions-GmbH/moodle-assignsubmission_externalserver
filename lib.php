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

defined('MOODLE_INTERNAL') || die();


// Unused
/** ASSIGNSUBMISSION_EXTERNAL_SERVER_COUNT_WORDS = 1 */
define('ASSIGNSUBMISSION_EXTERNAL_SERVER_COUNT_WORDS', 1);
/** ASSIGNSUBMISSION_EXTERNAL_SERVER_COUNT_LETTERS = 2 */
define('ASSIGNSUBMISSION_EXTERNAL_SERVER_COUNT_LETTERS', 2);



/** ASSIGNSUBMISSION_EXTERNAL_SERVER_EVENT_TYPE_DUE = 'due' is backwardscompatible to former events */
define('ASSIGNSUBMISSION_EXTERNAL_SERVER_EVENT_TYPE_DUE', 'due');
/** ASSIGNSUBMISSION_EXTERNAL_SERVER_EVENT_TYPE_GRADINGDUE = 'gradingdue' */
define('ASSIGNSUBMISSION_EXTERNAL_SERVER_EVENT_TYPE_GRADINGDUE', 'gradingdue');