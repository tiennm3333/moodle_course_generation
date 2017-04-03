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

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/local/courseportfolio/form/folders_form.php');
require_login();

$folders = new folders_form();

if ($foldersdata = $folders->get_data()) {
    $draftfiles = courseportfolio_get_draft_upload_files('folders');
    if (!empty($draftfiles) && is_array($draftfiles)) {
        try {
            $draftfilesvalue = array_values($draftfiles);
            $configfile = array_shift($draftfilesvalue);

            $results = courseportfolio_import_folders($configfile, $draftfiles);
            courseportfolio_import_result_report(IMPORT_FOLDER, $results);

        } catch (CsvFileOrderErrorException $e) {
            courseportfolio_import_error_report(IMPORT_FOLDER, $e);
        } catch (CsvFileFormatErrorException $e) {
            courseportfolio_import_error_report(IMPORT_FOLDER, $e);
        } catch (CsvContentErrorException $e) {
            courseportfolio_import_error_report(IMPORT_FOLDER, $e);
        } catch (Exception $e) {
            courseportfolio_import_error_report(IMPORT_FOLDER, $e);
        }
    }

}

$folders->display();
