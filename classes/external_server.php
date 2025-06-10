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
 * @package    assignsubmission_external_server
 * @author     Stefan Weber
 * @copyright  2025 think-modular
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace assignsubmission_external_server;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/extserver/lib.php');
require_once($CFG->libdir . '/pdflib.php');

/**
 * This class represents an external server (not an instance of external server the moodle module)
 *
 * @package    assignsubmission_external_server
 * @author     Stefan Weber
 * @copyright  2025 think-modular
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class external_server {

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

        if ($id != 0) {
            $this->obj = $DB->get_record('assignsubmission_external_server_servers', ['id' => $id]);
            if ($this->obj->hash == null) {
                $this->obj->hash = 'sha256';
            }
        }
    }

    /**
     * Gets a specific server by ID.
     *
     * @param int $id ID of the external server in the DB
     * @return stdClass|null DB record or null if not found
     */
    public static function get_server($id) {
        global $DB;
        return $DB->get_record('assignsubmission_external_server_servers', ['id' => $id]);
    }

    /**
     * Retrieves all active/visible external servers from the DB
     *
     * @return array DB records
     * @throws dml_exception
     */
    public static function get_servers() {
        global $DB;

        $servers = $DB->get_records('assignsubmission_external_server_servers', ['visible' => '1']);

        return $servers;
    }

    /**
     * Retrieves all external servers from the DB
     *
     * @return array DB records
     * @throws dml_exception
     */
    public static function get_all_servers() {
        global $DB;

        $servers = $DB->get_records('assignsubmission_external_server_servers');

        return $servers;
    }

    /**
     * Static method to delete an external server
     *
     * @param int $id ID of the external server in the DB
     * @return true
     * @throws dml_exception A DML specific exception is thrown for any errors.
     */
    public static function delete_server($id) {
        global $DB;

        if (!is_numeric($id)) {
            return false;
        }

        $ret = $DB->delete_records('assignsubmission_external_server_servers', ['id' => $id]);

        return $ret;
    }

    /**
     * Calculates the key which is used for security
     * @param string[]|string[][] $params all the params used for the request, which should be hashed
     * @param string $secret server secret
     * @param string $hash hashing algorithm to use!
     * @return string
     * @throws coding_exception
     */
    public function calc_akey($params, $secret, $hash) {
        global $OUTPUT;

        $string = $secret;

        // Add general parameters!
        foreach (self::AKEY_PARAMS as $param) {
            if (!array_key_exists($param, $params)) {
                echo $OUTPUT->box_start('generalbox').
                     $OUTPUT->notification(get_string('missing_hash_param', 'extserver', $param), 'notifyproblem').
                     $OUTPUT->box_end();
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
                    echo $OUTPUT->box_start('generalbox').
                         $OUTPUT->notification(get_string('missing_hash_param', 'extserver', $param), 'notifyproblem').
                         $OUTPUT->box_end();
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
     * @param string $jsongroupinfo json encoded group information
     * @param string $secret server secret
     * @param string $hash hashing algorithm to use!
     * @return string
     */
    public function calc_groups_hash($jsongroupinfo, $secret, $hash) {
        if (empty($jsongroupinfo)) {
            return '';
        }

        $string = $secret.$jsongroupinfo;

        if ($hash != null) {
            $hash = hash($hash, $string);
        } else {
            $hash = $string;
        }

        return $hash;
    }

    /**
     * Adds the group info (groupinfo and groupinfohash) to the given data array
     *
     * @param mixed[] $data the object to add groupinfo to!
     * @param string $secret the servers secret (for hashing group-infos!)
     * @param string $hash the servers hash-algorithm
     * @return void
     * @throws dml_exception
     */
    protected function add_group_data(array &$data, $secret, $hash) {
        global $COURSE, $DB;

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
     * Sends the external server a request that a new assignment was created
     * @deprecated this now works implicitly if a teacher views an assignment wich the server didnt know befor
     * @param stdClass $assignment
     * @return NULL|boolean
     * @throws coding_exception
     * @throws dml_exception
     */
    public function create_assignment($assignment) {
        global $USER;

        if ($this->concheck != null) {
            return $this->concheck;
        }

        if ($this->obj == null) {
            return $this->concheck = false;
        }

        $ch = curl_init();
        // Set URL and other appropriate options.
        curl_setopt($ch, CURLOPT_URL, $this->obj->server_url);

        $postdata = [
            'timestamp' => time(),
            'user' => $USER->username,
            'skey' => $USER->sesskey,
            'uidnr' => $USER->idnumber,
            'action' => 'create',
            'cidnr' => $assignment->course,
            'aid' => $assignment->timecreated,
            'aname' => $assignment->name,
            'fname' => $USER->firstname,
            'lname' => $USER->lastname,
            'role' => 'teacher',
        ];
        $postdata['akey'] = $this->calc_akey($postdata, $this->obj->server_secret, $this->obj->hash);
        $this->add_group_data($postdata, $this->obj->server_secret, $this->obj->hash);

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, !empty($this->obj->sslverification));
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $this->obj->sslverification);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $postresult = curl_exec($ch);

        $curlinfo = curl_getinfo($ch);
        $this->debuginfo = curl_multi_getcontent($ch);
        curl_close($ch);

        if ($postresult) {
            $this->httpcode = $curlinfo['http_code'];

            // HTTP/1.0 201 Created.
            if ($curlinfo['http_code'] == 201) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks the connection to the external server
     *
     * @return bool true if extserver retuns with http OK else it returns false
     */
    public function check_connection() {
        if ($this->concheck != null) {
            return $this->concheck;
        }

        if ($this->obj == null) {
            return $this->concheck = false;
        }

        $ch = curl_init();
        // Set URL and other appropriate options.
        curl_setopt($ch, CURLOPT_URL, $this->obj->server_url);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_exec($ch);
        $info = curl_getinfo($ch);
        $this->debuginfo = curl_multi_getcontent($ch);
        curl_close($ch);

        $this->httpcode = $info['http_code'];

        if ($info['http_code'] == 200) {
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
     * @return bool|mixed:
     * @throws coding_exception
     * @throws dml_exception
     */
    public function load_grades($assignment, $userlist = false) {
        global $USER;

        $url = $this->obj->server_url;

        $vars = [
        'timestamp' => time(),
        'user' => $USER->username,
        'skey' => $USER->sesskey,
        'uidnr' => $USER->idnumber,
        'action' => 'getgrades',
        'cidnr' => $assignment->course,
        'aid' => $assignment->timecreated,
        'aname' => $assignment->name,
        'fname' => $USER->firstname,
        'lname' => $USER->lastname,
        'role' => 'teacher',
        ];
        if ($userlist) {
            $vars['unames'] = [];
            foreach ($userlist as $cur) {
                // We include the value in the array index because we don't want to overwrite our array elements!
                $vars['unames'][] = $cur;
            }
        }
        $vars['akey'] = $this->calc_akey($vars, $this->obj->server_secret, $this->obj->hash);
        $this->add_group_data($vars, $this->obj->server_secret, $this->obj->hash);

        $url = $this->build_getrequest($url, $vars);

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, !empty($this->obj->sslverification));
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $this->obj->sslverification);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($ch);

        $curlinfo = curl_getinfo($ch);
        $this->debuginfo = curl_multi_getcontent($ch);
        curl_close($ch);

        $this->httpcode = $curlinfo['http_code'];

        if ($result) {
            $xmlstr = $result;
            // HTTP/1.0 200 OK.
            if ($curlinfo['http_code'] == 200) {
                // Xml to array.
                $parser = xml_parser_create('');
                xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, 'UTF-8');
                xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
                xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
                $result = xml_parse_into_struct($parser, trim($xmlstr), $xmlvalues);

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
     * @param string $filename The filename to use for the file
     * @param string $tmpfile The temporary filename of the file
     * @param stdClass $assignment The extserver instance
     * @return bool true if everything went right
     * @throws coding_exception
     * @throws dml_exception
     */
    public function upload_file($filename, $tmpfile, $assignment) {
        global $USER;

        $url = $this->obj->serverform_url;

        $ch = curl_init($url);

        $postdata = [
            'timestamp' => time(),
            'user' => $USER->username,
            'skey' => $USER->sesskey,
            'uidnr' => $USER->idnumber,
            'action' => 'submit',
            'cidnr' => $assignment->course,
            'aid' => $assignment->timecreated,
            'aname' => $assignment->name,
            'fname' => $USER->firstname,
            'lname' => $USER->lastname,
            'role' => 'student',
            'filename' => $filename,
        ];

        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);

        if (class_exists('CurlFile')) {
            // Since PHP 5.5!
            $postdata['file'] = new CurlFile($tmpfile);
            $postdata['file']->setPostFilename($filename);

        } else {
            $postdata['file'] = "@$tmpfile";
        }

        $postdata['filehash'] = hash_file($this->obj->hash, $tmpfile);
        $postdata['akey'] = $this->calc_akey($postdata, $this->obj->server_secret, $this->obj->hash);
        $this->add_group_data($postdata, $this->obj->server_secret, $this->obj->hash);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, !empty($this->obj->sslverification));
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $this->obj->sslverification);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $postresult = curl_exec($ch);
        $curlinfo = curl_getinfo($ch);
        $this->debuginfo = curl_multi_getcontent($ch);
        curl_close($ch);

        $this->httpcode = $curlinfo['http_code'];

        if ($postresult) {
            // HTTP/1.0 200 OK.
            if ($curlinfo['http_code'] == 200) {
                return true;
            }
        }
        echo $this->debuginfo;

        return false;
    }

    /**
     * generates the url to get the view for a teacher
     *
     * @param stdClass $assignment
     * @param string $studusername
     * @return string
     * @throws coding_exception
     * @throws dml_exception
     */
    public function url_teacherview($assignment, $studusername = '') {
        return $this->build_teacherview($assignment, $studusername);
    }

    /**
     * does the actual url generation
     *
     * @param stdClass $assignment
     * @param string $studusername
     * @return string
     * @throws coding_exception
     * @throws dml_exception
     */
    public function build_teacherview($assignment, $studusername = '') {
        global $USER;

        if ($this->obj != null) {
            $url = $this->obj->server_url;
        } else {
            $url = '';
        }

        $vars = [
            'timestamp' => time(),
            'user' => $USER->username,
            'skey' => $USER->sesskey,
            'uidnr' => $USER->idnumber,
            'action' => 'view',
            'cidnr' => $assignment->course,
            'aid' => $assignment->timecreated,
            'aname' => $assignment->name,
            'fname' => $USER->firstname,
            'lname' => $USER->lastname,
            'role' => 'teacher',
            'studusername' => $studusername,
        ];
        if ($this->obj != null) {
            $vars['akey'] = $this->calc_akey($vars, $this->obj->server_secret, $this->obj->hash);
        } else {
            $vars['akey'] = '';
        }
        $this->add_group_data($vars, $this->obj->server_secret, $this->obj->hash);

        $url = $this->build_getrequest($url, $vars);
        return $url;
    }

    /**
     * returns the debug info
     *
     * @return string the debuginfo
     */
    public function get_debuginfo() {
        return $this->debuginfo;
    }

    /**
     * returns the http status code
     *
     * @return string http status code
     */
    public function get_httpcode() {
        return $this->httpcode;
    }

    /**
     * generates the url to get the view for a student
     * @param stdClass $assignment
     * @return string
     * @throws coding_exception
     * @throws dml_exception
     */
    public function url_studentview($assignment) {
        global $USER;

        $url = $this->obj->server_url;

        $vars = [
            'timestamp' => time(),
            'user' => $USER->username,
            'skey' => $USER->sesskey,
            'uidnr' => $USER->idnumber,
            'action' => 'view',
            'cidnr' => $assignment->course,
            'aid' => $assignment->timecreated,
            'aname' => $assignment->name,
            'fname' => $USER->firstname,
            'lname' => $USER->lastname,
            'role' => 'student',
        ];

        $vars['akey'] = $this->calc_akey($vars, $this->obj->server_secret, $this->obj->hash);
        $this->add_group_data($vars, $this->obj->server_secret, $this->obj->hash);

        $url = $this->build_getrequest($url, $vars);

        return $url;
    }

    /**
     * displays the iframe
     * @param stdClass $assignment
     * @throws coding_exception
     * @throws dml_exception
     */
    public function view_externalframe($assignment) {
        global $OUTPUT;

        $url = $this->url_studentview($assignment);

        echo $OUTPUT->box_start('external_frame');
        echo '<iframe src="' . $url . '" class="externalframe"></iframe>';
        echo $OUTPUT->box_end();
    }

    /**
     * generates a request for an external server
     * @param string $url
     * @param array $vars
     * @return string
     */
    public function build_getrequest($url, $vars) {
        $url .= '?';

        foreach ($vars as $idx => $value) {
            if (is_array($value)) {
                foreach ($value as $validx => $curval) {
                    $url .= $idx.'['.$validx.']='.rawurlencode($curval).'&';
                }
            } else {
                $url .= $idx.'='.rawurlencode($value).'&';
            }
        }

        return $url;
    }
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
function mod_extserver_get_outcomes($usesoutcomes, $gradinginfo, $auser, $quickgrade, &$tabindex) {
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
function set_grading_successful(&$grades, &$submission, $curgrade, $timemarked) {
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
