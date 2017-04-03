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
 * Strings for component 'courseportfolio', language 'ja', branch 'MOODLE_28_STABLE'
 *
 * @package    local
 * @subpackage courseportfolio
 * @author     VERSION2, INC.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  2017 (C) VERSION2, INC.
 */

$string['courseportfolio'] = '授業支援インポート';
$string['setting'] = '設定';
$string['title/courses'] = 'Import Courses/Folders';
$string['title/files'] = 'Import Files';
$string['title/common_files'] = 'Import Files Common';
$string['folders'] = '';
$string['folders_help'] = '下記フォーマットのCSVファイルをアップロードしてください。<br>コースカテゴリ,コース名,トピックNo,フォルダ名,説明';
$string['folderfiles'] = '';
$string['folderfiles_help'] = '以下の構成でファイルをアップロードしてください。<br>１．登録先情報のCSVファイル（ファイル名, import.csv）<br>２．登録するファイル（複数)<br><br>CSVファイルのフォーマット<br>コースカテゴリ,コース名,トピックNo. , フォルダ名, ファイル名';
$string['topicfiles'] = '';
$string['topicfiles_help'] = '以下の構成でファイルをアップロードしてください。<br>１．登録先情報のCSVファイル（ファイル名, file.csv）<br>２．登録するファイル（複数)<br><br>CSVファイルのフォーマット<br>コースカテゴリ, トピックNo. , ファイル名';
$string['uploadbutton'] = 'Upload';
$string['csvimportfoldersuccess'] = 'フォルダー登録ができました。';
$string['csvimportfolderfalse'] = 'フォルダーは登録できません。';
$string['csvcontenterror'] = 'CSVファイルは登録できません。';
$string['csvfileformaterror'] = 'CSVファイルのフォーマットは正しくありません。';
$string['csvfileordererror'] = 'CSVファイルの形式でアップロードしてください。';
$string['csvimportfolderfilesresult'] = '指定された{$a->totalfile}個のファイルのうち、{$a->totalfileimported}個のファイルが登録されました';
$string['configuarationfileerror'] = '%sファイルをアップロードしてください';
$string['configuarationfilecontenterror'] = 'CSVファイルのフォーマットは正しくありません。';
$string['importcommonfiletopicerror'] = '%sコースに指定された%sトピックが存在しませんでした。';
$string['importcommonfiletopicsuccess'] = '%s件のファイルが%s件のコースに登録されました。';
$string['importcommonfilefoldersuccess'] = '%s件のファイルが%s件のフォルダーに登録されました。';
$string['importfolderssuccess'] = '%s件のフォルダーが%s件のトピックに登録されました。';
$string['reporttitle'] = '登録結果';
$string['csvimportfoldersresult'] = '指定された{$a->totalfolder}個のフォルダーのうち、{$a->totalfolderimported}個のフォルダーが登録されました';
$string['nothingimported'] = 'ファイルは一個も登録されません。';