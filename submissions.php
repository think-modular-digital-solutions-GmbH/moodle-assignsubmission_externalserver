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
 * @package    assignsubmission_external_server
 * @author     Stefan Weber (stefan.weber@think-modular.com)
 * @copyright  2025 think-modular
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once('lib.php');
require_once('locallib.php');
require_once($CFG->libdir.'/plagiarismlib.php');

$id   = optional_param('id', 0, PARAM_INT);          // Course module ID.
$a    = optional_param('a', 0, PARAM_INT);           // Assignment ID.
$mode = optional_param('mode', 'all', PARAM_ALPHA);  // What mode are we in?
$download = optional_param('download' , 'none', PARAM_ALPHA); // ZIP download asked for?
$redirect = optional_param('redirect', false, PARAM_BOOL);

$url = new moodle_url('/mod/extserver/submissions.php');
if ($id) {
    if (! $cm = get_coursemodule_from_id('extserver', $id)) {
        throw new moodle_exception('invalidcoursemodule');
    }

    if (! $assignment = $DB->get_record('extserver', ['id' => $cm->instance])) {
        throw new moodle_exception('invalidid', 'extserver');
    }

    if (! $course = $DB->get_record('course', ['id' => $assignment->course])) {
        throw new moodle_exception('coursemisconf', 'assignment');
    }
    $url->param('id', $id);
} else {
    if (!$assignment = $DB->get_record('extserver', ['id' => $a])) {
        throw new moodle_exception('invalidcoursemodule');
    }
    if (! $course = $DB->get_record('course', ['id' => $assignment->course])) {
        throw new moodle_exception('coursemisconf', 'extserver');
    }
    if (! $cm = get_coursemodule_from_instance('extserver', $assignment->id, $course->id)) {
        throw new moodle_exception('invalidcoursemodule');
    }
    $url->param('a', $a);
}

if ($mode !== 'all') {
    $url->param('mode', $mode);
}
$PAGE->set_url($url);
require_login($course->id, false, $cm);

require_capability('mod/extserver:grade', context_module::instance($cm->id));

// Load up the required assignment code.
$instance = new extserver($cm->id, $assignment, $cm, $course);

if ($redirect) {
    $instance->redirect_studentview();
} else if ($download == 'zip') {
    $instance->download_submissions();
} else {
    $instance->submissions($mode);   // Display or process the submissions.
}
