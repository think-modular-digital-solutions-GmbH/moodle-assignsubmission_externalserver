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
 * Add/edit server form for the external server submission plugin.
 *
 * @package    assignsubmission_externalserver
 * @author     Stefan Weber (stefan.weber@think-modular.com)
 * @copyright  2025 think-modular
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace assignsubmission_externalserver;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

use moodleform;
use assignsubmission_externalserver\externalserver;

/**
 * Add/edit server form for the external server submission plugin.
 *
 * @package    assignsubmission_externalserver
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
    public function definition(): void {
        $mform = $this->_form;
        $this->context = \context_system::instance();
        confirm_sesskey();

        // Make all our text inputs a little wider.
        $textoptions = 'maxlength="254" size="50"';

        // Form definition with new server defaults.
        $mform->addElement(
            'header',
            'general',
            get_string('general', 'form')
        );

        // Name.
        $mform->addElement(
            'text',
            'name',
            get_string('server:name', 'assignsubmission_externalserver'),
            $textoptions
        );
        $mform->setType('name', PARAM_TEXT);
        $mform->addHelpButton('name', 'server:name', 'assignsubmission_externalserver');
        $mform->addRule('name', get_string('server:name_missing', 'assignsubmission_externalserver'), 'required', null, 'client');

        // Url.
        $mform->addElement(
            'text',
            'url',
            get_string('server:url', 'assignsubmission_externalserver'),
            $textoptions
        );
        $mform->setType('url', PARAM_URL);
        $mform->addHelpButton('url', 'server:url', 'assignsubmission_externalserver');

        // Form URL.
        $mform->addElement(
            'text',
            'form_url',
            get_string('server:form_url', 'assignsubmission_externalserver'),
            $textoptions
        );
        $mform->setType('form_url', PARAM_URL);
        $mform->addHelpButton('form_url', 'server:form_url', 'assignsubmission_externalserver');

        // Authentification type.
        $authoptions = [
            'api_key' => get_string('server:auth_api_key', 'assignsubmission_externalserver'),
            'oauth2' => get_string('server:auth_oauth2', 'assignsubmission_externalserver'),
            'jwt' => get_string('server:auth_jwt', 'assignsubmission_externalserver')
        ];
        $mform->addElement(
            'select',
            'auth_type',
            get_string('server:auth_type', 'assignsubmission_externalserver'),
            $authoptions
        );
        $mform->addHelpButton('auth_type', 'server:auth_type', 'assignsubmission_externalserver');
        $mform->setDefault('auth_type', 'api_key');

        // Authentification secret.
        $mform->addElement(
            'passwordunmask',
            'auth_secret',
            get_string('server:auth_secret', 'assignsubmission_externalserver'),
            $textoptions
        );
        $mform->setType('auth_secret', PARAM_RAW);
        $mform->addHelpButton('auth_secret', 'server:auth_secret', 'assignsubmission_externalserver');

        // OAuth2 token endpoint.
        $mform->addElement(
            'text',
            'oauth2_endpoint',
            get_string('server:oauth2_endpoint', 'assignsubmission_externalserver'),
            $textoptions
        );
        $mform->setType('oauth2_endpoint', PARAM_URL);
        $mform->hideif('oauth2_endpoint', 'auth_type', 'eq', 'api_key');

        // OAuth2 client id.
        $mform->addElement(
            'text',
            'oauth2_client_id',
            get_string('server:oauth2_client_id', 'assignsubmission_externalserver'),
            $textoptions
        );
        $mform->setType('oauth2_client_id', PARAM_TEXT);
        $mform->hideif('oauth2_client_id', 'auth_type', 'eq', 'api_key');

        // JWT issuer.
        $mform->addElement(
            'text',
            'jwt_issuer',
            get_string('server:jwt_issuer', 'assignsubmission_externalserver'),
            $textoptions
        );
        $mform->setType('jwt_issuer', PARAM_TEXT);
        $mform->hideif('jwt_issuer', 'auth_type', 'neq', 'jwt');

        // JWT audience.
        $mform->addElement(
            'text',
            'jwt_audience',
            get_string('server:jwt_audience', 'assignsubmission_externalserver'),
            $textoptions
        );
        $mform->setType('jwt_audience', PARAM_TEXT);
        $mform->hideif('jwt_audience', 'auth_type', 'neq', 'jwt');

        // Group information.
        $groupinfo = [
            externalserver::NO_GROUPINFO => get_string('server:groupinfo_not_needed', 'assignsubmission_externalserver'),
            externalserver::NEEDS_GROUP_INFO => get_string('server:groupinfo_must_be_sent', 'assignsubmission_externalserver'),
        ];
        $mform->addElement(
            'select',
            'groupinfo',
            get_string('server:groupinfo', 'assignsubmission_externalserver'),
            $groupinfo
        );
        $mform->addHelpButton('groupinfo', 'server:groupinfo', 'assignsubmission_externalserver');
        $mform->setDefault('groupinfo', externalserver::NO_GROUPINFO);

        // Hash algorithm.
        $hashalgorithms = hash_algos();
        $hashalgorithms = array_combine($hashalgorithms, $hashalgorithms);
        $mform->addElement(
            'select',
            'hash',
            get_string('server:hashalgorithm', 'assignsubmission_externalserver'),
            $hashalgorithms
        );
        $mform->addHelpButton('hash', 'server:hashalgorithm', 'assignsubmission_externalserver');
        $mform->setAdvanced('hash');
        $mform->setDefault('hash', 'sha256');

        // SSL verification.
        $sslverificationoptions = [
            0 => get_string('server:sslverification_none', 'assignsubmission_externalserver'),
            2 => get_string('server:sslverification_identity', 'assignsubmission_externalserver'),
        ];
        $mform->addElement(
            'select',
            'sslverification',
            get_string('server:sslverification', 'assignsubmission_externalserver'),
            $sslverificationoptions
        );
        $mform->addHelpButton('sslverification', 'server:sslverification', 'assignsubmission_externalserver');
        $mform->setAdvanced('sslverification');
        $mform->setDefault('sslverification', 2);
        if (isset($server->sslverification)) {
            $mform->setConstant('sslverification', $server->sslverification);
        }

        // Contact information.
        $mform->addElement('header', 'contact', get_string('server:contact', 'assignsubmission_externalserver'));

        // Contact name.
        $mform->addElement(
            'text',
            'contact_name',
            get_string('server:contact_name', 'assignsubmission_externalserver'),
            $textoptions
        );
        $mform->setType('contact_name', PARAM_TEXT);
        $mform->addHelpButton('contact_name', 'server:contact_name', 'assignsubmission_externalserver');
        if (isset($server->contact_name)) {
            $mform->setConstant('contact_name', $server->contact_name);
        }

        // Contact email.
        $mform->addElement(
            'text',
            'contact_email',
            get_string('server:contact_email',
            'assignsubmission_externalserver'),
            $textoptions
        );
        $mform->setType('contact_email', PARAM_EMAIL);
        $mform->addHelpButton('contact_email', 'server:contact_email', 'assignsubmission_externalserver');
        if (isset($server->contact_email)) {
            $mform->setConstant('contact_email', $server->contact_email);
        }

        // Contact phone.
        $mform->addElement(
            'text',
            'contact_phone',
            get_string('server:contact_phone', 'assignsubmission_externalserver'),
            $textoptions
        );
        $mform->setType('contact_phone', PARAM_TEXT);
        $mform->addHelpButton('contact_phone', 'server:contact_phone', 'assignsubmission_externalserver');
        if (isset($server->contact_phone)) {
            $mform->setConstant('contact_phone', $server->contact_phone);
        }

        // Contact organization.
        $mform->addElement(
            'text',
            'contact_org',
            get_string('server:contact_org', 'assignsubmission_externalserver'),
            $textoptions
        );
        $mform->setType('contact_org', PARAM_TEXT);
        $mform->addHelpButton('contact_org', 'server:contact_org', 'assignsubmission_externalserver');
        if (isset($server->contact_org)) {
            $mform->setConstant('contact_org', $server->contact_org);
        }

        // Comments.
        $mform->addElement(
            'textarea',
            'info',
            get_string('server:info', 'assignsubmission_externalserver')
        );
        $mform->addHelpButton('info', 'server:info', 'assignsubmission_externalserver');
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
            $server = externalserver::get_server($id);
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
    public function validation($data, $files): array {
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
            $servicenames = $DB->get_records_list('assignsubmission_externalserver_servers', 'name', [$name], null, 'id, name');
            foreach ($servicenames as $obj) {
                if ($obj->id != $serviceid && strtolower($obj->name) == $name) {
                    $errors['name'] = get_string('server:name_duplicate', 'assignsubmission_externalserver');
                }
            }
        }

        // Contact E-Mail.
        if ($data['contact_email'] && !validate_email($data['contact_email'])) {
            $errors['contact_email'] = get_string('server:contact_email_invalid', 'assignsubmission_externalserver');
        }

        // Validate URLs.
        if ($data['url'] && !validateUrlSyntax($data['url'])) {
            $errors['url'] = get_string('server:url_invalid', 'assignsubmission_externalserver');
        }
        if ($data['form_url'] && !validateUrlSyntax($data['form_url'])) {
            $errors['form_url'] = get_string('server:url_invalid', 'assignsubmission_externalserver');
        }

        // Return errors.
        return $errors;
    }
}
