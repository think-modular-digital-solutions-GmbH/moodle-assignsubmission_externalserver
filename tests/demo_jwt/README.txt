# ---------------------------------------------------------------
# This software is provided under the GNU General Public License
# http://www.gnu.org/licenses/gpl.html
# with Copyright © 2009 onwards
#
# Dipl.-Ing. Andreas Hruska
# andreas.hruska@tuwien.ac.at
#
# Dipl.-Ing. Mag. rer.soc.oec. Katarzyna Potocka
# katarzyna.potocka@tuwien.ac.at
#
# Vienna University of Technology
# Teaching Support Center
# Guﬂhausstraﬂe 28/E015
# 1040 Wien
# http://tsc.tuwien.ac.at/
# ---------------------------------------------------------------
# FOR Moodle 3.4
# ---------------------------------------------------------------

README.txt
v.2018-01-24


Extserver-Module DUMMY PACKAGE
==============================

NOTICE: COURSE ID 0 is used for Testrequests

INSTALLATION
====================================================================================================
    Enter your secret (in moodle_extserver_lib.php in line 31)
    Enter your upload directory (in moodle_extserver_upload.php line 71)
        NOTE: the webserver (php) needs write access in this directory

RECOMMENDED CONFIGURATION
====================================================================================================
    Allow access from moodle and the web to moodle_extserver.php
    Allow access from moodle and deny the web to moodle_extserver_upload.php
    Deny web access to your upload directory

INTERFACE // REQUESTS FROM MOODLE PLUGIN TO EXTERNAL SERVER
====================================================================================================
External Server communication runs via CURL HTTP requests.
The External Server Module will request
I) Connection check:

    GET request to
        ~/moodle_extserver.php

without any variables, just to check HTTP Status Code!

II) Teacher View:

    GET request to
        ~/moodle_extserver.php

with following variables:

    timestamp     = Time of request
    user          = Teacher's username
    skey          = Teacher's sesskey
    uidnr         = Teacher's idnumber
    action        = 'view';
    cidnr         = Moodle DB id number for the course
    aid           = UNIX timestamp when the external server module has been created in the course
    aname         = External Server Module's name
    fname         = Teacher's first name
    lname         = Teacher's last name
    role          = 'teacher'
    studusername  = Student's user name (usually = '')
    akey          = Accesskey calculated from server secret, timestamp, current users username, moodle sesskey,
                    user's idnumber, action, course id, creation timestamp of extserver, name of extserver module,
                    firstname, lastname, role and student's username
    groupinfo     = (optional) JSON-encoded array of objects containing ID, name and members (=array of usernames)
    groupinfohash = (optional) hashed JSON encoded groupinfo with prepended server secret

Answer will be displayed in iframe!

III) Student View:

    GET request to
        ~/moodle_extserver.php

with following variables:

    timestamp     = Time of request
    user          = Student's username
    skey          = Student's sesskey
    uidnr         = Student's idnumber
    action        = 'view'
    cidnr         = Moodle DB id number for the course
    aid           = UNIX timestamp when the external server module has been created in the course
    aname         = External Server Module's name
    fname         = Student's first name
    lname         = Student's last name
    role          = 'student';
    akey          = Accesskey calculated from server secret, timestamp, current users username, moodle sesskey,
                    user's idnumber, action, course id, creation timestamp of extserver, name of extserver module,
                    firstname, lastname and role
    groupinfo     = (optional) JSON-encoded array of objects containing ID, name and members (=array of usernames)
    groupinfohash = (optional) hashed JSON encoded groupinfo with prepended server secret

Answer will be displayed in iframe!

IV) Upload file:

    POST request to
        ~/moodle_extserver_upload.php

with following variables:

    timestamp     = Time of request
    user          = the posting users name
    skey          = the posting users sesskey
    uidnr         = the posting users idnumber
    action        = 'submit'
    cidnr         = Moodle DB id number for the course
    aid           = UNIX timestamp when the external server module has been created in the course
    aname         = External Server Module's name
    fname         = the posting user's first name
    lname         = the posting user's last name
    role          = 'student'
    filename      = the posted files name
    filehash      = hashed file content
    file          = the file content
    akey          = Accesskey calculated from server secret, timestamp, posting user's username, moodle sesskey,
                    user's idnumber, action, course id, creation timestamp of extserver, name of extserver module,
                    firstname, lastname, role, filename and filehash
    groupinfo     = (optional) JSON-encoded array of objects containing ID, name and members (=array of usernames)
    groupinfohash = (optional) hashed JSON encoded groupinfo with prepended server secret

V) Load Grades:

    GET request to
        ~/moodle_extserver.php

with following variables:

    timestamp     = Time of request
    user          = acting user's username
    skey          = acting user's sesskey
    uidnr         = acting user's idnumber
    action        = 'getgrades'
    cidnr         = Moodle DB id number for the course
    aid           = UNIX timestamp when the external server module has been created in the course
    aname         = External Server's name
    fname         = acting user's first name
    lname         = acting user's last name
    role          = 'teacher'
    unames[]      = Array of usernames to request grades for (sorted by array_ksort())
    akey          = Accesskey calculated from server secret, timestamp, acting user's username, moodle sesskey,
                    user's idnumber, action, course id, creation timestamp of extserver, name of extserver module,
                    firstname, lastname, role and all the user names from the array (sorted by array_ksort() and
                                                                                     imploded with string 'unames' as glue)
    groupinfo     = (optional) JSON-encoded array of objects containing ID, name and members (=array of usernames)
    groupinfohash = (optional) hashed JSON encoded groupinfo with prepended server secret

expects XML of the following format in return:

    <assignment cidnr=\"$cidnr\" aidnr=\"$aid\">
        <submission uname="username1" teacheridnr="0" grade="38" timemodified="1393941008">Only 38%, be better next time!</submission>
        <submission uname="username2" teacheridnr="0" grade="93" timemodified="1393941008">Best submission this time, keep on!</submission>
        <submission uname="username3" teacheridnr="0" grade="72" timemodified="1393941008">You have to, blablabla better next time!</submission>
        <submission uname="username4" teacheridnr="0" grade="45" timemodified="1393941008">Improve blablabla to be positive!</submission>
        ...
    </assignment>

CHANGELOG
====================================================================================================
