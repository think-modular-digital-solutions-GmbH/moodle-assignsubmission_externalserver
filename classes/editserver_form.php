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

namespace assignsubmission_external_server;

use moodleform;

defined('MOODLE_INTERNAL') || die;

/**
 * Add/edit server form for the external server submission plugin.
 *
 * @package    assignsubmission_external_server
 * @author     Stefan Weber (stefan.weber@think-modular.com)
 * @copyright  2025 think-modular
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class editserver_form extends moodleform {
    /** @var stdClass Form data */
    protected $server;
    /** @var context_system System context object */
    protected $context;

    /**
     * Defines the form
     *
     * @throws coding_exception
     * @throws dml_exception
     * @throws HTML_Quickform_error
     */
    public function definition() {
        $mform = $this->_form;

        $server = $this->_customdata['server']; // This contains the data of this form.
        $editoroptions = $this->_customdata['editoroptions'];

        $this->server  = $server;
        $this->context = context_system::instance();

        // Form definition with new server defaults.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'servicename', get_string('servicename', 'extserver'), 'maxlength="254" size="50"');
        $mform->setType('servicename', PARAM_TEXT);
        $mform->addHelpButton('servicename', 'servicename', 'extserver');
        $mform->addRule('servicename', get_string('servicename_missing', 'extserver'), 'required', null, 'client');
        if (isset($server->service_name)) {
            $mform->setConstant('servicename', $server->service_name);
        }

        $mform->addElement('text', 'serverurl', get_string('serverurl', 'extserver'), 'maxlength="254" size="50"');
        $mform->setType('serverurl', PARAM_URL);
        $mform->addHelpButton('serverurl', 'serverurl', 'extserver');

        if (isset($server->server_url)) {
            $mform->setConstant('serverurl', $server->server_url);
        }

        $mform->addElement('text', 'serverformurl', get_string('serverformurl', 'extserver'), 'maxlength="254" size="50"');
        $mform->setType('serverformurl', PARAM_URL);
        $mform->addHelpButton('serverformurl', 'serverformurl', 'extserver');

        if (isset($server->serverform_url)) {
            $mform->setConstant('serverformurl', $server->serverform_url);
        }

        $mform->addElement('passwordunmask', 'serversecret', get_string('serversecret', 'extserver'), 'maxlength="254" size="50"');
        $mform->setType('serversecret', PARAM_RAW);
        $mform->addHelpButton('serversecret', 'serversecret', 'extserver');
        if (isset($server->server_secret)) {
            $mform->setConstant('serversecret', $server->server_secret);
        }

        $hashalgorithms = hash_algos();
        $hashalgorithms = array_combine($hashalgorithms, $hashalgorithms);
        $mform->addElement('select', 'hash', get_string('hashalgorithm', 'extserver'), $hashalgorithms);
        $mform->addHelpButton('hash', 'hashalgorithm', 'extserver');
        $mform->setAdvanced('hash');
        $mform->setDefault('hash', 'sha256');
        if (isset($server->hash)) {
            $mform->setConstant('hash', $server->hash);
        }

        $sslverificationoptions = [0 => get_string('sslverification_none', 'extserver'),
                                   2 => get_string('sslverification_identity', 'extserver'),
                                ];
        $mform->addElement('select', 'sslverification', get_string('sslverification', 'extserver'), $sslverificationoptions);
        $mform->addHelpButton('sslverification', 'sslverification', 'extserver');
        $mform->setAdvanced('sslverification');
        $mform->setDefault('sslverification', 2);
        if (isset($server->sslverification)) {
            $mform->setConstant('sslverification', $server->sslverification);
        }

        $groupinfo = [\external_server::NO_GROUPINFO => get_string('groupinfo_not_needed', 'extserver'),
                      \external_server::NEEDS_GROUP_INFO => get_string('groupinfo_must_be_sent', 'extserver'),
                    ];
        $mform->addElement('select', 'groupinfo', get_string('groupinfo', 'extserver'), $groupinfo);
        $mform->addHelpButton('groupinfo', 'groupinfo', 'extserver');
        $mform->setAdvanced('groupinfo');
        $mform->setDefault('groupinfo', \external_server::NO_GROUPINFO);

        $mform->addElement('header', '', get_string('contact', 'extserver'));

        $mform->addElement('text', 'contactname', get_string('contactname', 'extserver'), 'maxlength="254" size="50"');
        $mform->setType('contactname', PARAM_TEXT);
        $mform->addHelpButton('contactname', 'contactname', 'extserver');
        if (isset($server->contact_name)) {
            $mform->setConstant('contactname', $server->contact_name);
        }

        $mform->addElement('text', 'contactemail', get_string('contactemail', 'extserver'), 'maxlength="254" size="50"');
        $mform->setType('contactemail', PARAM_EMAIL);
        $mform->addHelpButton('contactemail', 'contactemail', 'extserver');
        if (isset($server->contact_email)) {
            $mform->setConstant('contactemail', $server->contact_email);
        }

        $mform->addElement('text', 'contactphone', get_string('contactphone', 'extserver'), 'maxlength="254" size="50"');
        $mform->setType('contactphone', PARAM_TEXT);
        $mform->addHelpButton('contactphone', 'contactphone', 'extserver');

        if (isset($server->contact_phone)) {
            $mform->setConstant('contactphone', $server->contact_phone);
        }

        $mform->addElement('text', 'contactorg', get_string('contactorg', 'extserver'), 'maxlength="254" size="50"');
        $mform->setType('contactorg', PARAM_TEXT);
        $mform->addHelpButton('contactorg', 'contactorg', 'extserver');
        if (isset($server->contact_org)) {
            $mform->setConstant('contactorg', $server->contact_org);
        }

        $mform->addElement('editor', 'server_info_editor', get_string('serverinfo', 'extserver'), null, $editoroptions);
        $mform->addHelpButton('server_info_editor', 'serverinfo', 'extserver');
        $mform->setType('server_info_editor', PARAM_RAW);

        $this->add_action_buttons();

        $mform->addElement('hidden', 'id', null);
        $mform->setType('id', PARAM_INT);

        // Finally set the current form data.
        $this->set_data($server);
    }

    /**
     * Perform some extra validation.
     *
     * @param array $data The submitted data
     * @param array $files Uploaded/Submitted files
     * @return array Associative array containing validation error messages
     * @throws dml_exception
     * @throws coding_exception
     */
    public function validation($data, $files) {
        global $DB;

        $errors = parent::validation($data, $files);

        if (!empty($data['servicename'])) {

            if (!empty($data['id'])) {
                $serviceid = $data['id'];
            } else {
                $serviceid = -1;
            }

            $name = strtolower($data['servicename']);
            $servicenames = $DB->get_records_list('extserver_servers', 'service_name', [$name], null, 'id, service_name');

            foreach ($servicenames as $obj) {
                if ($obj->id != $serviceid && strtolower($obj->service_name) == $name) {
                    $errors['servicename'] = get_string('servicename_duplicate', 'extserver');
                }
            }
        }

        if ($data['contactemail'] && !validate_email($data['contactemail'])) {
            $errors['contactemail'] = get_string('invalidemail', 'extserver');
        }

        // ValidateUrlSyntax doenst work 100 percent, but its better than nothing.
        if ($data['serverurl'] && !validateUrlSyntax($data['serverurl'])) {
            $errors['serverurl'] = get_string('invalidurl', 'extserver');
        }

        if ($data['serverformurl'] && !validateUrlSyntax($data['serverformurl'])) {
            $errors['serverformurl'] = get_string('invalidurl', 'extserver');
        }

        $errors = array_merge($errors, enrol_course_edit_validation($data, $this->context));

        return $errors;
    }
}
