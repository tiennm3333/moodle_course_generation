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

define('COURSE_FORMAT_TOPICS', 'topics');
define('COURSE_FORMAT_TOPIC_NUMBER', 'numsections');
define('COURSE_MODULE_FOLDER', 'folder');
define('COURSE_MODULE_RESOURCE', 'resource');

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

    if (isset($formatoptions['numsections']) && $formatoptions['numsections'] < $topicnumber) {
        $dataupdatecourseformat = (object)array('id' => $course->id, 'numsections' => $topicnumber);
        $changed =  $courseformat->update_course_format_options($dataupdatecourseformat);
    }
    return $changed;
}

/**
 * Get topics by multi course index
 *
 * @param int $courseid
 * @return mixed array | false
 */
function courseportfolio_get_topics_by_courses($courses, $sectionnumber, &$invalidcourses) {
    $topics = array();
    if (!empty($courses) && is_array($courses)) {
        foreach ($courses as $course) {
            if (!isset($course->id)) {
                $invalidcourses[] = $course->id;
                continue;
            }
            $courseformat = course_get_format($course->id);
            $section = $courseformat->get_section($sectionnumber);
            if (is_null($section)) {
                $invalidcourses[] = $course->id;
                continue;
            }
            $topics[] = $section;
        }
    }
    return $topics;
}

/**
 * create category if not exits
 *
 * @param string $categoryname
 * @return object $category if exits or create new
 */
