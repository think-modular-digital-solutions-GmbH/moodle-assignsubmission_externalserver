<?php
# This software is provided under the GNU General Public License # http://www.gnu.org/licenses/gpl.html with Copyright &copy; 2009 onwards
#
# Philipp Hager
#
# Dipl.-Ing. Andreas Hruska
# andreas.hruska@elearning.tuwien.ac.at
#
# Dipl.-Ing. Mag. rer.soc.oec. Katarzyna Potocka
# katarzyna.potocka@elearning.tuwien.ac.at
#
# Vienna University of Technology
# E-Learning Center
# Gußhausstraße 28/E015
# 1040 Wien
# http://elearning.tuwien.ac.at/
# ---------------------------------------------------------------
# FOR Moodle 3.1+
# ---------------------------------------------------------------
/** @const string[] array of params to include in akey */
$akeyparams = array('timestamp', 'user', 'skey', 'uidnr', 'action', 'cidnr',
                    'aid', 'aname', 'fname', 'lname', 'role');

// Params from actionparams can be arrays too (for example unames), these are handled separately!
$actionparams = array('submit'      => array('filename', 'filehash'),
                      'teacherview' => array('studusername'),
                      'getgrades'   => array('unames'));

function get_secret(): string {
    $secret = '2345678987654';
    return $secret;
}

function get_hash_algorithm(): string {
    $hash = 'sha256'; // TODO: change this to your actual hash algorithm!
    return $hash;
}

// function to check the akey
function check_akey($params, $akey): bool {
    global $akeyparams, $actionparams;

    // common server secret
    $secret = get_secret();
    $hash = get_hash_algorithm();

    // calculate the session key
    $string = $secret;

    // Add general parameters!
    foreach ($akeyparams as $param) {
        if (!array_key_exists($param, $params)) {
            header("HTTP/1.0 400 Bad Request");
            echo "missing params";
            die();
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
    if (key_exists($action, $actionparams) && !empty($actionparams[$action])) {
        foreach ($actionparams[$action] as $param) {
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

    // compare the generated and provided session key
    if ($hash == $akey) {  // if the generated session key matches the provided key is valid
        return true;
    }

    // if we got here, the provided session key for the user is not valid
    // and the authentication procedure result is negative

    return false;
}

// function to check the groupinfo's integrity
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

// function to check the file hash
function check_file_hash($filename, $filehash): bool {
    $uploadhash = hash_file(get_hash_algorithm(), $filename);

    if ($uploadhash == $filehash) {
        return true;
    }

    return false;
}

function assignment_exists($aid): bool {
  // Check if an assignment already exists

  return true;
}
?>
