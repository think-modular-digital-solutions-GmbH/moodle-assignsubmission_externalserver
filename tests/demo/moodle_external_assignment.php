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

//COURSE ID 0 ist Testrequest

error_reporting(E_ALL);
ini_set('display_errors', '1');

include("moodle_extserver_lib.php");

if (empty($_GET["akey"])) {
    header("HTTP/1.0 200 OK");
    echo "Available";
    die();
}

// get variables
if (!empty($_GET["akey"])) {
    $values = $_GET;
}

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
    $groupinfotxt = "<br />group info:<pre>".print_r($groupinfo, true)."</pre>";
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