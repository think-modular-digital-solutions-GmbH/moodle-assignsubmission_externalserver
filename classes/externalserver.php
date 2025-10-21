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
 * Contains the class representing the external server (not the module instance)
 *
 * @package    assignsubmission_externalserver
 * @author     Stefan Weber <stefan.weber@think-modular.com>
 * @copyright  2025 think-modular
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace assignsubmission_externalserver;

defined('MOODLE_INTERNAL') || die();

use context_module;
use html_writer;
use moodle_url;
use stdClass;
use core\notification;
use GuzzleHttp\Client;

require_once($CFG->dirroot . '/mod/assign/submission/externalserver/lib.php');
require_once($CFG->libdir . '/pdflib.php');

/**
 * This class represents an external server (not an instance of external server the moodle module)
 *
 * @package    assignsubmission_externalserver
 * @author     Stefan Weber <stefan.weber@think-modular.com>
 * @copyright  2025 think-modular
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class externalserver {
    /** server needs no group info */
    const NO_GROUPINFO = 0;
    /** server demands information about user groups */
    const NEEDS_GROUP_INFO = 1;
    /** string[] array of params to include in akey */
    const AKEY_PARAMS = ['timestamp', 'user', 'skey', 'uidnr', 'action', 'cidnr', 'aid', 'aname', 'fname', 'lname', 'role'];
    /** string[][] action-indexed array of array of params to include in akey (depending on action) */
    const ACTION_PARAMS = [
        'submit'      => ['filename', 'filehash'],
        'teacherview' => ['studusername'],
        'getgrades'   => ['unames'],
    ];

    /** @var Client Guzzle HTTP client */
    private $httpclient = null;

    /** @var stdClass Database record */
    public $obj = null;
    /** @var bool whether or not the connection is ok */
    public $concheck = null;
    /** @var bool|string HTTP status code */
    public $httpcode = false;
    /** @var string Debug info */
    public $debuginfo = '';

    /**
     * Constructor
     *
     * @param int $id ID of external server to fetch from DB
     * @throws dml_exception
     */
    public function __construct($id) {
        global $DB;

        // Get external server from DB.
        if ($id != 0) {
            $id = (string) $id;
            $this->obj = $DB->get_record('assignsubmission_externalserver_servers', ['id' => $id]);
            if ($this->obj->hash == null) {
                $this->obj->hash = 'sha256';
            }
        }

        // Initialize the HTTP client.
        $this->httpclient = new Client([
            'base_uri' => $this->obj->url,
            'timeout' => 10,
            'verify' => !empty($this->obj->sslverification),
        ]);
    }

    /**
     * Calculates the key which is used for security.
     *
     * @param string[]|string[][] $params all the params used for the request, which should be hashed
     * @param string $secret server secret
     * @param string $hash hashing algorithm to use!
     * @return string
     * @throws coding_exception
     */
    public function calc_akey($params, $secret, $hash): string {
        global $OUTPUT;

        $string = $secret;

        // Add general parameters!
        foreach (self::AKEY_PARAMS as $param) {
            if (!array_key_exists($param, $params)) {
                echo $OUTPUT->box_start('generalbox');
                echo $OUTPUT->notification(get_string('missing_hash_param', 'extserver', $param), 'notifyproblem');
                echo $OUTPUT->box_end();
            } else {
                // Add specified params to akey calculation!
                $string .= $params[$param];
            }
        }

        // Add action specific params!
        $action = $params['action'];
        if ($action == 'view' && $params['role'] == 'teacher') {
            $action = 'teacherview';
        } else {
            // We don't need studusername in student view!
            $action = 'studentview';
        }
        if (key_exists($action, self::ACTION_PARAMS) && !empty(self::ACTION_PARAMS[$action])) {
            foreach (self::ACTION_PARAMS[$action] as $param) {
                if (!array_key_exists($param, $params)) {
                    echo $OUTPUT->box_start('generalbox');
                    echo $OUTPUT->notification(get_string('missing_hash_param', 'extserver', $param), 'notifyproblem');
                    echo $OUTPUT->box_end();
                } else {
                    // Add specified params to akey calculation!
                    if (is_array($params[$param])) {
                        // This is an array, we have to take care of it explicitly...
                        $sorted = ksort($params[$param]);
                        $string .= implode($param, $sorted);
                    } else {
                        $string .= $params[$param];
                    }
                }
            }
        }

        if ($hash != null) {
            $hash = hash($hash, $string);
        } else {
            $hash = $string;
        }

        return $hash;
    }

    /**
     * Calculates the hash over user's group data!
     *
     * @param string $jsongroupinfo json encoded group information
     * @param string $secret server secret
     * @param string $hash hashing algorithm to use!
     * @return string
     */
    public function calc_groups_hash($jsongroupinfo, $secret, $hash): string {
        if (empty($jsongroupinfo)) {
            return '';
        }

        $string = $secret . $jsongroupinfo;

        if ($hash != null) {
            $hash = hash($hash, $string);
        } else {
            $hash = $string;
        }

        return $hash;
    }

    /**
     * Adds the group info (groupinfo and groupinfohash) to the given data array.
     *
     * @param mixed[] $data the object to add groupinfo to!
     * @param string $secret the servers secret (for hashing group-infos!)
     * @param string $hash the servers hash-algorithm
     * @return void
     * @throws dml_exception
     */
    protected function add_group_data(array &$data, $secret, $hash): void {
        global $COURSE, $DB;

        // Check if this server needs group info.
        if ($this->obj->groupinfo == self::NO_GROUPINFO) {
            return;
        }

        // Add general group data!
        $groups = groups_get_all_groups($COURSE->id, 0, 0, 'g.id, g.name', true);
        ksort($groups);
        if (empty($groups)) {
            return;
        }

        $users = [];
        // First we fetch the users in 1 query!
        foreach ($groups as $grpid => $group) {
            $users += $group->members;
        }
        $users = $DB->get_records_list('user', 'id', $users, 'id ASC', 'id, username');

        // Now we replace the members property with an array of usernames or false if there are no members!
        foreach ($groups as $grpid => $group) {
            if (empty($group->members) || $group->members === ["" => null]) {
                $group->members = false;
                continue;
            }

            $grpmembers = [];
            foreach ($group->members as $userid) {
                $grpmembers[] = $users[$userid]->username;
            }

            $group->members = $grpmembers;
        }

        // So we get a json-array instead an object!
        $data['groupinfo'] = json_encode(array_values($groups));
        $data['groupinfohash'] = $this->calc_groups_hash($data['groupinfo'], $secret, $hash);
    }

    /**
     * Checks the connection to the external server
     *
     * @return bool true if extserver retuns with http OK else it returns false
     */
    public function check_connection(): bool {

        // Check prerequisites.
        if ($this->concheck != null) {
            return $this->concheck;
        }
        if ($this->obj == null) {
            return $this->concheck = false;
        }

        // Make request.
        $response = $this->http_request();

        // Check the HTTP code.
        if ($this->httpcode == 200) {
            $this->concheck = true;
        } else {
            $this->concheck = false;
        }

        return $this->concheck;
    }

    /**
     * Load grades from an external server
     *
     * @param stdClass $assignment
     * @param string[]|bool $userlist
     * @param bool $responseonly if true, only the response is returned, not the grades
     * @return bool|mixed:
     * @throws coding_exception
     * @throws dml_exception
     */
    public function load_grades($assignment, $userlist = false, $responseonly = false): mixed {
        global $USER;

        // Common parameters.
        $params = $this->get_common_params($assignment);

        // Additional parameters.
        $params['action'] = 'getgrades';
        $params['role'] = 'teacher';
        $this->add_group_data($params, $this->obj->auth_secret, $this->obj->hash);
        if ($userlist) {
            $params['unames'] = [];
            foreach ($userlist as $cur) {
                // We include the value in the array index because we don't want to overwrite our array elements!
                $params['unames'][] = $cur;
            }
        }
        $params['akey'] = $this->calc_akey($params, $this->obj->auth_secret, $this->obj->hash);

        // Make request.
        $response = $this->http_request($params);

        // Evaluate the result.
        if ($response) {
            // HTTP/1.0 200 OK.
            if ($this->httpcode == 200) {
                if ($responseonly) {
                    return $response;
                }

                // Xml to array.
                $parser = xml_parser_create('');
                xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, 'UTF-8');
                xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
                xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
                $result = xml_parse_into_struct($parser, trim($response), $xmlvalues);

                if ($result == 0) {
                    // Xml parse error.
                    return false;
                }
                xml_parser_free($parser);

                $grades = [];
                foreach ($xmlvalues as $elem) {
                    if ($elem['tag'] == 'submission') {
                        $elem['attributes']['username'] = $elem['attributes']['uname'];
                        $elem['attributes']['comment'] = $elem['value'];
                        array_push($grades, $elem['attributes']);
                    }
                }

                return $grades;
            }
        }

        return false;
    }

    /**
     * Uploads a file to the external server
     *
     * @param stored_file $file The file to upload.
     * @param stdClass $assignment The assignment object.
     * @param bool $notify Whether or not to notify the user of success/failure
     *
     * @return bool true if everything went right
     * @throws coding_exception
     * @throws dml_exception
     */
    public function upload_file($file, $assignment, $notify = true): bool {
        global $PAGE, $USER;

        // Get params.
        $url = $this->obj->form_url;
        $filename = $file->get_filename();
        $tmpfile = $file->copy_content_to_temp();

        // Common parameters.
        $params = $this->get_common_params($assignment);

        // Additional parameters.
        $params['action'] = 'submit';
        $params['role'] = 'student';
        $params['filename'] = $filename;
        $params['file'] = ['path' => $tmpfile, 'mime' => $file->get_mimetype(), 'filename' => $filename];
        $params['filehash'] = hash_file($this->obj->hash, $tmpfile);
        $params['akey'] = $this->calc_akey($params, $this->obj->auth_secret, $this->obj->hash);
        $this->add_group_data($params, $this->obj->auth_secret, $this->obj->hash);

        // Make request.
        $response = $this->http_request($params, 'POST');

        // Evaluate the result.
        if ($response) {
            if ($this->httpcode == 200) { // HTTP/1.0 200 OK.
                if ($notify) {
                    notification::add(
                        get_string('file_uploaded', 'assignsubmission_externalserver'),
                        \core\output\notification::NOTIFY_SUCCESS
                    );
                }
                return true;
            }
        }
        if ($notify) {
            notification::add($this->debuginfo, \core\output\notification::NOTIFY_ERROR);
        }
        return false;
    }

    /**
     * Generates the url to get the view for a teacher
     *
     * @param stdClass $assignment
     * @param string $studusername
     * @return string
     * @throws coding_exception
     * @throws dml_exception
     */
    public function build_teacherview($assignment, $studusername = ''): string {
        global $USER;

        // Common parameters.
        $params = $this->get_common_params($assignment);

        // Additional parameters.
        $params['action'] = 'view';
        $params['role'] = 'teacher';
        $params['studusername'] = $studusername;
        if ($this->obj != null) {
            $params['akey'] = $this->calc_akey($params, $this->obj->auth_secret, $this->obj->hash);
        } else {
            $params['akey'] = '';
        }
        $this->add_group_data($params, $this->obj->auth_secret, $this->obj->hash);

        // Get the URL.
        if ($this->obj != null) {
            $url = $this->obj->url;
        } else {
            $url = '';
        }
        $url = "$url?" . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
        return $url;
    }

    /**
     * Returns the debug info.
     *
     * @return string the debuginfo
     */
    public function get_debuginfo(): string {
        return $this->debuginfo;
    }

    /**
     * Returns the http status code.
     *
     * @return string http status code
     */
    public function get_httpcode(): string|bool {
        return $this->httpcode;
    }

    /**
     * Returns success of last request.
     *
     * @return string the debuginfo
     */
    public function get_success(): string {
        $code = (int)$this->httpcode;
        return $code >= 200 && $code < 300;
    }

    /**
     * Generates the url to get the view for a student.
     *
     * @param stdClass $assignment
     * @return string
     * @throws coding_exception
     * @throws dml_exception
     */
    public function url_studentview($assignment): string {
        global $USER;

        // Common parameters.
        $params = $this->get_common_params($assignment);

        // Additional parameters.
        $params['action'] = 'view';
        $params['role'] = 'student';
        $params['akey'] = $this->calc_akey($params, $this->obj->auth_secret, $this->obj->hash);
        $this->add_group_data($params, $this->obj->auth_secret, $this->obj->hash);

        // Get the URL.
        $url = $this->obj->url;
        $url = "$url?" . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
        return $url;
    }

    /**
     * Displays the iframe.
     *
     * @param stdClass $assignment
     * @throws coding_exception
     * @throws dml_exception
     */
    public function view_externalframe($assignment): string {
        global $OUTPUT;

        $url = $this->url_studentview($assignment);
        $html = $OUTPUT->box_start('assignsubmission-externalserver-iframe-container');
        $html .= '<iframe src="' . $url . '" class="assignsubmission-externalserver-iframe border bg-light"></iframe>';
        $html .= $OUTPUT->box_end();

        return $html;
    }

    /**
     * Prints the server's response.
     *
     * @param string $title Headline for this server response
     * @param string $content The server response's content
     * @param bool $ok Whether or not the response is ok
     */
    public function print_response($title, $content, $ok): void {

        static $i = 0;
        $id = 'collapse-section-' . $i++;

        if ($ok) {
            $textclass = 'success';
            $symbol = '<i class="fa fa-check-square-o ml-1" aria-hidden="true"></i>';
        } else {
            $textclass = 'danger';
            $symbol = '<i class="fa fa-exclamation-triangle text-danger ml-1"></i>';
        }

        if (!$ok && empty($content)) {
            $httpcode = $this->get_httpcode();
            $content = ($httpcode === 0)
                ? get_string('sslerror', 'assignsubmission_externalserver')
                : get_string('unknownerror', 'assignsubmission_externalserver', $httpcode);
        }

        $summary = html_writer::tag(
            'summary',
            $title . $symbol,
            ['class' => "h4 text-$textclass", 'data-behat' => "$textclass-$i"]
        );
        $content = html_writer::div($content, 'mb-3');
        echo html_writer::tag('details', $summary . "<pre>$content</pre>", ['class' => 'moodle-collapsible extserver-result ml-4']);
    }

    /**
     * Get grades/feedback from external server.
     *
     * @param assign $assignment The assignment instance
     * @param array $userids The list of userids to grade
     *
     * @return array
     */
    public function grade_submissions($assignment, $userids): array {

        global $SESSION, $CFG, $COURSE, $PAGE, $DB, $OUTPUT, $USER;
        require_once($CFG->libdir . '/gradelib.php');
        $assign = $assignment->get_instance();

        // Check if assignment grading is set to numeric.
        $grademax = $assign->grade;
        if (!$grademax > 0) {
            $result['status'] = 'error';
            $result['message'] = get_string('nonnumericgrade', 'assignsubmission_externalserver');
            return $result;
        }

        // No users found.
        if (!$userids || empty($userids)) {
            $result['status'] = 'warning';
            $result['message'] = get_string('nothingtograde', 'assignsubmission_externalserver');
            return $result;
        }

        // Create a list of userids and usernames.
        $userlist = [];
        $useridlist = [];
        foreach ($userids as $userid) {
            $user = \core_user::get_user($userid);
            $userlist[$user->id] = $user->username;
            $useridlist[$user->username] = $user->id;
        }

        // Load grades from external server.
        if (!$extgrades = $this->load_grades($assign, $userlist)) {
            $result['status'] = 'error';
            $result['message'] = get_string('couldnotgetgrades', 'assignsubmission_externalserver');
            return $result;
        }

        // Check if feedback comments are enabled.
        $feedbackendabled = false;
        $feedbackplugins = $assignment->get_feedback_plugins();
        foreach ($feedbackplugins as $plugin) {
            if ($plugin->get_type() == 'comments' && $plugin->is_enabled() && $plugin->is_visible()) {
                $feedbackendabled = true;
                break;
            }
        }

        // Process grades.
        $updated = 0;
        foreach ($extgrades as $extgrade) {
            $username = $extgrade['username'];
            $comment = $extgrade['comment'];
            if (array_key_exists($username, $useridlist)) {
                $userid = $useridlist[$username];

                // Grade.
                $grade = new stdClass();
                $grade->userid = $userid;
                $grade->grade  = $extgrade['grade'];
                $grade->attemptnumber = -1;
                $grade->addattempt = false;

                // Comment.
                if ($feedbackendabled) {
                    $grade->assignfeedbackcomments_editor = [
                        'text' => $comment,
                        'format' => FORMAT_PLAIN,
                        'itemid' => 0,
                    ];
                }

                // Save.
                if ($assignment->get_instance()->teamsubmission) {
                    $group = $assignment->get_submission_group($userid);
                    $members = $assignment->get_submission_group_members($group->id, true);
                    foreach ($members as $member) {
                        $assignment->save_grade($member->id, $grade);
                    }
                } else {
                    $assignment->save_grade($userid, $grade);
                }
                $updated++;
            }
        }

        // Much success, very wow.
        $result['status'] = 'success';
        $result['message'] = get_string('gradesupdated', 'assignsubmission_externalserver', $updated);
        return $result;
    }

    /**
     * Get common parameters for external server requests.
     *
     * @param stdClass $assignment The assignment object.
     *
     * @return array
     */
    private function get_common_params($assignment): array {
        global $USER;

        if ($cm = get_coursemodule_from_instance('assign', $assignment->id, $assignment->course, false)) {
            $timecreated = $cm->added;
        } else {
            // Should only happen in behat tests and on fresh installs.
            $timecreated = time();
        }

        return [
            'timestamp' => time(),
            'user' => $USER->username,
            'skey' => $USER->sesskey,
            'uidnr' => $USER->idnumber,
            'cidnr' => $assignment->course,
            'aid' => $timecreated,
            'aname' => $assignment->name,
            'fname' => $USER->firstname,
            'lname' => $USER->lastname,
        ];
    }

    /**
     * Build header for external server requests.
     *
     * @return array
     */
    public function get_headers(): array {

        $authtype = $this->obj->auth_type;
        $headers = [
            'Content-Type' => 'application/json',
        ];

        // Headers for authentication.
        if ($authtype == 'oauth2' || $authtype == 'jwt') {
            $headers['Authorization'] = 'Bearer ' . $this->get_oauth2_token();
        }

        return $headers;
    }

    /**
     * Get OAuth2 token.
     *
     * @return string OAuth2 token
     */
    private function get_oauth2_token(): string {

        // Get params.
        $tokenurl = $this->obj->oauth2_token_endpoint;
        $clientid = $this->obj->oauth2_client_id;
        $secret = $this->obj->oauth2_client_secret;

        // Check cache.
        $cachename = 'assignsubmission_externalserver_' . $this->obj->id . '_' . $this->obj->auth_type;
        if ($cached = $this->get_cached_token($cachename)) {
            return $cached;
        }

        // Build the token request.
        $response = $this->httpclient->post($tokenurl, [
            'headers' => [
                'Accept' => 'application/json',
            ],
            'form_params' => [
                'grant_type'    => 'client_credentials',
                'client_id'     => $clientid,
                'client_secret' => $secret,
            ],
            'timeout' => 10,
        ]);

        // Get the token from the response.
        $statuscode = $response->getStatusCode();
        $body = json_decode($response->getBody()->getContents(), true);

        if ($statuscode != 200 || !isset($body['access_token'])) {
            \core\notification::add(
                get_string('error:couldnotgetoauth2token', 'assignsubmission_externalserver', $statuscode),
                \core\output\notification::NOTIFY_ERROR
            );
            return '';
        }

        // Get token and cache it.
        $token = $body['access_token'];
        $ttl   = isset($body['expires_in']) ? (int)$body['expires_in'] : 3600;
        $this->cache_token($cachename, $token, $ttl);
        return $token;
    }

    /**
     * Get cached token.
     *
     * @param string $key Token key
     * @return string|null Token value or null if not found/expired
     */
    private function get_cached_token(string $key): ?string {
        $rec = get_config('assignsubmission_externalserver', 'tok_' . $key);
        if (!$rec) {
            return null;
        }
        $data = json_decode($rec, true);
        if (!$data) {
            return null;
        }
        if (time() >= (int)$data['exp']) {
            return null;
        }
        return $data['val'];
    }

    /**
     * Cache token.
     *
     * @param string $key Token key
     * @param string $token Token value
     * @param int $ttl Time to live in seconds
     * @return void
     */
    private function cache_token(string $key, string $token, int $ttl): void {
        $exp = time() + max(60, $ttl - 60);
        set_config('tok_' . $key, json_encode(['val' => $token, 'exp' => $exp]), 'assignsubmission_externalserver');
    }

    /**
     * Make an HTTP request to the external server.
     *
     * @param array $params Parameters for the request.
     * @param string $type HTTP method type (GET or POST).
     * @param string|null $url Optional URL to override the base URL.     *
     * @return false|mixed The response from the server or false on failure.
     * @throws \GuzzleHttp\Exception\RequestException
     */
    public function http_request(array $params = [], $type = 'GET', $url = null): mixed {
        try {
            $payload = [
                'headers' => $this->get_headers(),
                'http_errors' => false,
            ];

            // GET.
            if ($type === 'GET') {
                // Either get payload from params or use the given URL.
                if ($url === null) {
                    $url = $this->obj->url;
                }
                if (!empty($params)) {
                    $payload['query'] = $params;
                }

                $response = $this->httpclient->get($url, $payload);
            } else {
                // POST - convert file uploads to multipart/form-data.
                if (!empty($params['file'])) {
                    unset($payload['headers']['Content-Type']); // Guzzle will set the correct Content-Type for multipart requests.
                    $payload['multipart'] = $this->convert_to_multipart($params);
                    $url = $this->obj->form_url;
                } else {
                    $payload['form_params'] = $params;
                    $url = $this->obj->url;
                }
                $response = $this->httpclient->post($url, $payload);
            }

            $result = $response->getBody()->getContents();
            $resulthtml = '<div class="d-inline-block alert alert-info m-3">' . $result . '</div>';
            $this->httpcode = $response->getStatusCode();
            $this->debuginfo = $this->get_debuginfo_from_response($response) . $resulthtml;
            return $result;
        } catch (RequestException $e) {
            $this->debuginfo = $e->getMessage();
            $this->httpcode = 0;
            $error = get_string('error:requestfailed', 'assignsubmission_externalserver', $this->debuginfo);
               \core\notification::add($error, \core\output\notification::NOTIFY_ERROR);
            return false;
        }
    }

    /**
     * Set debug information from the response.
     *
     * @param \GuzzleHttp\Psr7\Response $response The response object.
     * @return void
     */
    private function get_debuginfo_from_response($response): string {
        $status = $response->getStatusCode();
        $reason = $response->getReasonPhrase();
        $headers = $response->getHeaders();

        // Reconstruct headers.
        $headerstring = "HTTP/1.1 {$status} {$reason}\n";
        foreach ($headers as $name => $values) {
            foreach ($values as $value) {
                $headerstring .= "{$name}: {$value}\n";
            }
        }

        return $headerstring;
    }

    /**
     * Converts the given parameters to a multipart array for file upload.
     *
     * @param array $params The parameters to convert.
     * @return array The multipart array.
     */
    private function convert_to_multipart(array $params): array {
        $multipart = [];

        foreach ($params as $key => $value) {
            if ($key === 'file' && is_array($value)) {
                $multipart[] = [
                    'name'     => $key,
                    'contents' => fopen($value['path'], 'r'),
                    'filename' => $value['filename'],
                    'headers'  => [
                        'Content-Type' => $value['mime'],
                    ],
                ];
            } else {
                $multipart[] = [
                    'name'     => $key,
                    'contents' => $value,
                ];
            }
        }

        return $multipart;
    }
}