function courseportfolio_check_category($categoryname, $createnew = true) {
    if (empty($categoryname)) {
        return false;
    }

    global $DB;
    $category = $DB->get_record('course_categories', array('name' => $categoryname), 'id');

    if ($category) {
        return $category->id;
    }

    if ($createnew) {
        $data = new stdClass();
        $data->name = $categoryname;
        $category = coursecat::create($data);
    
        return isset($category->id) ? $category->id : false;
    }
    return false;
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
    if ($course = $DB->get_record('course', array('category' => $categoryid, 'shortname' => $shortname, 'format' => COURSE_FORMAT_TOPICS), '*')) {
        return $course;
    }

    $data = new stdClass();
    $data->category = $categoryid;
    $data->shortname = $shortname;
    $data->fullname = $shortname;

    // Apply course default settings
    $courseconfig = get_config('moodlecourse');
    $data->format = COURSE_FORMAT_TOPICS;
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
 * Get courses by specific category index
 * 
 * @param int $categoryid
 * @return mixed array | boolean
 */
function courseportfolio_get_courses_by_category($categoryid) {
    global $DB;
    return $DB->get_records('course', array('category' => $categoryid));
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
function courseportfolio_check_folder($foldername, $folderdescription, $course, $section) {
    if (empty($foldername) && empty($folderdescription) || !is_numeric($section)) {
        return false;
    }

    global $DB;
    if (!$module = $DB->get_record('modules', array('name' => COURSE_MODULE_FOLDER), 'id')) {
        return false;
    }

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
    $data->modulename = COURSE_MODULE_FOLDER;
    $data->groupmode = $course->groupmode;
    $data->groupingid = $course->defaultgroupingid;
    $data->mform_isexpanded_id_content = 1;
    $data->files = 0 ;
    $data->name = $foldername;
    $data->introeditor = array(
        'text' => $folderdescription,
        'format' => FORMAT_HTML,
        'itemid' => file_get_unused_draft_itemid(),
    );

    global $CFG;
    require_once($CFG->dirroot . '/mod/folder/mod_form.php');

    $cw = get_fast_modinfo($course)->get_section_info($section);
    $mform = new mod_folder_mod_form($data, $cw->section, null, $course);

    return add_moduleinfo($data, $course, $mform);
}

/**
 * check topic number available
 *
 * @param int $courseid
 * @param int $topicnumber
 * @return boolean
 */
function courseportfolio_check_course_topic_number($courseid, $topicnumber) {
    global $DB;
    if ($maxnumber = $DB->get_record('course_format_options', array('name' => COURSE_FORMAT_TOPIC_NUMBER, 'format' => COURSE_FORMAT_TOPICS, 'courseid' => $courseid), 'value')) {
        return $maxnumber->value >= (int) $topicnumber;
    }

    return false;
}

/**
 * get course topic by course name and categoryid
 *
 * @param int $courseid
 * @param int $topicnumber
 * @return object | boolean
 */
function courseportfolio_get_course_topic_by_name($categoryid, $coursename) {
    global $DB;
    return $DB->get_record('course', array('category' => $categoryid, 'shortname' => $coursename, 'format' => COURSE_FORMAT_TOPICS), '*');
}

/**
 * get folderid into topic of course if exits
 *
 * @param string $foldername
 * @param int $courseid
 * @return object | boolean
 */
function courseportfolio_get_folder_id_by_name($foldername, $courseid) {
    global $DB;
    return $DB->get_record('folder', array('name' => $foldername, 'course' => $courseid), 'id');
}

/**
 * create resource if not exist
 *
 * @param string $course
 * @param int $sectionid
 * @param string $filename
 * @return object moduleinfo resource if create new resource
 *         int resourceid if resource exist
 */
function courseportfolio_check_file($course, $sectionid, $filename) {
    if (empty($filename) && empty($filedescription) && empty($course) && (! is_number($sectionid) || $sectionid < 0)) {
        return false;
    }
    
    global $DB;
    if (!$section = get_fast_modinfo($course)->get_section_info($sectionid)) {
        return false;
    }
    
    if (!$module = $DB->get_record('modules', array('name' => COURSE_MODULE_RESOURCE), 'id')) {
        return false;
    }
    
    $cm = null;
    $data = new stdClass();
    $data->section = $sectionid;
    $data->visible = 1;
    $data->course = $course->id;
    $data->module = $module->id;
    $data->modulename = COURSE_MODULE_RESOURCE;
    $data->groupmode = $course->groupmode;
    $data->groupingid = $course->defaultgroupingid;
    $data->instance = 0;
    $data->add = COURSE_MODULE_RESOURCE;
    $data->return = 0;
    $data->display = 0;
    $data->mform_isexpanded_id_content = 1;
    $data->visibleold = 1;
    $data->revision = 1;
    $data->sr = 0;
    $data->files = 0;
    $data->name = $filename;
    $data->introeditor = array(
        'text' => '', 
        'format' => FORMAT_HTML, 
        'itemid' => file_get_unused_draft_itemid()
    );
    
    global $CFG;
    require_once ($CFG->dirroot . '/mod/resource/mod_form.php');
    $mform = new mod_resource_mod_form($data, $section->section, $cm, $course);
    
    return add_moduleinfo($data, $course, $mform);
}

/**
 * Get draft upload files
 * 
 * @param string $elemenname
 * @return mixed array | boolean
 */
function courseportfolio_get_draft_upload_files($elemenname) {
    $draftitemid = file_get_submitted_draft_itemid($elemenname);
    if (!empty($draftitemid)) {
        if ($contextid = courseportfolio_get_contextid_by_draftitemid($draftitemid)) {
            $fs = get_file_storage();
            return $fs->get_area_files($contextid, 'user', 'draft', $draftitemid, 'id ASC', false);
        }
    }
    return false;
}

/**
 * Get course module info by module name
 * 
 * @param string $modulename
 * @return mixed object | boolean
 */
function courseportfolio_get_course_module_info($modulename) {
    global $DB;
    return $DB->get_record('modules', array('name' => $modulename), 'id');
}

/**
 * Check file is csv or not
 * 
 * @param object $file
 * @return boolean
 */
function courseportfolio_check_file_csv_extension($file) {
    return ($file && pathinfo($file->get_filename(), PATHINFO_EXTENSION) != 'csv');
}

/**
 * Check file encoding
 *
 * @param object $file
 * @return boolean
 */
function courseportfolio_get_file_encoding($csvdata) {
    if (!empty($csvdata)) {
        return mb_detect_encoding($csvdata, 'UTF-8, JIS, SJIS, EUC-JP');
    }
    return false;
}

/**
 * Get csv import reader instance from file object
 *
 * @param object $file
 * @param string $type
 * @return int $csvtotalline
 */
function courseportfolio_get_csv_import_reader_instance($file, $type, &$csvtotalline = 0) {
    if (courseportfolio_check_file_csv_extension($file)) {
        throw new CsvFileOrderErrorException();
    }
    if ($csvdata = $file->get_content()) {
        if (! $encoding = courseportfolio_get_file_encoding($csvdata)) {
            throw new CsvFileFormatErrorException();
        }
        
        $iid = csv_import_reader::get_new_iid($type);
        $importreader = new csv_import_reader($iid, $type);
        $csvtotalline = $importreader->load_csv_content($csvdata, $encoding, 'comma');
        
        $csvloaderror = $importreader->get_error();
        if (!is_null($csvloaderror)) {
            throw new CsvContentErrorException();
        }
        
        $importreader->init();
        return $importreader;
    }
    return false;
}

/**
 * Import common files
 * 
 * @param object $fileconfig
 * @param array $attachmentfiles
 * @return mixed array
 */
function courseportfolio_import_common_files($fileconfig, $attachmentfiles) {
    $report = array();
    if ($importreader = courseportfolio_get_csv_import_reader_instance($fileconfig, 'topicfiles')) {
        while ($line = $importreader->next()) {
            if (!empty($line[0]) && !empty($line[1]) && !empty($line[2])) {
                if ($results = courseportfolio_import_common_file($line[0], $line[1], $line[2], $attachmentfiles)) {
                    $report[] = $results;
                }
            }
        }
        $importreader->close();
        $importreader->cleanup(true);
    }
    return $report;
}

/**
 * Import folder files
 *
 * @param object $fileconfig
 * @param array $attachmentfiles
 * @return mixed array
 */
function courseportfolio_import_folder_files($fileconfig, $attachmentfiles) {
    if ($importreader = courseportfolio_get_csv_import_reader_instance($fileconfig, 'folderfiles', $totalfile)) {
        $totalfileimported = 0;

        // import fist line of file csv
        $firstline = courseportfolio_get_csv_fisrt_line($importreader);
        if (!empty($firstline[0]) && !empty($firstline[1]) && !empty($firstline[2]) && !empty($firstline[3]) && !empty($firstline[4])) {
            if (courseportfolio_import_folfer_file($firstline[0], $firstline[1], $firstline[2], $firstline[3], $firstline[4], $attachmentfiles)) {
                $totalfileimported++;
            }
        }

        while ($line = $importreader->next()) {
            if (!empty($line[0]) && !empty($line[1]) && !empty($line[2]) && !empty($line[3]) && !empty($line[4])) {
                if (courseportfolio_import_folfer_file($line[0], $line[1], $line[2], $line[3], $line[4], $attachmentfiles)) {
                    $totalfileimported++;
                }
            }
        }
        $importreader->close();
        $importreader->cleanup(true);
    }
    return array($totalfile, $totalfileimported);
}

/**
 * Import folders
 *
 * @param object $fileconfig
 * @return mixed array
 */
function courseportfolio_import_folders($fileconfig) {
    if ($importreader = courseportfolio_get_csv_import_reader_instance($fileconfig, 'folders', $totalfile)) {
        $totalfileimported = 0;

        // import fist line of file csv
        $firstline = courseportfolio_get_csv_fisrt_line($importreader);
        if (!empty($firstline[0]) && !empty($firstline[1]) && !empty($firstline[2]) && !empty($firstline[3]) && !empty($firstline[4])) {
            if (courseportfolio_import_folfer($firstline[0], $firstline[1], $firstline[2], $firstline[3], $firstline[4])) {
                $totalfileimported++;
            }
        }

        // import from the second line to end of file csv
        while ($line = $importreader->next()) {
            if (!empty($line[0]) && !empty($line[1]) && !empty($line[2]) && !empty($line[3]) && !empty($line[4])) {
                if (courseportfolio_import_folfer($line[0], $line[1], $line[2], $line[3], $line[4])) {
                    $totalfileimported++;
                }
            }
        }
        $importreader->close();
        $importreader->cleanup(true);
    }
    return array($totalfile, $totalfileimported);
}

/**
 * Generaet import common file report
 *
 * @param array $results
 */
function courseportfolio_report_import_common_files($results) {
    
}

/**
 * Import a common file to multi course by topic number
 * 
 * @param string $categoryname
 * @param int $topicnumber
 * @param string $filename
 * @param array $attachmentfiles
 * @return mixed array | boolean
 */
function courseportfolio_import_common_file($categoryname, $topicnumber, $filename, $attachmentfiles) {
    if (!$categoryid = courseportfolio_check_category($categoryname, false)) {
        return false;
    }
    
    if (!$file = courseportfolio_get_file_instance_by_name($filename, $attachmentfiles)) {
        return false;
    }
    
    if (!$courses = courseportfolio_get_courses_by_category($categoryid)) {
        return false;
    }
    
    $invalidcourses = array();
    $importedcourse = array();
    $topics = courseportfolio_get_topics_by_courses($courses, $topicnumber, $invalidcourses);
    if (!empty($topics)) {
        courseportfolio_create_file_activity_for_topics($topics, $file, $importedcourse);
    }
    return array('error' => array('topic' => $topicnumber ,'courses' => $invalidcourses), 'success' => array('filename' => $filename, 'courses' => $importedcourse));
}

/**
 * Import files into folders
 *
 * @param string $categoryname
 * @param string $coursename
 * @param int $topicnumber
 * @param string $foldername
 * @param string $filename
 * @param array $attachmentfiles
 * @return boolean
 */
function courseportfolio_import_folfer_file($categoryname, $coursename, $topicnumber, $foldername, $filename, $attachmentfiles) {
    if (!$file = courseportfolio_get_file_instance_by_name($filename, $attachmentfiles)) {
        return false;
    }

    if (!$categoryid = courseportfolio_check_category($categoryname, false)) {
        return false;
    }

    if (!$course = courseportfolio_get_course_topic_by_name($categoryid, $coursename)) {
        return false;
    }

    if (!courseportfolio_check_course_topic_number($course->id, $topicnumber)) {
        return false;
    }

    if (!$folderid = courseportfolio_get_folder_id_by_name($foldername, $course->id)) {
        return false;
    }

    return courseportfolio_create_file_activity_for_folders($folderid->id, $file);
}

/**
 * Import folder
 *
 * @param string $categoryname
 * @param string $coursename
 * @param int $topicnumber
 * @param string $foldername
 * @param string $folderdescription
 * @return boolean
 */
function courseportfolio_import_folfer($categoryname, $coursename, $topicnumber, $foldername, $folderdescription) {
    if (!$categoryid = courseportfolio_check_category($categoryname)) {
        return false;
    }

    if (!$course = courseportfolio_check_course($categoryid, $coursename)) {
        return false;
    }

    if (courseportfolio_check_topic_number($course, $coursename)) {
        if ($folder = courseportfolio_check_folder($foldername, $folderdescription, $course, $topicnumber)) {
            return $folder;
        }
    }

    return false;
}

/**
 * Create a file activities
 * 
 * @param array $topics
 * @param object $attachmentfile
 * @param array $importedcourse
 * @return boolean
 */
function courseportfolio_create_file_activity_for_topics($topics, $attachmentfile, &$importedcourse) {
    if (!empty($topics) && is_array($topics)) {
        foreach ($topics as $topic) {
            if ($topic instanceof section_info && $attachmentfile instanceof stored_file) {
                $course = get_course($topic->course);
                if (!$course) {
                    continue;
                }
                if ($fileinstance = courseportfolio_create_file_activity($course, $topic, $attachmentfile->get_filename())) {
                    if (courseportfolio_attach_file_to_activity($fileinstance, $attachmentfile, COURSE_MODULE_RESOURCE)) {
                        $importedcourse[] = $topic->course;
                    }
                }
            }
        }
        return true;
    }
    return false;
}

/**
 * Create a file activities to folder
 *
 * @param array $folderid
 * @param object $attachmentfile
 * @return boolean
 */
function courseportfolio_create_file_activity_for_folders($folderid, $attachmentfile) {
    if ($fileinstance = courseportfolio_get_course_modules_by_folder_name($folderid)) {
        $fileinstance->coursemodule = $fileinstance->id;
        if (courseportfolio_attach_file_to_activity($fileinstance, $attachmentfile, COURSE_MODULE_FOLDER)) {
            return true;
        }
    }

    return false;
}

/**
 * Create file activity
 * 
 * @param object $course
 * @param object $topic
 * @param string $activityname
 * @return boolean|object
 */
function courseportfolio_create_file_activity($course, $topic, $activityname) {
    if (!$module = courseportfolio_get_course_module_info(COURSE_MODULE_RESOURCE)) {
        return false;
    }
    
    $data = new stdClass();
    $data->section = $topic->section;
    $data->visible = 1;
    $data->course = $course->id;
    $data->module = $module->id;
    $data->modulename = COURSE_MODULE_RESOURCE;
    $data->groupmode = $course->groupmode;
    $data->groupingid = $course->defaultgroupingid;
    $data->instance = 0;
    $data->add = COURSE_MODULE_RESOURCE;
    $data->return = 0;
    $data->display = 0;
    $data->mform_isexpanded_id_content = 1;
    $data->visibleold = 1;
    $data->revision = 1;
    $data->sr = 0;
    $data->files = 0;
    $data->name = $activityname;
    $data->introeditor = array(
                    'text' => '',
                    'format' => FORMAT_HTML,
                    'itemid' => file_get_unused_draft_itemid()
    );
    
    global $CFG;
    require_once ($CFG->dirroot . '/mod/resource/mod_form.php');
    $form = new mod_resource_mod_form($data, $topic->section, null, $course);
    
    return add_moduleinfo($data, $course, $form);
}

/**
 * Attach uploaded file to activity
 * 
 * @param array $fileinstance
 * @param object $attachmentfile
 * @param string $modulename
 * @return mixed object | boolean
 */
function courseportfolio_attach_file_to_activity($fileinstance, $attachmentfile, $modulename) {
    if ($fileinstance && ($attachmentfile instanceof stored_file)) {
        if (!$context = context_module::instance($fileinstance->coursemodule)) {
            return false;
        }
        $fs = get_file_storage();
        return $fs->create_file_from_storedfile(array('contextid' => $context->id, 'component' => 'mod_' . $modulename, 'filearea' => 'content', 'itemid' => 0), $attachmentfile->get_id());
    }
    return false;
}

/**
 * Get file instance from list file instances by specific name
 * 
 * @param string $filename
 * @param array $attachmentfiles
 * @return stored_file|boolean
 */
function courseportfolio_get_file_instance_by_name($filename, $attachmentfiles) {
    if (!empty($attachmentfiles) && is_array($attachmentfiles)) {
        foreach ($attachmentfiles as $attachmentfile) {
            if ($attachmentfile instanceof stored_file && $attachmentfile->get_filename() == $filename) {
                return $attachmentfile;
            }
        }
    }
    return false;
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
 * @param int $folderid
 * @return object $coursemodule if exits | false
 */
function courseportfolio_get_course_modules_by_folder_name($folderid) {
    global $DB;
    $params = array(
        'folderid' => $folderid,
        'modulename' => COURSE_MODULE_FOLDER
    );
    $sql = 'SELECT cm.*
            FROM {course_modules} cm
                   JOIN {modules} md ON md.id = cm.module
                   JOIN {folder} fd ON fd.id = cm.instance
            WHERE fd.id = :folderid AND md.name = :modulename';

    return $DB->get_record_sql($sql, $params);

}

/**
 * get first line of csv content
 *
 * @param object $importreader
 * @return object $coursemodule if exits | false
 */
function courseportfolio_get_csv_fisrt_line($importreader) {
    $arrdata = (array)$importreader;
    foreach ($arrdata as $value) {
        if (is_array($value)) {
            return $value;
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

class CsvFileOrderErrorException extends ErrorException {}
class CsvFileFormatErrorException extends ErrorException {}
class CsvContentErrorException extends ErrorException {}