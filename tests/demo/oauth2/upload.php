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
 * Demo package using OAuth2: assignment endpoint for an external server.
 *
 * @package    assignsubmission_externalserver
 * @author     Stefan Weber <stefan.weber@think-modular.com>
 * @copyright  2025 think-modular
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/config.php');

// OAuth2 authorization.
require_valid_access_token();

// Get variables.
if (!empty($_POST["akey"])) {
    $values = $_POST;
} else {
    $values = array();
}
$timestamp = $_POST["timestamp"];
$user =     $_POST["user"];
$skey =     $_POST["skey"];
$uidnr =    $_POST["uidnr"];
$action =   $_POST["action"];
$cidnr =    $_POST["cidnr"];
$aid =      $_POST["aid"];
$aname =    $_POST["aname"];
$fname =    $_POST["fname"];
$lname =    $_POST["lname"];
$role =     $_POST["role"];
$filename = $_POST["filename"];
$filehash = $_POST["filehash"];
$akey =      $_POST["akey"];
if (key_exists("groupinfo", $_GET)) {
    $groupinfo = $_GET["groupinfo"];
    $groupinfohash = $_GET["groupinfohash"];
} else {
    $groupinfo = false;
}
if ($groupinfo !== false) {
    if (!check_groupinfo($groupinfo, $groupinfohash)) {
        header("HTTP/1.0 418 I'm a teapot! (Somebody tried to alter the submitted groupinfos!");
        echo "Submitted groupinfos invalid, due to hash mismatch!";
        die;
    }
    $groupinfo = json_decode($groupinfo);
    if ($groupinfo === null) {
        $groupinfo = json_last_error_message();
    }
    $groupinfotxt = "<br />Groupinfo:<pre>".print_r($groupinfo, true)."</pre>";
}

// Verzeichnis
$upload_dir = __DIR__ . '/uploads/';

// Wurde wirklich eine Datei hochgeladen?
if (is_uploaded_file($_FILES["file"]["tmp_name"])) {
    if (!check_file_hash($_FILES["file"]["tmp_name"], $values["filehash"])) {
        header("HTTP/1.0 400 Bad Request");
        echo "File hashes doesn't match. File has probably been modified!\n";
    }

    // Alles OK -> Datei kopieren
    if (move_uploaded_file($_FILES["file"]["tmp_name"], $upload_dir.$filename)) {
        header("HTTP/1.0 200 OK");
        echo "File uploaded successfully!";
        die();

    } else {
      header("HTTP/1.0 500 Internal Server Error");
      echo "File could not be uploaded.\n" . $upload_dir.$filename . "\nend";
      die();
    }
} else {
    header("HTTP/1.0 400 Bad Request");
    echo "No file selected for upload.";
    die();
}

?>