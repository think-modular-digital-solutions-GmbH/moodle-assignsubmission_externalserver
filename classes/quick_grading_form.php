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
 * Quick grading form for external server submission plugin.
 *
 * @package    assignsubmission_external_server
 * @author     Stefan Weber (stefan.weber@think-modular.com)
 * @copyright  2025 think-modular
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace assignsubmission_external_server;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

use moodleform;
use assignsubmission_external_server\external_server;
use assign_submission_external_server;
use context_module;
use moodle_url;
use html_writer;

/**
 * Quick submission edit form to save one click.
 *
 * @package    assignsubmission_external_server
 * @author     Stefan Weber (stefan.weber@think-modular.com)
 * @copyright  2025 think-modular
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class quick_grading_form extends moodleform {

    /** @var assign_submission_external_server $extserver The assignment instance */
    protected $extserver;

    /** @var stdClass $assignment The assignment */
    protected $assignment;

    /** @var stdClass $submission The submission */
    protected $submission;

    /**
     * Constructor
     *
     * @param assign_submission_external_server $extserver The assignment instance
     * @param stdClass $assignment The assignment
     * @param stdClass stdClass $submission The submission
     * @param moodle_url $actionurl The form action URL
     * @param array $customdata Custom data for the form
     */
    public function __construct(assign_submission_external_server
        $extserver, $assignment, $submission, $actionurl, $customdata = null) {
        $this->extserver = $extserver;
        $this->assignment = $assignment;
        $this->submission = $submission;

        // Call parent constructor with correct arguments.
        parent::__construct($actionurl, $customdata);
    }

    /**
     * Defines the form
     *
     * @throws coding_exception
     * @throws dml_exception
     * @throws HTML_Quickform_error
     */
    public function definition() {

        global $USER;

        $mform = $this->_form;

        // Show error.
        $error = optional_param('error', null, PARAM_TEXT);
        $debug = optional_param('debug', null, PARAM_TEXT);
        if ($error) {
            $mform->addElement('html', '<div class="alert alert-danger">' .
                get_string($error, 'assignsubmission_external_server') . "<code>$debug</code></div>");
        }

        // Grading.
        $cm = $this->assignment->get_course_module();
        $context = context_module::instance($cm->id);
        if (has_capability('mod/assign:grade', $context)) {

            // Header.
            $mform->addElement('header', 'quickgradingheader', get_string('getgrades', 'assignsubmission_external_server'));

            // Info text.
            $url = new moodle_url('/mod/assign/view.php',
                ['id' => $cm->id, 'action' => 'grading']);
            $link = html_writer::link($url, get_string('gradeitem:submissions', 'assign'));
            $text = get_string('quickgradinginfo', 'assignsubmission_external_server', $link);
            $mform->addElement('html', html_writer::div($text));

            // Status selection.
            $options = [
                'all' => get_string('all', 'assignsubmission_external_server'),
                'submitted' => get_string('submitted', 'assignsubmission_external_server'),
                'ungraded' => get_string('ungraded', 'assignsubmission_external_server'),
            ];
            $mform->addElement('select', 'status', get_string('downloadgradesfor', 'assignsubmission_external_server'), $options);

            // Start grading button.
            $mform->addElement('submit', 'gradebutton', get_string('start', 'assignsubmission_external_server'));

        }

        // Preserve values as hidden fields.
        $mform->addElement('hidden', 'id', $this->assignment->get_course_module()->id);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'action', 'view');
        $mform->setType('action', PARAM_RAW);
    }

    /**
     * Returns info about a user's submission.
     *
     * @return string
     */
    private function submission_info() {

        global $CFG, $USER;

        $html = '';
        $assignment = $this->assignment->get_instance();
        if ($submission = $this->assignment->get_user_submission($USER->id, false)) {
            if ($submission->timemodified <= $assignment->duedate || empty($this->assignment->duedate)) {
                $class = 'text-success';
            } else {
                $class = 'text-danger';
            }
            $html = html_writer::span(userdate($submission->timemodified), $class);
        }

        return $html;
    }
}
