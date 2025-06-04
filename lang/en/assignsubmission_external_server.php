<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Language strings for the external server submission plugin.
 *
 * @package    assignsubmission_external_server
 * @author     Stefan Weber (stefan.weber@think-modular.com)
 * @copyright  2025 think-modular
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Basics.
$string['pluginname'] = 'External server';
$string['privacy:metadata'] = 'The External server submission plugin does not store any personal data.';

// Strings.
$string['defaultsettings'] = 'Default settings';
$string['defaultsettings_help'] = 'Defaults that can be changed in every instance.';
$string['maxbytes'] = 'Maximum size';
$string['maxbytes_help'] = 'Maximum assignment size for all assignments on the site (subject to course limits and other local settings)';
$string['servers'] = 'Servers';
$string['addserver'] = 'Add external server';
$string['noservers'] = 'No external servers configured';
$string['filetypes'] = 'Allowed file types';
$string['filetypes_help'] = 'Accepted file types can be restricted by entering a list of file extensions. If the field is left empty, then all file types are allowed.';
$string['uploads'] = 'Number of uploads';
$string['uploads_help'] = 'Defines how many times a student can upload a file. Only the last uploaded file will be saved on the Moodle server';