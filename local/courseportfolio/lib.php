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
 * common function for courseportfolio.
 *
 * @package    local
 * @subpackage courseportfolio
 * @author     VERSION2, INC.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  2017 (C) VERSION2, INC.
 */
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->libdir . '/coursecatlib.php');
require_once($CFG->libdir.'/filelib.php');
require_once($CFG->dirroot . '/course/modlib.php');
require_once($CFG->dirroot . '/mod/folder/mod_form.php');

define('COURSE_FOMAT_TOPICS', 'topics');
define('COURSE_MODULE_FOLDER', 'folder');

function courseportfolio_check_topic_number($course, $topicnumber) {
    $changed = true;
    $courseformat = course_get_format($course->id);
    $formatoptions = $courseformat->get_format_options();

    if (isset($formatoptions['numsections']) && $formatoptions['numsections'] != $topicnumber) {
        $dataupdatecourseformat = (object)array('id' => $course->id, 'numsections' => $topicnumber);
        $changed =  $courseformat->update_course_format_options($dataupdatecourseformat);
    }
    return $changed;
}

function courseportfolio_check_category($categoryname) {
    global $DB;
    $category = $DB->get_record('course_categories', array('name' => $categoryname), 'id');

    if ($category->id) {
        return $category->id;
    }

    $data = new stdClass();
    $data->name = $categoryname;
    $category = coursecat::create($data);

    return $category ? $category->id : false;
}

function courseportfolio_check_course($categoryid, $shortname) {
    global $DB;
    if ($course = $DB->get_record('course', array('category' => $categoryid, 'shortname' => $shortname, 'format' => COURSE_FOMAT_TOPICS), '*')) {
        return $course;
    }

    $data = new stdClass();
    $data->category = $categoryid;
    $data->shortname = $shortname;
    $data->fullname = $shortname;

    // Apply course default settings
    $courseconfig = get_config('moodlecourse');
    $data->format = COURSE_FOMAT_TOPICS;
    $data->newsitems = $courseconfig->newsitems;
    $data->showgrades = $courseconfig->showgrades;
    $data->showreports = $courseconfig->showreports;
    $data->maxbytes = $courseconfig->maxbytes;
    $data->groupmode = $courseconfig->groupmode;
    $data->groupmodeforce = $courseconfig->groupmodeforce;
    $data->visible = $courseconfig->visible;
    $data->visibleold = $data->visible;
    $data->lang = $courseconfig->lang;
    $course = create_course($data);

    return $course ? $course : false;
}

function courseportfolio_check_folder($course, $section) {
    global $DB;
    $cw = get_fast_modinfo($course)->get_section_info($section);
    $module = $DB->get_record('modules', array('name' => COURSE_MODULE_FOLDER), 'id');

    $cm = null;
    $data = new stdClass();
    $data->sr = 0;
    $data->return = 0;
    $data->display = 0;
    $data->instance = 0;
    $data->revision = 1;
    $data->cmidnumber = '';
    $data->showexpanded = 1;
    $data->section = $section;
    $data->course = $course->id;
    $data->module = $module->id;
    $data->visible = $cw->visible;
    $data->add = COURSE_MODULE_FOLDER;
    $data->modulename = COURSE_MODULE_FOLDER;
    $data->groupmode = $course->groupmode;
    $data->groupingid = $course->defaultgroupingid;
    $data->mform_isexpanded_id_content = 1;
    $data->files = 0;
    $data->name = 'sssss';
    $data->introeditor = array(
        'text' => 'day la folder ssss',
        'format' => FORMAT_HTML,
        'itemid' => file_get_unused_draft_itemid(),
    );
    $mform = new mod_folder_mod_form($data, $cw->section, $cm, $course);

    return add_moduleinfo($data, $course, $mform);
}

function courseportfolio_check_csv_import($draftitemid, $importtype) {
    $draftitemid = file_get_submitted_draft_itemid('coursefolderfiles');
    $importtype = 'coursefolderfiles';

//    $context = context_course::instance(2);
//    $context = context_module::instance(16);
//    require_capability('moodle/course:manageactivities', $context);
//    echo '<pre>';
//    var_dump($draftitemid);
//    $draftitemid = 599571289;
    $fs = get_file_storage();
//    $files = $fs->get_area_files(5, 'mod_folder', 'intro', $draftitemid);
    $files = $fs->get_area_files(5, 'user', 'draft', $draftitemid, 'id ASC', false);
//    echo '<pre>';
//    var_dump($files);die;

    $i = 0;
    foreach ($files as $file) {
        if (!$i) {
            if (pathinfo($file->get_filename(), PATHINFO_EXTENSION) != 'csv') {
                var_dump('sai kieu file');die;
            }
            if ($csvdata = $file->get_content()) {
                $iid = csv_import_reader::get_new_iid($importtype);
                $cir = new csv_import_reader($iid, $importtype);
                $readcount = $cir->load_csv_content($csvdata, 'shift_jis', 'comma');

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
}
