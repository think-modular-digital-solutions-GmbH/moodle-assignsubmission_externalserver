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

defined('MOODLE_INTERNAL') || die();

// Constants.
define('ASSIGNSUBMISSION_EXTERNAL_SERVER_FILEAREA', 'submission_external_server');
define('ASSIGNSUBMISSION_EXTERNAL_SERVER_UNLIMITED', '0');
define('ASSIGNSUBMISSION_EXTERNAL_SERVER_NOUPLOADS', -1);
define('ASSIGNSUBMISSION_EXTERNAL_SERVER_SETTINGS', ['server', 'maxbytes', 'filetypes', 'uploads']);

use assignsubmission_external_server\helper;
use assignsubmission_external_server\external_server;
use assignsubmission_external_server\quick_edit_form;

/**
 * Library class for external server submission plugin
 *
 * @package    assignsubmission_external_server
 * @author     Stefan Weber (stefan.weber@think-modular.com)
 * @copyright  2025 think-modular
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class assign_submission_external_server extends assign_submission_plugin {

    /**
     * Get the name of the submission plugin
     * @return string
     */
    public function get_name() {
        return get_string('pluginname', 'assignsubmission_external_server');
    }

    /**
     * Get submission information from the database
     *
     * @param int $submissionid
     * @return mixed
     */
    private function get_file_submission($submissionid) {
        global $DB;
        return $DB->get_record('assignsubmission_external_server', array('submission'=>$submissionid));
    }

    /**
     * Get the default setting for exernal server submission plugin
     *
     * @param MoodleQuickForm $mform The form to add elements to
     * @return void
     */
    public function get_settings(MoodleQuickForm $mform) {
        global $CFG, $DB, $OUTPUT;

        // Get instance settings or default settings.
        foreach (ASSIGNSUBMISSION_EXTERNAL_SERVER_SETTINGS as $setting) {
            if ($this->assignment->has_instance()) {
                $$setting = $this->get_config($setting);
            } else {
                $$setting = get_config('assignsubmission_external_server', $setting);
            }
        }
        $filetypes = (string)$filetypes;

        // External server.
        $name = get_string('externalserver', 'assignsubmission_external_server');
        $submissioncount = $this->count_submissions();
        if ($submissioncount > 0) {
            $visible = []; // If there are already submissions, include all the servers, so that now-invisible still show as selected.
        } else {
            $visible = ['visible' => 1];
        }
        $servers = $DB->get_records('assignsubmission_external_server_servers', $visible,  'name ASC');
        foreach ($servers as $ser) {
            $options[$ser->id] = format_string($ser->name);
        }
        if (empty($options)) {
            $options[''] = get_string('noservers', 'assignsubmission_external_server');
        } else {
            $options = ['' => get_string('selectserver', 'assignsubmission_external_server')] + $options;
        }
        $mform->addElement('select', 'assignsubmission_external_server_server', $name, $options);
        $mform->addHelpButton('assignsubmission_external_server_server', 'externalserver', 'assignsubmission_external_server');
        $mform->setDefault('assignsubmission_external_server_server', $server);
        $mform->hideIf('assignsubmission_external_server_server', 'assignsubmission_external_server_enabled', 'notchecked');
        $mform->disabledIf('assignsubmission_external_server_server', 'assignsubmission_external_server_enabled', 'notchecked');

        // Maximum file size.
        $name = get_string('maxbytes', 'assignsubmission_external_server');
        $options = get_max_upload_sizes($CFG->maxbytes);
        $mform->addElement('select', 'assignsubmission_external_server_maxbytes', $name, $options);
        $mform->addHelpButton('assignsubmission_external_server_maxbytes', 'maxbytes', 'assignsubmission_external_server');
        $mform->setDefault('assignsubmission_external_server_maxbytes', $maxbytes);
        $mform->hideIf('assignsubmission_external_server_maxbytes', 'assignsubmission_external_server_enabled', 'notchecked');

        // File types.
        $name = get_string('filetypes', 'assignsubmission_external_server');
        $mform->addElement('filetypes', 'assignsubmission_external_server_filetypes', $name);
        $mform->addHelpButton('assignsubmission_external_server_filetypes', 'filetypes', 'assignsubmission_external_server');
        $mform->setDefault('assignsubmission_external_server_filetypes', $filetypes);
        $mform->hideIf('assignsubmission_external_server_filetypes', 'assignsubmission_external_server_enabled', 'notchecked');

        // Number of uploads.
        $name = get_string('uploads', 'assignsubmission_external_server');
        $options = helper::get_upload_options($this->count_submissions());
        $mform->addElement('select', 'assignsubmission_external_server_uploads', $name, $options);
        $mform->addHelpButton('assignsubmission_external_server_uploads', 'uploads', 'assignsubmission_external_server');
        $mform->setDefault('assignsubmission_external_server_uploads', $uploads);
        $mform->hideIf('assignsubmission_external_server_uploads', 'assignsubmission_external_server_enabled', 'notchecked');

        // Check if the assignment already has submissions.
        if ($submissioncount > 0) {
            $mform->disabledIf('assignsubmission_external_server_server', 'assignsubmission_external_server_enabled', 'checked');
            $mform->disabledIf('assignsubmission_external_server_maxbytes', 'assignsubmission_external_server_enabled', 'checked');
            $mform->disabledIf('assignsubmission_external_server_filetypes', 'assignsubmission_external_server_enabled', 'checked');
            $message = get_string('submissionswarning', 'assignsubmission_external_server', $submissioncount);
            $warning = $OUTPUT->notification($message, \core\output\notification::NOTIFY_WARNING);
            $mform->addElement('html', $warning);
        }
    }

    /**
     * Save the settings for file submission plugin
     *
     * @param stdClass $data
     * @return bool
     */
    public function save_settings(stdClass $data) {
        foreach (ASSIGNSUBMISSION_EXTERNAL_SERVER_SETTINGS as $setting) {
            $property = "assignsubmission_external_server_$setting";
            if (!empty($data->$property)) {
                $this->set_config($setting, $data->$property);
            } else {
                $this->set_config($setting, '');
            }
        }

        return true;
    }

    /**
     * File format options
     *
     * @return array
     */
    public function get_file_options() {
        $fileoptions = ['subdirs' => 1,
                        'maxbytes' => $this->get_config('maxbytes'),
                        'maxfiles' => 1,
                        'accepted_types' => $this->get_configured_typesets(),
                        'return_types' => (FILE_INTERNAL | FILE_CONTROLLED_LINK)
        ];
        // Use module default if nothing is set.
        if ($fileoptions['maxbytes'] == 0) {
            $fileoptions['maxbytes'] = get_config('assignsubmission_external_server', 'maxbytes');
        }
        return $fileoptions;
    }

    /**
     * Add elements to submission form
     *
     * @param mixed $submission stdClass|null
     * @param MoodleQuickForm $mform
     * @param stdClass $data
     * @return bool
     */
    public function get_form_elements($submission, MoodleQuickForm $mform, stdClass $data) {
        global $OUTPUT;

        // Check if there are uploads left.
        $has_uploads = $this->has_uploadattempts($submission)['has_uploads'];

        // Display filepicker.
        if ($has_uploads) {
            $fileoptions = $this->get_file_options();
            $submissionid = $submission ? $submission->id : 0;
            $data = file_prepare_standard_filemanager($data,
                                                    'files',
                                                    $fileoptions,
                                                    $this->assignment->get_context(),
                                                    'assignsubmission_external_server',
                                                    ASSIGNSUBMISSION_EXTERNAL_SERVER_FILEAREA,
                                                    $submissionid);
            $mform->addElement('filepicker', 'files_filemanager', $this->get_name(), null, $fileoptions);

        } else {

            // No uploads left, display message.
            $message = get_string('nouploadsleft', 'assignsubmission_external_server');
            $mform->addElement('static', 'no_uploads', '', $OUTPUT->notification($message, \core\output\notification::NOTIFY_WARNING));
        }

        return true;
    }

    /**
     * Count the number of files
     *
     * @param int $submissionid
     * @param string $area
     * @return int
     */
    private function count_files($submissionid, $area) {
        $fs = get_file_storage();
        $files = $fs->get_area_files($this->assignment->get_context()->id,
                                     'assignsubmission_external_server',
                                     $area,
                                     $submissionid,
                                     'id',
                                     false);

        return count($files);
    }

    /**
     * Save the files and trigger plagiarism plugin, if enabled,
     * to scan the uploaded files via events trigger
     *
     * @param stdClass $submission
     * @param stdClass $data
     * @return bool
     */
    public function save(stdClass $submission, stdClass $data) {
        global $USER, $DB, $OUTPUT;

        // Save file.
        $fileoptions = $this->get_file_options();
        $fileoptions['maxbytes'] = (int) $fileoptions['maxbytes'];
        $data = file_postupdate_standard_filemanager($data,
                                                     'files',
                                                     $fileoptions,
                                                     $this->assignment->get_context(),
                                                     'assignsubmission_external_server',
                                                     ASSIGNSUBMISSION_EXTERNAL_SERVER_FILEAREA,
                                                     $submission->id);


        $filesubmission = $this->get_file_submission($submission->id);

        // Plagiarism code event trigger when files are uploaded.

        $fs = get_file_storage();
        $files = $fs->get_area_files($this->assignment->get_context()->id,
                                     'assignsubmission_external_server',
                                     ASSIGNSUBMISSION_EXTERNAL_SERVER_FILEAREA,
                                     $submission->id,
                                     'id',
                                     false
        );

        // No files uploaded - this can happen via the quick_edit_form.
        if (!$files) {
            $url = new moodle_url('/mod/assign/view.php', [
                'id' => $this->assignment->get_course_module()->id,
                'action' => 'view',
                'error' => 'needselectfile',
            ]);
            redirect($url);
        }

        $count = $this->count_files($submission->id, ASSIGNSUBMISSION_EXTERNAL_SERVER_FILEAREA);
        $params = array(
            'context' => context_module::instance($this->assignment->get_course_module()->id),
            'courseid' => $this->assignment->get_course()->id,
            'objectid' => $submission->id,
            'other' => array(
                'content' => '',
                'pathnamehashes' => array_keys($files)
            )
        );
        if (!empty($submission->userid) && ($submission->userid != $USER->id)) {
            $params['relateduserid'] = $submission->userid;
        }
        if ($this->assignment->is_blind_marking()) {
            $params['anonymous'] = 1;
        }
        $event = \assignsubmission_external_server\event\assessable_uploaded::create($params);
        $event->set_legacy_files($files);
        $event->trigger();

        $groupname = null;
        $groupid = 0;
        // Get the group name as other fields are not transcribed in the logs and this information is important.
        if (empty($submission->userid) && !empty($submission->groupid)) {
            $groupname = $DB->get_field('groups', 'name', array('id' => $submission->groupid), MUST_EXIST);
            $groupid = $submission->groupid;
        } else {
            $params['relateduserid'] = $submission->userid;
        }

        // Unset the objectid and other field from params for use in submission events.
        unset($params['objectid']);
        unset($params['other']);
        $params['other'] = array(
            'submissionid' => $submission->id,
            'submissionattempt' => $submission->attemptnumber,
            'submissionstatus' => $submission->status,
            'filesubmissioncount' => $count,
            'groupid' => $groupid,
            'groupname' => $groupname
        );

        // File was submitted.
        if ($filesubmission) {

            $filesubmission->numfiles = $this->count_files($submission->id,
                                                           ASSIGNSUBMISSION_EXTERNAL_SERVER_FILEAREA);

            // Increment the number of uploads.
            $filesubmission->uploads++;

            // Update filesubmission.
            $DB->update_record('assignsubmission_external_server', $filesubmission);

            // Update submission - this is needed for quick_edit_form to update it.
            $submission->timemodified = time();
            $DB->update_record('assign_submission', $submission);

            // Fire event.
            $params['objectid'] = $filesubmission->id;
            $event = \assignsubmission_external_server\event\submission_updated::create($params);
            $event->set_assign($this->assignment);
            $event->trigger();

            // Upload the file to the external server.
            $file = reset($files);
            $externalserver = $this->get_external_server();
            if ($externalserver) {
                $updatestatus = $externalserver->upload_file($file, $this->assignment->get_instance());
                if (!$updatestatus) {
                    return false;
                }
            } else {
                return false;
            }

            return $updatestatus;

        // No file was submitted - this should not happen, but we handle it gracefully.
        } else {
            $filesubmission = new stdClass();
            $filesubmission->numfiles = $this->count_files($submission->id,
                                                           ASSIGNSUBMISSION_EXTERNAL_SERVER_FILEAREA);
            $filesubmission->submission = $submission->id;
            $filesubmission->assignment = $this->assignment->get_instance()->id;
            $filesubmission->uploads = 1;
            $filesubmission->id = $DB->insert_record('assignsubmission_external_server', $filesubmission);
            $params['objectid'] = $filesubmission->id;

            $event = \assignsubmission_external_server\event\submission_created::create($params);
            $event->set_assign($this->assignment);
            $event->trigger();
            return $filesubmission->id > 0;
        }
    }

    /**
     * Remove files from this submission.
     *
     * @param stdClass $submission The submission
     * @return boolean
     */
    public function remove(stdClass $submission) {
        global $DB;
        $fs = get_file_storage();

        $fs->delete_area_files($this->assignment->get_context()->id,
                               'assignsubmission_external_server',
                               ASSIGNSUBMISSION_EXTERNAL_SERVER_FILEAREA,
                               $submission->id);

        $currentsubmission = $this->get_file_submission($submission->id);
        if ($currentsubmission) {
            $currentsubmission->numfiles = 0;
            $DB->update_record('assignsubmission_external_server', $currentsubmission);
        }

        return true;
    }

    /**
     * Produce a list of files suitable for export that represent this feedback or submission
     *
     * @param stdClass $submission The submission
     * @param stdClass $user The user record - unused
     * @return array - return an array of files indexed by filename
     */
    public function get_files(stdClass $submission, stdClass $user) {
        $result = array();
        $fs = get_file_storage();

        $files = $fs->get_area_files($this->assignment->get_context()->id,
                                     'assignsubmission_external_server',
                                     ASSIGNSUBMISSION_EXTERNAL_SERVER_FILEAREA,
                                     $submission->id,
                                     'timemodified, id',
                                     false);

        foreach ($files as $file) {
            // Do we return the full folder path or just the file name?
            if (isset($submission->exportfullpath) && $submission->exportfullpath == false) {
                $result[$file->get_filename()] = $file;
            } else {
                $result[$file->get_filepath().$file->get_filename()] = $file;
            }
        }
        return $result;
    }

    /**
     * Display the list of files in the submission status table
     *
     * @param stdClass $submission
     * @param bool $showviewlink Set this to true if the list of files is long
     * @return string
     */
    public function view_summary(stdClass $submission, & $showviewlink) {

        // Uploaded file.
        $html = $this->assignment->render_area_files('assignsubmission_external_server',
                                                      ASSIGNSUBMISSION_EXTERNAL_SERVER_FILEAREA,
                                                      $submission->id
        );

        // Get user and group IDs.
        $userid = $submission->userid;
        if ($userid == 0) {
            $groupid = $submission->groupid;
            if ($user = $this->get_group_submission_user($submission)) {
                $userid = $user->id;
            }
        } else {
            $user = core_user::get_user($userid);
            $groupid = 0;
        }

        // Upload attempts.
        $uploadattempts = $this->has_uploadattempts($submission);
        $uploads = get_string('uploadattempts', 'assignsubmission_external_server') . ': ' . $uploadattempts['html'];
        $html .= html_writer::div($uploads, 'uploadattempts');

        $context = $this->assignment->get_context();
        if (has_capability('mod/assign:grade', $context)) {

            // Link to view the full submission.
            if ($user) {
                $ext = $this->get_external_server();
                $url = $ext->build_teacherview($this->assignment->get_instance(), $user->username);
                $html .= html_writer::link($url, get_string('view'), ['class' => 'btn btn-secondary mr-1 mb-1',
                    'target' => '_blank']);

                // Link to update grade/feedback.
                $assignmentid = $submission->assignment;
                $cm = get_coursemodule_from_instance('assign', $assignmentid, 0, false, MUST_EXIST);
                $url = new moodle_url('/mod/assign/submission/external_server/grade.php',
                    ['cmid' => $cm->id, 'userid' => $userid, 'groupid' => $groupid]);
                $html .= html_writer::link($url, get_string('gradeverb'), ['class' => 'btn btn-primary']);
            }
        }

        return $html;
    }

    /**
     * No full submission view - the summary contains the list of files and that is the whole submission
     *
     * @param stdClass $submission
     * @return string
     */
    public function view(stdClass $submission) {
        return $this->assignment->render_area_files('assignsubmission_external_server',
                                                    ASSIGNSUBMISSION_EXTERNAL_SERVER_FILEAREA,
                                                    $submission->id);
    }

    /**
     * The assignment has been deleted - cleanup
     *
     * @return bool
     */
    public function delete_instance() {
        global $DB;
        // Will throw exception on failure.
        $DB->delete_records('assignsubmission_external_server',
                            array('assignment'=>$this->assignment->get_instance()->id));

        return true;
    }

    /**
     * Return true if there are no submission files
     * @param stdClass $submission
     */
    public function is_empty(stdClass $submission) {
        return $this->count_files($submission->id, ASSIGNSUBMISSION_EXTERNAL_SERVER_FILEAREA) == 0;
    }

    /**
     * Determine if a submission is empty
     *
     * This is distinct from is_empty in that it is intended to be used to
     * determine if a submission made before saving is empty.
     *
     * @param stdClass $data The submission data
     * @return bool
     */
    public function submission_is_empty(stdClass $data) {
        global $USER;
        $fs = get_file_storage();
        // Get a count of all the draft files, excluding any directories.
        $files = $fs->get_area_files(context_user::instance($USER->id)->id,
                                     'user',
                                     'draft',
                                     $data->files_filemanager,
                                     'id',
                                     false);
        return count($files) == 0;
    }

    /**
     * Get file areas returns a list of areas this plugin stores files
     * @return array - An array of fileareas (keys) and descriptions (values)
     */
    public function get_file_areas() {
        return array(ASSIGNSUBMISSION_EXTERNAL_SERVER_FILEAREA=>$this->get_name());
    }

    /**
     * Copy the student's submission from a previous submission. Used when a student opts to base their resubmission
     * on the last submission.
     * @param stdClass $sourcesubmission
     * @param stdClass $destsubmission
     */
    public function copy_submission(stdClass $sourcesubmission, stdClass $destsubmission) {
        global $DB;

        // Copy the files across.
        $contextid = $this->assignment->get_context()->id;
        $fs = get_file_storage();
        $files = $fs->get_area_files($contextid,
                                     'assignsubmission_external_server',
                                     ASSIGNSUBMISSION_EXTERNAL_SERVER_FILEAREA,
                                     $sourcesubmission->id,
                                     'id',
                                     false);
        foreach ($files as $file) {
            $fieldupdates = array('itemid' => $destsubmission->id);
            $fs->create_file_from_storedfile($fieldupdates, $file);
        }

        // Copy the assignsubmission_external_server record.
        if ($filesubmission = $this->get_file_submission($sourcesubmission->id)) {
            unset($filesubmission->id);
            $filesubmission->submission = $destsubmission->id;
            $DB->insert_record('assignsubmission_external_server', $filesubmission);
        }
        return true;
    }

    /**
     * Return a description of external params suitable for uploading a file submission from a webservice.
     *
     * @return \core_external\external_description|null
     */
    public function get_external_parameters() {
        return array(
            'files_filemanager' => new external_value(
                PARAM_INT,
                'The id of a draft area containing files for this submission.',
                VALUE_OPTIONAL
            )
        );
    }

    /**
     * Return the plugin configs for external functions.
     *
     * @return array the list of settings
     * @since Moodle 3.2
     */
    public function get_config_for_external() {
        global $CFG;

        $configs = $this->get_config();

        // Get a size in bytes.
        if ($configs->maxsubmissionsizebytes == 0) {
            $configs->maxsubmissionsizebytes = get_max_upload_file_size($CFG->maxbytes, $this->assignment->get_course()->maxbytes,
                                                                        get_config('assignsubmission_external_server', 'maxbytes'));
        }
        return (array) $configs;
    }

    /**
     * Get the type sets configured for this assignment.
     *
     * @return array('groupname', 'mime/type', ...)
     */
    private function get_configured_typesets() {
        $typeslist = (string)$this->get_config('filetypes');

        $util = new \core_form\filetypes_util();
        $sets = $util->normalize_file_types($typeslist);

        return $sets;
    }

    /**
     * Checks if there are already submissions for this assignment.
     *
     * @return bool
     */
    public function count_submissions() {
        global $DB;

        if (!$this->assignment->get_context()) {
            return 0;
        } else {
            return $DB->count_records('assignsubmission_external_server',
                ['assignment' => $this->assignment->get_instance()->id]);
        }
    }

    /**
     * Returns external server for this assignment.
     */
    public function get_external_server() {
        global $DB;

        $serverid = $this->get_config('server');
        if (empty($serverid)) {
            return null;
        }

        return new external_server($serverid);
    }

    /**
     * This allows a plugin to render an introductory section which is displayed
     * right below the activity's "intro" section on the main assignment page.
     *
     * @return string
     */
    public function view_header() {

        global $OUTPUT, $USER;

        $ext = $this->get_external_server();
        $cmid = $this->assignment->get_course_module()->id;
        $userid = $USER->id;

        // Get submission for group or user.
        if ($this->assignment->get_instance()->teamsubmission) {
            $submission = $this->assignment->get_group_submission($userid, 0, true);
        } else {
            $submission = $this->assignment->get_user_submission($userid, true);
        }

        // String for external server name.
        if ($ext) {
            $extservername = $ext->obj->name;
        } else {
            $context = $this->assignment->get_context();
            if (has_capability('mod/assign:grade', $context)) {
                $message = get_string('noneselected', 'assignsubmission_external_server');
            } else {
                $message = get_string('noneselectedstudent', 'assignsubmission_external_server');
            }
            return $OUTPUT->notification($message, \core\output\notification::NOTIFY_WARNING);
        }

        // Header.
        $title = get_string('externalservertitle', 'assignsubmission_external_server', $extservername);
        $html = html_writer::tag('h2', $title);

        // Quick submission/grading edit form.
        $url = new moodle_url('/mod/assign/view.php', [
            'id' => $cmid,
            'action' => 'view',
        ]);
        $mform = new quick_edit_form($this, $this->assignment, $submission, $url);

        // Determine if quickedit form was used to submit.
        $quickedit = false;
        if ($data = $mform->get_data()) {
            if (isset($data->submitbutton)) {
                $quickedit = true;
            }
        }

        // Check uploadattempts.
        $uploadattempts = $this->has_uploadattempts($submission, $quickedit);

        $context = $this->assignment->get_context();
        if ($uploadattempts['has_uploads'] || has_capability('mod/assign:grade', $context)) {

            // Embed form.
            ob_start();
            $mform->display();

            // Handle submission.
            if ($data) {

                // Grading.
                if (isset($data->gradebutton)) {
                    $url = new moodle_url('/mod/assign/submission/external_server/grade.php', [
                        'status' => $data->status, 'cmid' => $cmid
                    ]);
                    redirect($url);

                // Submission.
                } else {

                    $quickedit = 1; // Used to fake progression on the upload counter.

                    // Update the submission record
                    $submission->timemodified = time();
                    $submission->status = ASSIGN_SUBMISSION_STATUS_SUBMITTED;

                    // Save the core submission record (handles group mode properly)
                    $this->assignment->save_submission($data, $notices);
                }
            }

            // End embedding.
            $html .= ob_get_clean();
        }

        // Status table.
        $table = new html_table();
        $table->attributes['class'] = 'assignsubmission-external-server-table';
        $table->data = [
            [get_string('connectionstatus', 'assignsubmission_external_server') . ':', $this->print_server_status($ext)],
            [get_string('uploadattempts', 'assignsubmission_external_server') . ':', $uploadattempts['html']],
        ];
        $html .= html_writer::table($table);

        // iFrame.
        if ($ext) {
            $toggle_id = 'assignsubmission-external-server-response-toggle-' . $cmid;

            $button = html_writer::tag('button',
                get_string('expandresponse', 'assignsubmission_external_server'),
                [
                    'class' => 'btn btn-link',
                    'type' => 'button',
                    'data-bs-toggle' => 'collapse',
                    'data-bs-target' => "#$toggle_id",
                    'aria-expanded' => 'false',
                    'aria-controls' => $toggle_id
                ]
            );

            $content = html_writer::div(
                $ext->view_externalframe($this->assignment->get_instance()),
                'collapse mt-2', // class
                ['id' => $toggle_id] // attributes
            );

            $html .= html_writer::div($button . $content, 'my-collapsible-wrapper');
        }

        $html .= '<hr>';

        return $html;
    }

    /**
     * Prints the upload attempts for the current user.
     *
     * @param stdClass $submission The submission record.
     * @param bool $quickedit We have to add one submission if quickedit form was used.
     *
     * @return array
     */
    private function has_uploadattempts($submission, $quickedit = false) {

        global $DB, $USER;

        // Get the number of uploads and max uploads.
        $uploads = $DB->get_field('assignsubmission_external_server', 'uploads',
            array('submission' => $submission->id));
        if (!$uploads) {
            $uploads = 0; // Default to 0 if no uploads found.
        }

        // If quick edit form was used, we have to add one upload.
        if ($quickedit) {
            $uploads++;
        }
        $maxuploads = $this->get_config('uploads');

        // Unlimited uploads.
        if ($maxuploads < 0) {
            $has_uploads = true;
            $type = 'success';
            $uploadstring = get_string('unlimiteduploads', 'assignsubmission_external_server');

        // Show attempts.
        } else {
            $uploadstring = "$uploads/$maxuploads";

            // See if we still have uploads left.
            if ($uploads < $maxuploads) {
                $has_uploads = true;
                $type = 'success';
            } else {
                $has_uploads = false;
                $type = 'danger';
            }
        }

        // Render text.
        $html = html_writer::tag('span', $uploadstring, [
            'title' => get_string('uploadattempts', 'assignsubmission_external_server'), 'class' => "text-$type"]);
        return ['has_uploads' => $has_uploads, 'html' => $html];
    }

    /**
     * Prints the server status.
     *
     * @param stdClass $ext The external server instance.
     *
     * @return string
     */
    private function print_server_status($ext) {
        global $OUTPUT;

        if ($ext) {
            if ($ext->check_connection()) {
                $status = get_string('ok');
                $type = 'success';
            } else {
                $status = get_string('error');
                $type = 'danger';
            }
        } else {
            $status = get_string('none');
            $type = 'warning';
        }

        // Render text.
        $html = html_writer::tag('span', $status, ['class' => "text-$type"]);
        return $html;
    }

    /**
     * Gets the user that submitted a group submission.
     *
     * @param stdClass $submission The group submission record.
     *
     * @return stdClass $user.
     */
    public function get_group_submission_user($submission) {

        // Get params.
        $groupid = $submission->groupid;
        $assignid = $submission->assignment;
        $attempt = $submission->attemptnumber;

        $groupmembers = groups_get_members($groupid);
        foreach ($groupmembers as $member) {
            $usersubmission = $this->assignment->get_user_submission($member->id, false);
            if ($usersubmission &&
                $usersubmission->status === ASSIGN_SUBMISSION_STATUS_SUBMITTED &&
                $usersubmission->attemptnumber == $attempt) {
                // This user likely triggered the group submission
                return $member;
            }
        }
    }
}
