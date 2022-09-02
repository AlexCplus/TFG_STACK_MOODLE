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
 * Version details.
 *
 * @package    local_codechecker
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__. '/../../config.php');

$PAGE->set_url(new moodle_url('/../blocks/stack/students.php'));
$PAGE->set_context(\context_system::instance());
$PAGE->set_title('Reporte estadísticas');

echo $OUTPUT->header();

$CFG->cachejs = false;
$PAGE->requires->js_call_amd('block_stack/main','search');
$PAGE->requires->js_call_amd('block_stack/main','graph');

$links = array("render_statistics.php","students.php", "porcentage.php");
$links_names = array("Estadísticas","Estudiantes", "Porcentaje");

$output  = html_writer::start_div();
$output .= html_writer::start_tag('ul', array("class" => "nav nav-tabs", "role" => "tablist", "style" => "float:right;"));
for ($i = 0; $i < count($links_names); $i++) {
    $output .= html_writer::start_tag('li', array("class" => "nav-item"));
    $url = new moodle_url("../../blocks/stack/". $links[$i] );
    $output .= html_writer::link($url, $links_names[$i], array("class" => "nav-link"));
    $output .= html_writer::end_tag('li');
}
$output .= html_writer::end_div();

$output .= html_writer::start_div('page-header-headings');
$output .= html_writer::tag('h2', 'Estudiantes');
$output .= html_writer::end_div();

$output .= html_writer::start_tag('nav', array("class" => "navbar navbar-light bg-light"));
$output .= html_writer::start_tag('form', array("class" => "form-inline", "method" => "POST"));

$output .= html_writer::tag('input', '', array("class" => "form-control mr-sm-2", "type", "placeholder" => "Búsqueda", "aria-label" => "Search", "id" => "search-student", "name" => "student"));
$output .= html_writer::tag('button', 'Enviar', array("class" => "btn btn-primary", "type" => "submit"));

$output .= html_writer::end_tag('form');
$output .= html_writer::end_tag('nav');

echo $output;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // collect value of input field
    if (!empty($_POST['student'])) {
        $SESSION->studentselected = $_POST['student']; 
        $split = explode(' ', $_POST['student']);
        $sql_user = 'SELECT * FROM {block_stack_students} WHERE firstname = ? AND surname = ? AND courseid = ?';
        if(!isset($split[0])) {
            $split[0] = '';
        }else if(!isset($split[1])) {
            $split[1] = '';
        }
        $result_user = $DB->get_record_sql($sql_user, array($split[0], $split[1], $SESSION->course));
        if($result_user) {
            $sql_quiz_attempt = 'SELECT * FROM {block_stack_quiz_attempt} WHERE userid = ? AND attempt = 1';
            $result_quiz_attempt = $DB->get_records_sql($sql_quiz_attempt, array($result_user->studentid));
            $quiz = array();
            foreach ($result_quiz_attempt as $attempt) {
                $sql_quiz = 'SELECT * FROM {block_stack_quiz} where quizid = ?';
                $result_quiz = $DB->get_record_sql($sql_quiz, array($attempt->quiz));
                array_push($quiz, $result_quiz->name);
            }
            $img = $OUTPUT->user_picture(core_user::get_user($result_user->studentid), array('size' => '160', 'link' => true, 'class' => 'student-img'));
        
            $output = html_writer::start_div('px-4 pt-5 my-5', array('id' => 'container-questions'));
            $output .= html_writer::start_tag('select', array('class' => 'custom-select', 'style' => 'float:right;'));
            $output .= html_writer::tag('option', '-Seleccione un cuestionario');
            foreach ($quiz as $qname) {
                $output .= html_writer::tag('option', $qname);
            }
            $output .= html_writer::end_tag('select');
            $output .= html_writer::start_div('user-data', array('style' => 'padding-bottom:10px;'));
            $output .= $img;
            $output .= html_writer::tag('h2', $_POST['student']);
            $output .= html_writer::end_div();
            $output .= html_writer::end_div();
            echo $output;
        }else{
            echo \core\notification::error("No existe ningún usuario con ese nombre.");
        }
    }
}

echo $OUTPUT->footer();