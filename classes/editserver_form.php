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

require_once($CFG->libdir . '/formslib.php');

defined('MOODLE_INTERNAL') || die;

use moodleform;
use assignsubmission_external_server\external_server;

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
        $this->context = \context_system::instance();
        confirm_sesskey();

        // Form definition with new server defaults.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Name.
        $mform->addElement('text', 'name', get_string('server:name', 'assignsubmission_external_server'), 'maxlength="254" size="50"');
        $mform->setType('name', PARAM_TEXT);
        $mform->addHelpButton('name', 'server:name', 'assignsubmission_external_server');
        $mform->addRule('name', get_string('server:name_missing', 'assignsubmission_external_server'), 'required', null, 'client');
        if (isset($server->name)) {
            $mform->setConstant('name', $server->name);
        }

        // Url.
        $mform->addElement('text', 'url', get_string('server:url', 'assignsubmission_external_server'), 'maxlength="254" size="50"');
        $mform->setType('url', PARAM_URL);
        $mform->addHelpButton('url', 'server:url', 'assignsubmission_external_server');

        // Form URL.
        $mform->addElement('text', 'form_url', get_string('server:form_url', 'assignsubmission_external_server'), 'maxlength="254" size="50"');
        $mform->setType('form_url', PARAM_URL);
        $mform->addHelpButton('form_url', 'server:form_url', 'assignsubmission_external_server');

        // Authentification type.
        $authoptions = ['api_key' => get_string('server:auth_api_key', 'assignsubmission_external_server'),
                        'oauth2' => get_string('server:auth_oauth2', 'assignsubmission_external_server'),
                        'jwt' => get_string('server:auth_jwt', 'assignsubmission_external_server')];
        $mform->addElement('select', 'auth_type', get_string('server:auth_type', 'assignsubmission_external_server'), $authoptions);
        $mform->addHelpButton('auth_type', 'server:auth_type', 'assignsubmission_external_server');
        $mform->setDefault('autht_ype', 'api_key');

        // Authentification secret.
        $mform->addElement('passwordunmask', 'auth_secret', get_string('server:auth_secret', 'assignsubmission_external_server'), 'maxlength="254" size="50"');
        $mform->setType('auth_secret', PARAM_RAW);
        $mform->addHelpButton('auth_secret', 'server:auth_secret', 'assignsubmission_external_server');
        if (isset($server->auth_secret)) {
            $mform->setConstant('serversecret', $server->auth_secret);
        }

        // Group information.
        $groupinfo = [external_server::NO_GROUPINFO => get_string('server:groupinfo_not_needed', 'assignsubmission_external_server'),
                      external_server::NEEDS_GROUP_INFO => get_string('server:groupinfo_must_be_sent', 'assignsubmission_external_server'),
                     ];
        $mform->addElement('select', 'groupinfo', get_string('server:groupinfo', 'assignsubmission_external_server'), $groupinfo);
        $mform->addHelpButton('groupinfo', 'server:groupinfo', 'assignsubmission_external_server');
        $mform->setDefault('groupinfo', external_server::NO_GROUPINFO);

        // Hash algorithm.
        $hashalgorithms = hash_algos();
        $hashalgorithms = array_combine($hashalgorithms, $hashalgorithms);
        $mform->addElement('select', 'hash', get_string('server:hashalgorithm', 'assignsubmission_external_server'), $hashalgorithms);
        $mform->addHelpButton('hash', 'server:hashalgorithm', 'assignsubmission_external_server');
        $mform->setAdvanced('hash');
        $mform->setDefault('hash', 'sha256');
        if (isset($server->hash)) {
            $mform->setConstant('hash', $server->hash);
        }

        // SSL verification.
        $sslverificationoptions = [0 => get_string('server:sslverification_none', 'assignsubmission_external_server'),
                                   2 => get_string('server:sslverification_identity', 'assignsubmission_external_server'),
                                ];
        $mform->addElement('select', 'sslverification', get_string('server:sslverification', 'assignsubmission_external_server'), $sslverificationoptions);
        $mform->addHelpButton('sslverification', 'server:sslverification', 'assignsubmission_external_server');
        $mform->setAdvanced('sslverification');
        $mform->setDefault('sslverification', 2);
        if (isset($server->sslverification)) {
            $mform->setConstant('sslverification', $server->sslverification);
        }

        // Contact information.
        $mform->addElement('header', 'contact', get_string('server:contact', 'assignsubmission_external_server'));

        // Contact name.
        $mform->addElement('text', 'contact_name', get_string('server:contact_name', 'assignsubmission_external_server'), 'maxlength="254" size="50"');
        $mform->setType('contact_name', PARAM_TEXT);
        $mform->addHelpButton('contact_name', 'server:contact_name', 'assignsubmission_external_server');
        if (isset($server->contact_name)) {
            $mform->setConstant('contact_name', $server->contact_name);
        }

        // Contact email.
        $mform->addElement('text', 'contact_email', get_string('server:contact_email', 'assignsubmission_external_server'), 'maxlength="254" size="50"');
        $mform->setType('contact_email', PARAM_EMAIL);
        $mform->addHelpButton('contact_email', 'server:contact_email', 'assignsubmission_external_server');
        if (isset($server->contact_email)) {
            $mform->setConstant('contact_email', $server->contact_email);
        }

        // Contact phone.
        $mform->addElement('text', 'contact_phone', get_string('server:contact_phone', 'assignsubmission_external_server'), 'maxlength="254" size="50"');
        $mform->setType('contact_phone', PARAM_TEXT);
        $mform->addHelpButton('contact_phone', 'server:contact_phone', 'assignsubmission_external_server');
        if (isset($server->contact_phone)) {
            $mform->setConstant('contactphone', $server->contact_phone);
        }

        // Contact organization.
        $mform->addElement('text', 'contact_org', get_string('server:contact_org', 'assignsubmission_external_server'), 'maxlength="254" size="50"');
        $mform->setType('contact_org', PARAM_TEXT);
        $mform->addHelpButton('contact_org', 'server:contact_org', 'assignsubmission_external_server');
        if (isset($server->contact_org)) {
            $mform->setConstant('contact_org', $server->contact_org);
        }

        // Comments.
        $mform->addElement('textarea', 'info', get_string('server:info', 'assignsubmission_external_server'));
        $mform->addHelpButton('info', 'server:info', 'assignsubmission_external_server');
        $mform->setType('info', PARAM_RAW);

        // Hidden ID element.
        $mform->addElement('hidden', 'id', null);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'sesskey', sesskey());
        $mform->setType('sesskey', PARAM_ALPHANUMEXT);

        // Submit buttons.
        $this->add_action_buttons();

        // Finally set the current form data.
        if ($id = optional_param('id', 0, PARAM_INT)) {
            $server = external_server::get_server($id);
            $this->set_data($server);
        }

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

        // Get errors from parent validation.
        $errors = parent::validation($data, $files);

        // Check if the name is a duplicate.
        if (!empty($data['name'])) {

            if (!empty($data['id'])) {
                $serviceid = $data['id'];
            } else {
                $serviceid = -1;
            }

            $name = strtolower($data['name']);
            $servicenames = $DB->get_records_list('assignsubmission_external_server_servers', 'name', [$name], null, 'id, name');
            foreach ($servicenames as $obj) {
                if ($obj->id != $serviceid && strtolower($obj->name) == $name) {
                    $errors['name'] = get_string('server:name_duplicate', 'assignsubmission_external_server');
                }
            }
        }

        // Contact E-Mail.
        if ($data['contact_email'] && !validate_email($data['contact_email'])) {
            $errors['contact_email'] = get_string('server:contact_email_invalid', 'assignsubmission_external_server');
        }

        // Validate URLs.
        if ($data['url'] && !validateUrlSyntax($data['url'])) {
            $errors['url'] = get_string('server:url_invalid', 'assignsubmission_external_server');
        }
        if ($data['form_url'] && !validateUrlSyntax($data['form_url'])) {
            $errors['form_url'] = get_string('server:url_invalid', 'assignsubmission_external_server');
        }

        // Return errors.
        return $errors;
    }
}
