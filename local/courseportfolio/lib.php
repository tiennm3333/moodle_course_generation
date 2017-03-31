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

require_once($CFG->libdir.'/gradelib.php');
require_once($CFG->libdir.'/completionlib.php');
require_once($CFG->libdir.'/conditionlib.php');
require_once($CFG->libdir.'/plagiarismlib.php');

define('COURSE_FOMAT_TOPICS', 'topics');
define('COURSE_MODULE_FOLDER', 'folder');

/**
 * update topic number
 *
 * @param string $course
 * @param string $topicnumber
 * @return boolean whether there were any changes to topic number
 */
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

/**
 * create category if not exits
 *
 * @param string $categoryname
 * @return object $category if exits or create new
 */
function courseportfolio_check_category($categoryname) {
    if (empty($categoryname)) {
        return false;
    }

    global $DB;
    $category = $DB->get_record('course_categories', array('name' => $categoryname), 'id');

    if ($category) {
        return $category->id;
    }

    $data = new stdClass();
    $data->name = $categoryname;
    $category = coursecat::create($data);

    return isset($category->id) ? $category->id : false;
}

/**
 * create course if not exits
 *
 * @param string $categoryid
 * @param string $shortname
 * @return object $course if exits or create new
 */
function courseportfolio_check_course($categoryid, $shortname) {
    if (empty($shortname)) {
        return false;
    }

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
    $data->newsitems = isset($courseconfig->newsitems) ? $courseconfig->newsitems : 1;
    $data->showgrades = isset($courseconfig->showgrades) ? $courseconfig->showgrades : 1;
    $data->showreports = isset($courseconfig->showreports) ? $courseconfig->showreports : 0;
    $data->maxbytes = isset($courseconfig->maxbytes) ? $courseconfig->maxbytes : 0;
    $data->groupmode = isset($courseconfig->groupmode) ? $courseconfig->groupmode : 0;
    $data->groupmodeforce = isset($courseconfig->groupmodeforce) ? $courseconfig->groupmodeforce : 0;
    $data->visible = isset($courseconfig->visible) ? $courseconfig->visible : 1;
    $data->visibleold = $data->visible;
    $data->lang = isset($courseconfig->lang) ? $courseconfig->lang : '';
    $course = create_course($data);

    return $course ? $course : false;
}

/**
 * create folder if not exits
 *
 * @param string $foldername
 * @param string $folderdescription
 * @param string $course
 * @param int $section
 * @return object moduleinfo folder if create new folder
 *         int folderid if folder exits
 */
function courseportfolio_check_folder($foldername, $folderdescription, $course, $section, $draftitemid = '') {
    if (empty($foldername) && empty($folderdescription)) {
        return false;
    }

    global $DB;
    if ($folder = $DB->get_record('folder', array('name' => $foldername, 'course' => $course->id), 'id')) {
        return $folder->id;
    }

    list($module, $context, $cw) = can_add_moduleinfo($course, COURSE_MODULE_FOLDER, $section);

    $cm = null;
    $data = new stdClass();
    $data->id = '';
    $data->sr = 0;
    $data->return = 0;
    $data->display = 0;
    $data->instance = '';
    $data->revision = 1;
    $data->cmidnumber = '';
    $data->showexpanded = 1;
    $data->section = $section;
    $data->course = $course->id;
    $data->module = $module->id;
    $data->coursemodule = '';
    $data->visible = 1;
    $data->visibleold = 1;
    $data->add = COURSE_MODULE_FOLDER;
    $data->modulename = $module->name;
    $data->groupmode = $course->groupmode;
    $data->groupingid = $course->defaultgroupingid;
    $data->mform_isexpanded_id_content = 1;
    $data->files = empty($draftitemid) ? 0 : $draftitemid;
    $data->name = $foldername;
    $data->introeditor = array(
        'text' => $folderdescription,
        'format' => FORMAT_HTML,
        'itemid' => file_get_unused_draft_itemid(),
    );

    $mform = new mod_folder_mod_form($data, $cw->section, $cm, $course);
    $result = add_moduleinfo($data, $course, $mform);

    return $result;

}

/**
 * create course folder
 *
 * @param string $categoryname
 * @param string $coursename
 * @param int $topicnumber
 * @param string $foldername
 * @param string $folderdescription
 * @return object moduleinfo folder if create new folder
 *         int folderid if folder exits
 *         false if folder exits
 */
function courseportfolio_create_folder($categoryname, $coursename, $topicnumber, $foldername, $folderdescription , $draftitemid = '') {
    if ($category = courseportfolio_check_category($categoryname)) {
        $course = courseportfolio_check_course($category, $coursename);
        if (courseportfolio_check_topic_number($course, $topicnumber)) {
            if ($folder = courseportfolio_check_folder($foldername, $folderdescription, $course, $topicnumber, $draftitemid)) {
                return $folder;
            }
        }
    }

    return false;
}

/**
 * get contextid by draftitemid
 *
 * @param int $draftitemid
 * @param string $foldername
 * @param string $folderdescription
 * @return int contextid if exits | else return false
 */
function courseportfolio_get_contextid_by_draftitemid($draftitemid) {
    global $DB;
    $contextid = $DB->get_records_select('files', 'itemid = :itemid', array('itemid' => $draftitemid), '', 'contextid', 1, 1);

    return is_array($contextid) ? key($contextid) : false;
}
