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

require_once(__DIR__ . "/../../config.php");
require_once($CFG->dirroot . '/local/courseportfolio/form/course_form.php');
require_once($CFG->dirroot . '/local/courseportfolio/form/file_form.php');
require_once($CFG->dirroot . '/local/courseportfolio/form/file_common_form.php');

require_login();
$PAGE->set_context(context_system::instance());
$PAGE->set_url('/local/courseportfolio/courseportfolio.php');
$PAGE->set_pagelayout('admin');

$title = get_string("courseportfolio", "local_courseportfolio");
$PAGE->navbar->add($title);
$PAGE->set_title($title);
$PAGE->set_heading($title);

if (!is_siteadmin()) {
    print_error('nologinas');
}

$courseform = new course_form();
$fileform = new file_form();
$filecommonform = new file_common_form();

if ($courses = $courseform->get_data()) {
    $draftitemid = file_get_submitted_draft_itemid('coursefolders');
    var_dump($draftitemid);
    die('courseform');
}

if ($files = $fileform->get_data()) {
    die('fileform');
}

if ($commonfiles = $filecommonform->get_data()) {
    die('filecommonform');
}

echo $OUTPUT->header();
$courseform->display();
$fileform->display();
$filecommonform->display();
echo $OUTPUT->footer();