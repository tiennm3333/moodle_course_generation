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
require_once($CFG->dirroot . '/local/courseportfolio/lib.php');

require_login();
$PAGE->set_context(context_system::instance());
$PAGE->set_url('/local/courseportfolio/courseportfolio.php');

$PAGE->set_title(get_string('courseportfolio', 'local_courseportfolio'));
$PAGE->set_heading(get_string('reporttitle', 'local_courseportfolio'));

$error = optional_param('error', false, PARAM_TEXT);
$results = optional_param('results', false, PARAM_TEXT);
$type = optional_param('type', false, PARAM_TEXT);

echo $OUTPUT->header();
echo courseportfolio_generate_report($error, $type, json_decode($results, true));
echo $OUTPUT->footer();