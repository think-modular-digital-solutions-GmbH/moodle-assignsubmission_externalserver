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
 * Show response from external server.
 *
 * @package    assignsubmission_externalserver
 * @author     Stefan Weber <stefan.weber@think-modular.com>
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

// Objects.
$cm = get_coursemodule_from_id('assign', $cmid, 0, false, MUST_EXIST);
$course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
$context = context_module::instance($cmid);
$assignment = new assign($context, $cm, $course);
$extserver = $assignment->get_plugin_by_type('assignsubmission', 'externalserver')->get_externalserver();

// Permission check.
require_login();
require_capability('mod/assign:grade', context_module::instance($cmid));

// Get user.
$user = core_user::get_user($userid, '*', MUST_EXIST);
$fullname = fullname($user);

// Setup page.
$title = get_string('showresponsetitle', 'assignsubmission_externalserver', $fullname);
$PAGE->set_cm($cm, $course);
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/mod/assign/submission/externalserver/showresponse.php'));
$PAGE->set_title($title);
$PAGE->set_heading($title);
echo $OUTPUT->header();
echo $OUTPUT->heading($title);
echo html_writer::tag('hr', '');

// Show teacher view.
$extviewurl = $extserver->build_teacherview($assignment->get_instance(), $user->username);
$result = $extserver->http_request([], 'GET', $extviewurl);
echo "<pre>$result</pre>";

echo $OUTPUT->footer();
