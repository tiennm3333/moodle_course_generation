<?php

/**
 * This script will search the submission situation of course
 *
 * @package    local_elms_activity;;
 * @copyright
 * @license
 */
require_once($CFG->libdir . '/formslib.php');

class course_form extends moodleform {

    protected function definition() {
        global $CFG, $DB, $PAGE;

        $mform = $this->_form;

        $mform->addElement('html', '<div class="block"><div class="content">');
        $mform->addElement('html', '<div class="title">');

        $mform->addElement('filemanager', 'coursefiles', get_string('introattachments', 'assign'), null, array('subdirs'=>1, 'accepted_types'=>'*'));

        $buttonarray = array();

        $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string("uploadbutton", "local_courseportfolio"));

        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');

        $mform->addElement('html', '</div></div>');
    }

}
