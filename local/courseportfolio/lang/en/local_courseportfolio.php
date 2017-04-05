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
 * Strings for component 'courseportfolio', language 'en', branch 'MOODLE_28_STABLE'
 *
 * @package    local
 * @subpackage courseportfolio
 * @author     VERSION2, INC.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  2017 (C) VERSION2, INC.
 */

$string['courseportfolio'] = 'Courses support import';
$string['setting'] = 'Setting';
$string['title/courses'] = 'Import Courses/Folders';
$string['title/files'] = 'Import Files';
$string['title/common_files'] = 'Import Files Common';
$string['folders'] = '';
$string['folders_help'] = 'Please upload a CSV filimportcommonfilefoldersuccesse as below.<br>Course Category,Course name,Topic No,Folder name,Description';
$string['folderfiles'] = '';
$string['folderfiles_help'] = 'Upload CSV files as below <br>1.CSV file registered（file.csv）<br>2.registered Files<br><br>Format file.csv<br>Category,Course name,Topic No,Folder name,File name';
$string['topicfiles'] = '';
$string['topicfiles_help'] = 'Upload CSV files as below <br>1.CSV file registered（file.csv）<br>2.registered Files<br><br>Format file.csv<br>Category,Topic No,File name';
$string['uploadbutton'] = 'Upload';
$string['csvimportfoldersuccess'] = 'import folder success';
$string['csvimportfolderfalse'] = 'import folder false or folder exits';
$string['csvcontenterror'] = 'csv content file error';
$string['csvfileformaterror'] = 'csv file type not support encoding';
$string['csvfileordererror'] = 'the first file import must have csv extension';
$string['csvimportfolderfilesresult'] = 'import flies success with: {$a->totalfileimported} / {$a->totalfile} files';
$string['configuarationfileerror'] = 'Please upload the configuaration file named %s';
$string['configuarationfilecontenterror'] = 'Configuration file content is not valid';
$string['importcommonfiletopicerror'] = 'Course %s doesn\'t contain topic %s.';
$string['importcommonfiletopicsuccess'] = '%s file (s) has been imported into %s course (s).';
$string['importcommonfilefoldersuccess'] = '%s file (s) of % uploaded files have been imported successfully';
$string['importfolderssuccess'] = '%s folder (s) has been imported into %s topic (s).';
$string['reporttitle'] = 'Import results';
$string['nothingimported'] = 'No file has been imported.';