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
    $draftfiles = courseportfolio_get_draft_upload_files('folderfiles');
    if (!empty($draftfiles) && is_array($draftfiles)) {
        try {
            $results = courseportfolio_import_folder_files(courseportfolio_get_import_config_file(IMPORT_FOLDER_CONFIG_FILE, $draftfiles), $draftfiles);
            courseportfolio_import_result_report(IMPORT_FOLDER_FILE, $results);
        } catch (CsvFileOrderErrorException $e) {
            courseportfolio_import_error_report(IMPORT_FOLDER_FILE, $e);
        } catch (CsvFileFormatErrorException $e) {
            courseportfolio_import_error_report(IMPORT_FOLDER_FILE, $e);
        } catch (CsvContentErrorException $e) {
            courseportfolio_import_error_report(IMPORT_FOLDER_FILE, $e);
        } catch (Exception $e) {
            courseportfolio_import_error_report(IMPORT_FOLDER_FILE, $e);
        }
    }
}


$folderfiles->display();
