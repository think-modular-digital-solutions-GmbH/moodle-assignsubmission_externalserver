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
 * Demo package using OAuth2: assignment endpoint for an external server
 *
 * @package    assignsubmission_externalserver
 * @author     Stefan Weber <stefan.weber@think-modular.com>
 * @copyright  2025 think-modular
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/config.php');

// OAuth2 authorization.
require_valid_access_token();

// No payload - just checking if server is running.
if (empty($_GET["akey"])) {
    header("HTTP/1.0 200 OK");
    echo "Available";
    die();
}

// Get variables.
$timestamp = $_GET["timestamp"];
$user = $_GET["user"];
$skey = $_GET["skey"];
$uidnr = $_GET["uidnr"];
$action = $_GET["action"];
$cidnr = $_GET["cidnr"];
$aid = $_GET["aid"];
$aname = $_GET["aname"];
$fname = $_GET["fname"];
$lname = $_GET["lname"];
$role = $_GET["role"];
$akey = $_GET["akey"];
$filename = isset($_GET["filename"]) ? $_GET["filename"] : "";
$filehash = isset($_GET["filehash"]) ? $_GET["filehash"] : "";
$studusername = isset($_GET["studusername"]) ? $_GET["studusername"] : "";
$unames = isset($_GET["unames"]) ? $_GET['unames'] : "";
if (key_exists("groupinfo", $_GET)) {
    $groupinfo = $_GET["groupinfo"];
    $groupinfohash = $_GET["groupinfohash"];
} else {
    $groupinfo = false;
}

// Check payload integrity.
if (!check_akey($_GET, $akey)) {
    header("HTTP/1.0 401 Unauthorized");
    echo "Payload could not be verified - hash mismatch";
    die();
}

if ($groupinfo !== false) {
    $groupinfotxt = get_groupinfo_txt($user, $groupinfo, $groupinfohash);
}

if(!assignment_exists($aid) && $role == "teacher"){
    // if only teacher requests can trigger the creation of a new assignment

    // create assignment
}

if(!assignment_exists($aid)){
    // if everyones request can trigger the creation of a new assignment

    // create assignment
}

switch($action) {
  case 'view':
    if ($role == "student") {
      // for debuging only!!
      echo "<h1>External Server: ".$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME']."</h1><b>with request params:</b><br/>firstname: ".$fname."<br/>lastname: ".$lname."<br/>username: ".$user."<br/>user-idnumber: ".$uidnr."<br/>role: ".$role."<br/>akey: ".$akey."<br/>course: ".$cidnr."<br/>assignmentid: ".$aid."<br/>assignmentname: ".$aname."<br/>action: ".$action."<br/>studusername: ".$studusername."<hr/>";
      // for debuging only!!
      echo "<h1>RESULTS</h1>";

      echo "<b>Display personal information for student</b><br/>firstname: ".$fname."<br/>lastname: ".$lname."<br/>username: ".$user."<br/>user-idnumber: ".$uidnr."<br/>course: ".$cidnr."<br/>assignmentid: ".$aid."<br/>assignmentname: ".$aname;
      if ($groupinfo !== false) {
          echo $groupinfotxt;
      }

    } else if($role == "teacher"){
      // for debuging only!!
      echo "<h1>External Server: ".$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME']."</h1><b>with request params:</b><br/>firstname: ".$fname."<br/>lastname: ".$lname."<br/>username: ".$user."<br/>user-idnumber: ".$uidnr."<br/>role: ".$role."<br/>akey: ".$akey."<br/>course: ".$cidnr."<br/>assignmentid: ".$aid."<br/>assignmentname: ".$aname."<br/>action: ".$action."<br/>studusername: ".$studusername."<hr/>";
      // for debuging only!!
      echo "<h1>RESULTS</h1>";

            if ($studusername != "") {
                echo "<b>Display detail information on Student ".$studusername." for teacher</b> <br/>firstname: ".$fname."<br/>lastname: ".$lname."<br/>username: ".$user."<br/>user-idnumber: ".$uidnr."<br/>course: ".$cidnr."<br/>assignmentid: ".$aid."<br/>assignmentname: ".$aname."<br/>studusername: ".$studusername;
            }
            else {
                echo "<b>Display overview information for teacher</b><br/>firstname: ".$fname."<br/>lastname: ".$lname."<br/>username: ".$user."<br/>user-idnumber: ".$uidnr."<br/>course: ".$cidnr."<br/>assignmentid: ".$aid."<br/>assignmentname: ".$aname;
            }
            if ($groupinfo !== false) {
                echo $groupinfotxt;
            }
        } else {
            header("HTTP/1.0 400 Bad Request");
            echo "wrong params";
            die();
        }
    break;
  case 'getgrades':
    if ($role == "teacher") {
      header ("Content-Type:text/xml");
      echo "<assignment cidnr=\"$cidnr\" aidnr=\"$aid\">";

      for($i = 0; $i < count($unames); $i++){
        // grade every seconds user
        $username = $unames[$i];

          $grade = rand(0,100);
          // teacheridnr optional
          // timemodified optional (prevents updating everytime, only when timemodified newer than saved one)
          echo "  <submission uname=\"$username\" teacheridnr=\"0\" grade=\"$grade\" timemodified=\"1393941008\">extserver comment</submission>";

      }
      echo "</assignment>";
      die();

    } else {
      header("HTTP/1.0 403 Forbidden");
      echo "Forbidden";
      die();
    }

    break;
}
?>