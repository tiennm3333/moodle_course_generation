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
 * Course Portfolio
 *
 * @package    local
 * @subpackage courseportfolio
 * @author     VERSION2, INC.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  2017 (C) VERSION2, INC.
 */

require_once($CFG->libdir . '/formslib.php');

class folders_form extends moodleform {

    protected function definition() {
        $mform = $this->_form;

        $mform->addElement('html', '<div class="block"><div class="content">');
        $mform->addElement('html', '<div class="header">');
        $mform->addElement('html', '<h2>');
        $mform->addElement('html', get_string('title/courses', 'local_courseportfolio'));
        $mform->addElement('html', '</h2>');
        $mform->addElement('html', '</div>');

        $mform->addElement('filemanager', 'folders', get_string('introattachments', 'assign'), null, array('subdirs' => 0, 'accepted_types' => '*'));
        $mform->addHelpButton('folders', 'folders', 'local_courseportfolio');

        $buttonarray = array();
        $buttonarray[] = &$mform->createElement('submit', 'submitcoursefolders', get_string("uploadbutton", "local_courseportfolio"), array('class' => 'form-submit'));
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');

        $mform->addElement('html', '</div></div>');
    }

}
