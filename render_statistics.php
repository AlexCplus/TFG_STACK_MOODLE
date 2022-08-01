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

$PAGE->set_url(new moodle_url('/../blocks/stack/render_statistics.php'));
$PAGE->set_context(\context_system::instance());
$PAGE->set_title('Reporte estadísticas');

echo $OUTPUT->header();

if(isset($SESSION->course)) {
    // Gráfica: Personas que han superado en su totalidad el curso habiendo superado los quiz
    $course_name  = null;
    $sql = 'SELECT * FROM {block_stack_quiz} WHERE course = ?';
    $passed_f_chart = 0;
    $not_passed_f_chart = 0;
    $quizs =  $DB->get_records_sql($sql, array($SESSION->course));
    foreach ($quizs as $element) {
        $sql = 'SELECT * FROM {block_stack_quiz_attempt} WHERE quiz = ?';
        $attempts = $DB->get_records_sql($sql, array($element->quizid));
        foreach ($attempts as $attempt) {
            if ($attempt->pf == 0) {
                $not_passed_f_chart++;
            }else {
                $passed_f_chart++;
            }
        }
    }

    // Sacamos el total de estudiantes que estan matriculados para ese curso.

    $sql = 'SELECT * FROM {block_stack_students} WHERE courseid = ?';
    $response = $DB->get_records_sql($sql, array($SESSION->course));
    $amount_students = count($response);

    $passed_s_chart = 0;
    $not_passed_s_chart = 0;
    $sql = 'SELECT * FROM {block_stack_quiz} WHERE course = ?';
    $quizs =  $DB->get_records_sql($sql, array($SESSION->course));
    foreach ($quizs as $element) {
        $sql = 'SELECT * FROM {block_stack_quiz_attempt} WHERE quiz = ? AND attempt = 1';
        $attempts = $DB->get_records_sql($sql, array($element->quizid));
        foreach ($attempts as $attempt) {
            if ($attempt->pf == 0) {
                $not_passed_s_chart++;
            }else {
                $passed_s_chart++;
            }
        }
    }
    $not_even_trying = ($amount_students*count($quizs)) - ($not_passed_s_chart+$passed_s_chart);

    $not_passed_t_chart = 0;
    $passed_t_chart = 0;
    $sql = 'SELECT * FROM {block_stack_quiz} WHERE course = ?';
    $quizs =  $DB->get_records_sql($sql, array($SESSION->course));
    foreach ($quizs as $element) {
        $sql = 'SELECT * FROM {block_stack_quiz_attempt} WHERE quiz = ? AND attempt = 1';
        $attempts = $DB->get_records_sql($sql, array($element->quizid));
        foreach ($attempts as $attempt) {
            $sql = 'SELECT * FROM {block_stack_question} WHERE uniqueid = ?';
            $questions = $DB->get_records_sql($sql, array($attempt->uniqueid));
            foreach ($questions as $question) {
                if ($question->pf == 0) {
                    $not_passed_t_chart++;
                }else{
                    $passed_t_chart++;
                }
            }
        }
    }

    //Vamos a calcular cual es el mejor cuestionario respondido.
    $sql = 'SELECT * FROM {block_stack_quiz} WHERE course = ?';
    $quizs =  $DB->get_records_sql($sql, array($SESSION->course));
    $results_quizs = array();
    foreach ($quizs as $element) {
        $sql = 'SELECT * FROM {block_stack_quiz_attempt} WHERE quiz = ? AND attempt = 1';
        $attempts = $DB->get_records_sql($sql, array($element->quizid));
        foreach ($attempts as $attempt) {
            $newmark = ($attempt->mark/$attempt->maxgrade)*10;
            if(!isset($results_quizs[$element->name])) {
                $results_quizs[$element->name] = $newmark;
            }else{
                $results_quizs[$element->name] += $newmark;
            }
        }
    }
    
    //Vamos a calcular cual es la respuesta mejor contestada.
    $sql = 'SELECT * FROM {block_stack_quiz} WHERE course = ?';
    $quizs =  $DB->get_records_sql($sql, array($SESSION->course));
    $results_question = array();
    foreach ($quizs as $element) {
        $sql = 'SELECT * FROM {block_stack_quiz_attempt} WHERE quiz = ? AND attempt = 1';
        $attempts = $DB->get_records_sql($sql, array($element->quizid));
        foreach ($attempts as $attempt) {
            $sql = 'SELECT * FROM {block_stack_question} WHERE uniqueid = ?';
            $questions = $DB->get_records_sql($sql, array($attempt->uniqueid));
            foreach ($questions as $question) {
                $newmark = ($question->mark/$question->maxgrade)*10;
                if(!isset($results_question[$question->name])) {
                    $results_question[$question->name] = $newmark;
                }else{
                    $results_question[$question->name] += $newmark;
                }
            }
        }
    }
    
    $CFG->chart_colorset = ['#77dd77', '#ff0000', '#9b9b9b'];
    $chartf = new \core\chart_pie();
    $chartf->set_title('Total de cuestionarios con más de un intento.');
    $chartf->set_doughnut(true);
    $chartf->add_series(new \core\chart_series('Cantidad de alumnos', [$passed_f_chart, $not_passed_f_chart]));
    $chartf->set_labels(['Superados', 'No Superados']);

    $charts = new \core\chart_pie();
    $charts->set_title('Total de cuestionarios con el primer intento.');
    $charts->set_doughnut(true);
    $charts->add_series(new \core\chart_series('Cantidad de alumnos', [$passed_s_chart, $not_passed_s_chart, $not_even_trying]));
    $charts->set_labels(['Superados', 'No Superados', 'Sin intentos']);

    $chartt = new \core\chart_pie();
    $chartt->set_title('Total de preguntas con un intento.');
    $chartt->set_doughnut(true);
    $chartt->add_series(new \core\chart_series('Cantidad de alumnos', [$passed_t_chart, $not_passed_t_chart]));
    $chartt->set_labels(['Superados', 'No Superados']);

    $links = array("render_statistics.php","students.php");
    $links_names = array("Estadísticas","Estudiantes");
    $col_data = array("#", "Cuestionarios", "Preguntas");
    $row_data = array(  
        0 => "Mejores resultados", 1 => array_search(max($results_quizs), $results_quizs), 2 =>  array_search(max($results_question), $results_question), 3 => "-", 
        4 => "Peores resultados", 5 => array_search(min($results_quizs), $results_quizs), 6 => array_search(min($results_question), $results_question), 7 => "-",
        //8 => "No intentos", 9 => "", 10 => ""
    );

    $out = $OUTPUT->heading('Estadísticas ' . $course_name);
    $out .= html_writer::start_tag('ul', array("class" => "nav nav-tabs", "role" => "tablist", "style" => "float:right;"));
    for ($i = 0; $i < 2; $i++) {
        $out .= html_writer::start_tag('li', array("class" => "nav-item"));
        $url = new moodle_url("../../blocks/stack/". $links[$i] );
        $out .= html_writer::link($url, $links_names[$i], array("class" => "nav-link"));
        $out .= html_writer::end_tag('li');
    }
    $out .= html_writer::end_tag('ul');
    $out .= html_writer::start_tag('div', array('class' => 'table-responsive'));
    $out .= html_writer::div($OUTPUT->render($chartf, false), 'course-statistics', array('style' => 'position: relative; height:40vh; width:30vw;')); 
    $out .= html_writer::div($OUTPUT->render($charts, false), 'quiz-statistics', array('style' => 'position: relative; height:40vh; width:40vw; float:right; transform: translate(20px, -200px);')); 
    $out .= html_writer::div($OUTPUT->render($chartt, false), 'question-statistics', array('style' => 'position: relative; height:40vh; width:30vw;')); 
    $out .= html_writer::start_tag('table', array('class' => 'table'));
    
    // Start the table data columns. 
    $out .= html_writer::start_tag('thead');
    $out .= html_writer::start_tag('tr');
    for ($i = 0; $i < count($col_data); $i++) {
        $out .= html_writer::start_tag('th', array('scope' => 'col'));
        $out .= html_writer::span($col_data[$i]);
        $out .= html_writer::end_tag('th');     
    }

    $out .= html_writer::end_tag('tr');
    $out .= html_writer::end_tag('thead');

    // Start the table data rows.

    $out .= html_writer::start_tag('tbody');
    $out .= html_writer::start_tag('tr');
    $out .= html_writer::tag('th', $row_data[0]);
    for($i = 1; $i < count($row_data); $i++) {
        if ($row_data[$i] == "-") {
            if ($i == 10) {

            }
            $out .= html_writer::end_tag('tr');
            $out .= html_writer::start_tag('tr');
            if (isset($row_data[$i+1])) {
                $out .= html_writer::tag('th', $row_data[$i+1]);
            }
            $i++;
        }else{
            $out .= html_writer::tag('td', $row_data[$i]);
        }
    }

    $out .= html_writer::end_tag('tbody');
    $out .= html_writer::end_tag('table');
    $out .= html_writer::end_tag('div');
    echo $out;
}else{
    echo \core\notification::error("No se ha seleccionado ningún curso.");
}


echo $OUTPUT->footer();
