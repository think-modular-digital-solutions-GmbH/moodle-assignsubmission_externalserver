# External server submission type

## Installation

Place in /mod/assign/submission/

## Configuration

As an administrator you can set the default values instance-wide on the settings page for
administrators in the extserver module, as well as manage and test the available external servers.

## Description

The external server submission type  enables users to submit files to be processed automatically by an external server.

The activity module stores the submitted files and sends it via curl to the chosen server. This server is used to process the files in an automated way (e.g. automatically compile and run a certain source code) and is able to return an automatically calculated grade and feedback afterwards.

### Usage Example

Students of a computer science course have to write code to fullfill a certain task. The source code has to be submitted via this activity until a (optional) certain deadline. As soon as the students submit their version of the solution, the files get transfered to the external server and are automatically compiled and tested. Based on the output of the compiler, the testing framework and
similar criteria the server calculates the grade and prepares an automated feedback for the students.

After everyone has submitted their solution (or the deadline passed) and all the processing is done, the teacher grades the students simply by fetching the grades from the external server. Afterwards the teacher is able to overrule the automatic grading or grade everyone manually from the start.

## Documentation / Interface description

Server-side code is included in this plugin under /tests/demo.

If you want to use this exact code for testing purposes, just chown wwwdata for the /upload directory, so you can upload files.

### Connection check:

GET request to the URL set in the server settings.

Without any variables - just to check HTTP Status Code.

### Teacher View:

GET request to the URL set in the server settings.

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

### Student View

GET request to the URL set in the server settings.

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

### Upload file

GET request to the **Upload URL** set in the server settings.

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

### Load Grades:

GET request to the URL set in the server settings.

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


## License

This plugin is free software: you can redistribute it and/or modify it under the terms of the GNU
General Public License as published by the Free Software Foundation, either version 3 of the
License, or (at your option) any later version.

The plugin is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
General Public License for more details.

You should have received a copy of the GNU General Public License with Moodle. If not, see
<http://www.gnu.org/licenses/>.


Good luck and have fun!