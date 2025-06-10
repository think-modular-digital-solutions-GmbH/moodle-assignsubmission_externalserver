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
 * @package    assignsubmission_external_server
 * @author     Stefan Weber
 * @copyright  2025 think-modular
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace assignsubmission_external_server;

defined('MOODLE_INTERNAL') || die();

use html_writer;
use moodle_url;

/**
 * This class contains helper methods for the external server submission plugin.
 *
 * @package    assignsubmission_external_server
 * @author     Stefan Weber
 * @copyright  2025 think-modular
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class helper {

    /**
     * Returns edit icons for a server in the server list.
     *
     * @param obj $server the server object containing the server details.
     * @return string HTML string containing the edit and delete icons.
     */
    public static function edit_icons($server) {

        global $OUTPUT;
        $id = $server->id;

        // Edit.
        $editurl = new \moodle_url('/mod/assign/submission/external_server/editserver.php', ['id' => $id, 'sesskey' => sesskey()]);
        $editicon = $OUTPUT->action_icon($editurl, new \pix_icon('t/edit', get_string('edit')));

        // Show/hide.
        if ($server->visible) {
            $hideurl =  new \moodle_url('/mod/assign/submission/external_server/editserver.php', ['id' => $id, 'hide' => 1, 'sesskey' => sesskey()]);
            $hideicon = $OUTPUT->action_icon($hideurl, new \pix_icon('t/hide', get_string('hide')));
        } else {
            $hideurl =  new \moodle_url('/mod/assign/submission/external_server/editserver.php', ['id' => $id, 'show' => 1, 'sesskey' => sesskey()]);
            $hideicon = $OUTPUT->action_icon($hideurl, new \pix_icon('t/show', get_string('show')));
        }

        // Delete.
        $deleteurl = new \moodle_url('/mod/assign/submission/external_server/editserver.php', ['id' => $id, 'delete' => $id, 'sesskey' => sesskey()]);
        $deleteicon = $OUTPUT->action_icon($deleteurl, new \pix_icon('t/delete', get_string('delete')));

        return $editicon . '&nbsp;' . $hideicon . '&nbsp;' . $deleteicon;
    }

}