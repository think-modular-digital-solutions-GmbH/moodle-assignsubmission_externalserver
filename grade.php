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
 * Grade submissions from external server.
 *
 * @package    assignsubmission_externalserver
 * @author     Stefan Weber (stefan.weber@think-modular.com)
 * @copyright  2025 think-modular
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../../../config.php');
require_once($CFG->dirroot . '/mod/assign/locallib.php');

defined('MOODLE_INTERNAL') || die;

use assignsubmission_externalserver\externalserver;
use core_user;

// Params.
$cmid = required_param('cmid', PARAM_INT);
$userid = required_param('userid', PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_BOOL);

// Objects.
$cm = get_coursemodule_from_id('assign', $cmid, 0, false, MUST_EXIST);
$course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
$context = context_module::instance($cmid);
$assignment = new assign($context, $cm, $course);
$ext = $assignment->get_plugin_by_type('assignsubmission', 'externalserver')->get_externalserver();

// Permission check.
require_login();
require_capability('mod/assign:grade', context_module::instance($cmid));

// Setup page.
$PAGE->set_cm($cm, $course);
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/mod/assign/submission/externalserver/grade.php', ['status' => $status]));
$PAGE->set_title('Confirm grading');
$PAGE->set_heading('Confirm grading');
echo $OUTPUT->header();

// Confirmed - fetch grades from server.
if ($confirm) {
    $result = $ext->grade_submissions($assignment, [$userid]);
    echo $OUTPUT->notification($result['message'], $result['status']);
    echo html_writer::link(
        new moodle_url(
            '/mod/assign/view.php',
            ['id' => $cmid, 'action' => 'grading']),
            get_string('continue'),
            ['class' => 'btn btn-primary']
    );
} else {
    // Confirmation urls.
    $yesurl = new moodle_url('/mod/assign/submission/externalserver/grade.php', [
        'cmid' => $cmid,
        'userid' => $userid,
        'confirm' => 1,
        'sesskey' => sesskey(),
    ]);
    $nourl = new moodle_url('/mod/assign/view.php', ['id' => $cmid, 'action' => 'view']);

    // Get string for whom the action will be performed.
    $user = core_user::get_user($userid);

    // Show confirmation dialog.
    echo $OUTPUT->confirm(
        get_string('confirmgrading', 'assignsubmission_externalserver', ['for' => fullname($user), 'server' => $ext->obj->name]),
        $yesurl,
        $nourl
    );
}

echo $OUTPUT->footer();
