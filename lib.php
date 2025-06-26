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

/**
 * Serves files.
 *
 * @param mixed $course course or id of the course
 * @param mixed $cm course module or id of the course module
 * @param context $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @param array $options - List of options affecting file serving.
 * @return bool false if file not found, does not return if found - just send the file
 */
function assignsubmission_external_server_pluginfile($course,
                                          $cm,
                                          context $context,
                                          $filearea,
                                          $args,
                                          $forcedownload,
                                          array $options=array()) {
    global $DB, $CFG;

    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    require_login($course, false, $cm);
    $itemid = (int)array_shift($args);
    $record = $DB->get_record('assign_submission',
                              array('id'=>$itemid),
                              'userid, assignment, groupid',
                              MUST_EXIST);
    $userid = $record->userid;
    $groupid = $record->groupid;

    require_once($CFG->dirroot . '/mod/assign/locallib.php');

    $assign = new assign($context, $cm, $course);

    if ($assign->get_instance()->id != $record->assignment) {
        return false;
    }

    if ($assign->get_instance()->teamsubmission &&
        !$assign->can_view_group_submission($groupid)) {
        return false;
    }

    if (!$assign->get_instance()->teamsubmission &&
        !$assign->can_view_submission($userid)) {
        return false;
    }

    $relativepath = implode('/', $args);

    $fullpath = "/{$context->id}/assignsubmission_external_server/$filearea/$itemid/$relativepath";

    $fs = get_file_storage();
    if (!($file = $fs->get_file_by_hash(sha1($fullpath))) || $file->is_directory()) {
        return false;
    }

    // Download MUST be forced - security!
    send_stored_file($file, 0, 0, true, $options);
}
