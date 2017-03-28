<?php
/**
 * @author Le Xuan Anh
 * @email anhlx412@gmail.com
 * Date: 3/28/2017
 */

require_once(__DIR__ . "/../../config.php");
require_once($CFG->dirroot . '/local/courseportfolio/form/course_form.php');

$PAGE->set_url('/local/courseportfolio/courseportfolio.php');
$PAGE->set_pagelayout('incourse');
$PAGE->navbar->add('xuaan anh');
$PAGE->set_title('xyz');
$PAGE->set_heading('abc');

$mform = new course_form();

echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();