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
 * Demo package using OAuth2: hash functions to verify requests.
 *
 * This was mostly re-used from the old external server demo package.
 *
 * @package    assignsubmission_externalserver
 * @author     Andreas Hruska <andreas.hruska@elearning.tuwien.ac.at>
 * @author     Katarzyna Potocka <katarzyna.potocka@elearning.tuwien.ac.at>
 * @author     Stefan Weber <stefan.weber@think-modular.com>
 * @copyright  2025 think-modular
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/** @const string[] array of params to include in akey */
$AKEYPARAMS = array('timestamp', 'user', 'skey', 'uidnr', 'action', 'cidnr',
                    'aid', 'aname', 'fname', 'lname', 'role');

// Params from actionparams can be arrays too (for example unames), these are handled separately!
$ACTIONPARAMS = array('submit'      => array('filename', 'filehash'),
                      'teacherview' => array('studusername'),
                      'getgrades'   => array('unames'));


/**
 * Check payload integrity by verifying the akey.
 *
 * @param array $params parameters received from the client
 * @param string $akey akey received from the client
 * @return bool true if akey is valid, false otherwise
 */
function check_akey($params, $akey): bool {
    global $AKEYPARAMS, $ACTIONPARAMS;

    $hash = HASH_ALGO;
    $string = SECRET_KEY;

    // Add general parameters.
    foreach ($AKEYPARAMS as $param) {
        if (!array_key_exists($param, $params)) {
            header("HTTP/1.0 400 Bad Request");
            echo "missing params";
            die();
        } else {
            // Add specified params to akey calculation!
            $string .= $params[$param];
        }
    }

    // Add action specific params.
    $action = $params['action'];
    if ($action == 'view' && $params['role'] == 'teacher') {
        $action = 'teacherview';
    } else {
        // We don't need studusername in student view!
        $action = 'studentview';
    }
    if (key_exists($action, $ACTIONPARAMS) && !empty($ACTIONPARAMS[$action])) {
        foreach ($ACTIONPARAMS[$action] as $param) {
            if (!array_key_exists($param, $params)) {
                header("HTTP/1.0 400 Bad Request");
                echo "missing params";
                die();
            } else {
                // Add specified params to akey calculation!
                if (is_array($params[$param])) {
                    // This is an array, we have to take care of it explicitly...
                    $sorted = array_ksort($params[$param]);
                    $string .= implode($param, $sorted);
                } else {
                    $string .= $params[$param];
                }
            }
        }
    }

    $hash = hash($hash, $string);

    // Compare the generated and provided session key.
    if ($hash == $akey) {
        return true;
    }

    // Authentication failed.
    return false;
}

/**
 * Check group info integrity.
 *
 * @param string $groupinfo group info json string
 * @param string $groupinfohash expected group info hash
 * @return bool true if group info hash matches, false otherwise
 */
function check_groupinfo($groupinfo, $groupinfohash): bool {
    // common server secret
    $secret = get_secret();
    $hash = get_hash_algorithm();

    // calculate the session key
    $string = $secret.$groupinfo;

    $hash = hash($hash, $string);

    // compare the generated and provided session key
    if ($hash == $groupinfohash) {
        // if the generated hash matches the provided hash, it's valid!
        return true;
    }

    // if we got here, the provided hash for the groupinfos is not valid
    // and we have to assume, somebody manipulated the traffic!

    return false;
}

/**
 * Check file hash integrity.
 *
 * @param string $filename path to the file to check
 * @param string $filehash expected file hash
 * @return bool true if file hash matches, false otherwise
 */
function check_file_hash($filename, $filehash): bool {
    $uploadhash = hash_file(get_hash_algorithm(), $filename);
    if ($uploadhash == $filehash) {
        return true;
    }
    return false;
}

/**
 * Get group info text.
 *
 * @param string $username
 * @param string $groupinfo
 * @param string $groupinfohash
 * @return string
 */
function get_groupinfo_txt($username, $groupinfo, $groupinfohash): string {
    if (!check_groupinfo($groupinfo, $groupinfohash)) {
        header("HTTP/1.0 418 I'm a teapot! (Somebody tried to alter the submitted groupinfos!");
        echo "Submitted groupinfos invalid, due to hash mismatch!";
        die;
    }
    $groups = json_decode($groupinfo);
    if ($groups === null) {
        $groups = json_last_error_message();
    }

    // Determine group of the student.
    $groupids = [];
    $groupnames = [];
    foreach ($groups as $group) {
        $groupmembers = $group->members;
        if (is_array($groupmembers) && in_array($username, $groupmembers)) {
            $groupids[] = $group->id;
            $groupnames[] = $group->name;
        }
    }

    $groupids = implode(', ', $groupids);
    $groupnames = implode(', ', $groupnames);

    $html = "<br />group ID(s): $groupids";
    $html .= "<br />group name(s): $groupnames";

    return $html;
}
