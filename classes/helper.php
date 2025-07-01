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
 * @package    assignsubmission_external_server
 * @author     Stefan Weber
 * @copyright  2025 think-modular
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace assignsubmission_external_server;

defined('MOODLE_INTERNAL') || die();

use html_writer;
use moodle_url;

/**
 * This class contains helper methods for the external server submission plugin.
 *
 * @package    assignsubmission_external_server
 * @author     Stefan Weber
 * @copyright  2025 think-modular
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class helper {

    /**
     * Returns edit icons for a server in the server list.
     *
     * @param obj $server the server object containing the server details.
     * @return string HTML string containing the edit and delete icons.
     */
    public static function edit_icons($server) {

        global $DB, $OUTPUT;
        $id = $server->id;
        $icons = [];

        // Edit.
        $url = new \moodle_url('/mod/assign/submission/external_server/editserver.php', ['id' => $id, 'sesskey' => sesskey()]);
        $icons[] = $OUTPUT->action_icon($url, new \pix_icon('t/edit', get_string('edit')));

        // Show/hide.
        if ($server->visible) {
            $url =  new \moodle_url('/mod/assign/submission/external_server/editserver.php', ['id' => $id, 'hide' => 1, 'sesskey' => sesskey()]);
            $icons[] = $OUTPUT->action_icon($url, new \pix_icon('t/hide', get_string('hide')));
        } else {
            $url =  new \moodle_url('/mod/assign/submission/external_server/editserver.php', ['id' => $id, 'show' => 1, 'sesskey' => sesskey()]);
            $icons[] = $OUTPUT->action_icon($url, new \pix_icon('t/show', get_string('show')));
        }

        // Check if the server is used in any assignment.
        $assignments = self::get_assignments_using_server($id);

        // Delete.
        if ($assignments) {
            $icons[] = $OUTPUT->pix_icon('t/delete', get_string('cannotdelete', 'assignsubmission_external_server', count($assignments)), '', ['class' => 'iconsmall icon-disabled']);
        } else {
            $url = new \moodle_url('/mod/assign/submission/external_server/editserver.php', ['id' => $id, 'delete' => $id, 'sesskey' => sesskey()]);
            $icons[] = $OUTPUT->action_icon($url, new \pix_icon('t/delete', get_string('delete')));
        }

        // Test.
        $url = new \moodle_url('/mod/assign/submission/external_server/servertest.php', ['id' => $id, 'sesskey' => sesskey()]);
        $icons[] = html_writer::link(
            $url,
            get_string('checkconnection', 'assignsubmission_external_server')
        );

        $html = implode('&nbsp;', $icons);
        return $html;
    }

    /**
     * Helper function for getting the outcomes of user, if any.
     * This was mostly created for code maintenance, to reduce the dublicated
     * codelines here. Still a lot to do.
     *
     * @param bool $usesoutcomes tells whether or not the activity is set to use outcomes
     * @param class $gradinginfo info on the grading of the user
     * @param array $auser user info from the database
     * @param bool $quickgrade whether or not quickgrading is active
     * @param int $tabindex used for indexing the quickgrading fields so they can be tabbed in the right order
     * @return string $outcomes indicates the relevant outcome
     */
    public static function mod_extserver_get_outcomes($usesoutcomes, $gradinginfo, $auser, $quickgrade, &$tabindex) {
        $outcomes = '';
        if ($usesoutcomes) {
            foreach ($gradinginfo->outcomes as $n => $outcome) {
                $outcomes .= '<div class="outcome"><label>'.$outcome->name.'</label>';
                $options = make_grades_menu(-$outcome->scaleid);

                if ($outcome->grades[$auser->id]->locked || !$quickgrade) {
                    $options[0] = get_string('nooutcome', 'grades');
                    $outcomes .= ': <span id="outcome_'.$n.'_'.$auser->id.'">'.
                            $options[$outcome->grades[$auser->id]->grade].'</span>';
                } else {
                    $attributes = [];
                    $attributes['tabindex'] = $tabindex++;
                    $attributes['id'] = 'outcome_'.$n.'_'.$auser->id;
                    $outcomes .= ' '.html_writer::select($options, 'outcome_'.$n.'['.$auser->id.']',
                            $outcome->grades[$auser->id]->grade,
                            [0 => get_string('nooutcome', 'grades')], $attributes);
                }
                $outcomes .= '</div>';
            }
        }
        return $outcomes;
    }

    /**
     * Helper function for setting the grades and calling an event on success.
     * This was mostly created for code maintenance, to reduce the dublicated
     * codelines here. Still a lot to do.
     *
     * @param stdClass $grades the grades to be set on user basis
     * @param class $submission the submission being graded
     * @param array $curgrade grades for the current user
     * @param time $timemarked the time the grading was marked
     * @return void
     */
    public static function set_grading_successful(&$grades, &$submission, $curgrade, $timemarked) {
        global $DB, $PAGE;
        $grades[$curgrade['userid']]->userid = $curgrade['userid'];
        $grades[$curgrade['userid']]->rawgrade = $curgrade['grade'];
        $grades[$curgrade['userid']]->dategraded = $timemarked;
        $mailinfo = get_user_preferences('extserver_mailinfo', 0);
        if (!$mailinfo) {
            $submission->mailed = 1; // Treat as already mailed.
        } else {
            $submission->mailed = 0; // Make sure mail goes out (again, even).
        }

        // Don't update these.
            unset($submission->data1);
            unset($submission->data2);

        $DB->update_record('extserver_submissions', $submission);
        $event = \mod_extserver\event\submission_graded::create([
                'objectid' => $PAGE->cm->instance,
                'context' => $PAGE->context,
        ]);
        $event->add_record_snapshot('course', $PAGE->course);
        $event->trigger();
    }

    /**
     * Get the options for number of uploads.
     *
     * @param int $submissioncount the current number of submissions
     *
     * @return array
     */
    public static function get_upload_options($submissioncount = 0) {
        $maxuploads = [];
        $maxuploads[ASSIGNSUBMISSION_EXTERNAL_SERVER_NOUPLOADS] = get_string('nouploads', 'assignsubmission_external_server');
        $maxuploads[ASSIGNSUBMISSION_EXTERNAL_SERVER_UNLIMITED] = get_string('unlimited', 'assignsubmission_external_server');
        for ($i = 100; $i > $submissioncount; $i--) {
            $maxuploads[$i] = $i;
        }
        return $maxuploads;
    }

    /**
     * Gets all assignments that are using a specific external server.
     *
     * @param int $id The ID of the external server.
     * @return array|false Array of assignments using the server, or false if none found
     */
    public static function get_assignments_using_server($id) {
        global $DB;

        $sql = "SELECT *
                FROM {assign_plugin_config}
                WHERE plugin = :plugin
                AND subtype = :subtype
                AND name = :name
                AND " . $DB->sql_compare_text('value') . " = :value";
        $params = [
            'plugin' => 'external_server',
            'subtype' => 'assignsubmission',
            'name' => 'server',
            'value' => (string) $id
        ];
        return $DB->get_records_sql($sql, $params);
    }
}