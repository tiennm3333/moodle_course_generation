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
require_once($CFG->libdir.'/csvlib.class.php');

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
$filefolderform = new file_form();
$filecommonform = new file_common_form();

if ($courses = $courseform->get_data()) {
    $draftitemid = file_get_submitted_draft_itemid('coursefolders');
    $content = $courseform->get_file_content('coursefolders');

//    $context = context_course::instance($course->id);
    $context = context_course::instance(2);

    $fs = get_file_storage();
    $files = $fs->get_area_files($context, 'mod_folder', 'intro', $draftitemid);
//    $fs->get_area_files($contextid, $component, $filearea, $itemid);

    $a = $fs->get_file_by_hash($pathnamehash);

    echo '<pre>';
    var_dump($files);
    die('courseform');
}

if ($filefolder = $filefolderform->get_data()) {
    $draftitemid = file_get_submitted_draft_itemid('coursefolderfiles');

//    $context = context_course::instance(2);
//    $context = context_module::instance(16);
//    require_capability('moodle/course:manageactivities', $context);
//    echo '<pre>';
//    var_dump($draftitemid);
//    $draftitemid = 599571289;
    $fs = get_file_storage();
//    $files = $fs->get_area_files(5, 'mod_folder', 'intro', $draftitemid);
    $files = $fs->get_area_files(5, 'user', 'draft', $draftitemid, 'id ASC', false);

    $encodings = core_text::get_encodings();

//    echo '<pre>';
//    var_dump($encodings);die;

    $i = 0;
    foreach ($files as $file) {
        if (!$i) {
            if (pathinfo($file->get_filename(), PATHINFO_EXTENSION) != 'csv') {
                var_dump('sai kieu file');die;
            }
            if ($csvdata = $file->get_content()) {
                echo '<pre>';
                var_dump($csvdata);die;
                $encoding = mb_detect_encoding($csvdata, 'auto');
//                if (empty($encoding)) {
//                    $encoding = mb_detect_encoding($csvdata, 'auto', true);
//                }
//                if (!empty($encoding) && !mb_check_encoding($content, 'UTF-8')) {
//                    $result = mb_convert_encoding($content, 'UTF-8', $encoding);
//                }
//                shift_jis
//                echo '<pre>';
//                var_dump($encoding);die;

                $iid = csv_import_reader::get_new_iid('coursefolderfiles');
                $cir = new csv_import_reader($iid, 'coursefolderfiles');
                $readcount = $cir->load_csv_content($csvdata, $encoding, 'comma');

                $csvloaderror = $cir->get_error();
                if (!is_null($csvloaderror)) {
                    $returnurl = new moodle_url('/admin/tool/uploaduser/index.php');
                    print_error('csvloaderror', '', $returnurl, $csvloaderror);
                }
//                $filecolumns = uu_validate_user_upload_columns($cir, $STD_FIELDS, $PRF_FIELDS, $returnurl);

                // init csv import helper
                $cir->init();
                $linenum = 1; //column header is first line


                while ($line = $cir->next()) {
                    $linenum++;
//                    $user = new stdClass();

                    // add fields to user object
                    foreach ($line as $keynum => $value) {
//                        if (!isset($filecolumns[$keynum])) {
//                            // this should not happen
//                            continue;
//                        }
//                        $key = $filecolumns[$keynum];
//                        if (strpos($key, 'profile_field_') === 0) {
//                            //NOTE: bloody mega hack alert!!
//                            if (isset($USER->$key) and is_array($USER->$key)) {
//                                // this must be some hacky field that is abusing arrays to store content and format
//                                $user->$key = array();
//                                $user->$key['text'] = $value;
//                                $user->$key['format'] = FORMAT_MOODLE;
//                            } else {
//                                $user->$key = trim($value);
//                            }
//                        } else {
//                            $user->$key = trim($value);
//                        }
    echo '<pre>';
    var_dump($line);die;

                    }
                }



                $cir->close();
                $cir->cleanup(true);


                echo '<pre>';
                var_dump($content);

            }
        }
        $i++;
    }

    echo '<pre>';
    var_dump($files);

    die('fileform');
}

if ($commonfiles = $filecommonform->get_data()) {
    die('filecommonform');
}

echo $OUTPUT->header();
$courseform->display();
$filefolderform->display();
$filecommonform->display();
echo $OUTPUT->footer();