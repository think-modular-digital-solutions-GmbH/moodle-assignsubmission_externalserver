<?php
# This software is provided under the GNU General Public License # http://www.gnu.org/licenses/gpl.html with Copyright &copy; 2009 onwards
#
# Dipl.-Ing. Andreas Hruska
# andreas.hruska@elearning.tuwien.ac.at
#
# Dipl.-Ing. Mag. rer.soc.oec. Katarzyna Potocka
# katarzyna.potocka@elearning.tuwien.ac.at
#
# Vienna University of Technology
# E-Learning Center
# Gu§hausstra§e 28/E015
# 1040 Wien
# http://elearning.tuwien.ac.at/
# ---------------------------------------------------------------
# FOR Moodle 2.5.3
# ---------------------------------------------------------------

include("lib.php");

//COURSE ID 0 ist Testrequest

// get variables
// get variables
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

if (!check_akey($values, $akey)) {
    header("HTTP/1.0 401 Unauthorized");
    echo "Session could not be verified - wrong akey";
    die();
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