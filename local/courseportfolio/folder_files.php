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
require_once($CFG->dirroot . '/local/courseportfolio/form/folder_files_form.php');
require_login();

$folderfiles = new folder_files_form();

if ($folderfilesdata = $folderfiles->get_data()) {
    $draftitemid = file_get_submitted_draft_itemid('folderfiles');
    $contextid = courseportfolio_get_contextid_by_draftitemid($draftitemid);
    $fs = get_file_storage();
    $files = $fs->get_area_files($contextid, 'user', 'draft', $draftitemid, 'id ASC', false);
    $i = 0;
    $countsucess = 0;
    foreach ($files as $file) {
        if (!$i) {
            if (pathinfo($file->get_filename(), PATHINFO_EXTENSION) != 'csv') {
                echo get_string('csvfileordererror', 'local_courseportfolio');
                break;
            }
            if ($csvdata = $file->get_content()) {
                if (!$encoding = mb_detect_encoding($csvdata, 'UTF-8, JIS, SJIS, EUC-JP')) {
                    echo get_string('csvfileformaterror', 'local_courseportfolio');
                    break;
                }

                $iid = csv_import_reader::get_new_iid('coursefolderfiles');
                $cir = new csv_import_reader($iid, 'coursefolderfiles');
                $csvtotalline = $cir->load_csv_content($csvdata, $encoding, 'comma');

                $csvloaderror = $cir->get_error();
                if (!is_null($csvloaderror)) {
                    echo get_string('csvcontenterror', 'local_courseportfolio');
                    break;
                }

                $cir->init();
                $linenum = 1; //column header is first line

                while ($line = $cir->next()) {
                    $linenum++;
                    /*
                        $categoryname = $line[0];
                        $coursename = $line[1];
                        $topicnumber = $line[2];
                        $foldername = $line[3];
                        $filename = $line[4];
                    */
                    if (empty($line[0]) || empty($line[1]) || empty($line[2]) || empty($line[3]) || empty($line[4])) {

                    } else {
                        $folder = courseportfolio_create_folder($line[0], $line[1], $line[2], $line[3], $line[4], $draftitemid);
                        if ($folder && is_object($folder)) {
                            $countsucess++;
                        }
                    }
                }
                $cir->close();
                $cir->cleanup(true);
            }
        }
        $i++;
    }

    echo '<div class="message">';
    if ($countsucess) {
        echo  get_string('csvimportfolderfilessuccess', 'local_courseportfolio');
    } else {
        echo  get_string('csvimportfolderfilesfalse', 'local_courseportfolio');
    }
    echo '</div>';
}

$folderfiles->display();
