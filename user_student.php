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

$PAGE->set_url(new moodle_url('/../blocks/stack/user_student.php'));
$PAGE->set_context(\context_system::instance());
$PAGE->set_title('Reporte estadísticas');

echo $OUTPUT->header();

if (isset($SESSION->course)) {
    $sql = 'SELECT * FROM {block_stack_quiz} WHERE course = ?';
    $passed = 0;
    $not_passed = 0;
    $student_score = 0;
    $max_score = 0;
    $outpt = $OUTPUT->heading('Tus estadísticas'); 
    $quizs =  $DB->get_records_sql($sql, array($SESSION->course));
    foreach ($quizs as $element) {
        $sql = 'SELECT * FROM {block_stack_quiz_attempt} WHERE quiz = ? AND userid = ?';
        $attempts = $DB->get_records_sql($sql, array($element->quizid, $USER->id));
        foreach ($attempts as $attempt) {
            if ($attempt->attempt = 1) {
                $student_score += $attempt->mark;
                $max_score += $attempt->maxgrade;
            }
            if ($attempt->pf == 0) {
                $not_passed++;
            }else {
                $passed++;
            }
        }
    }

    $outpt .= html_writer::start_div('row');

    $img = $OUTPUT->user_picture(core_user::get_user($USER->id), array('size' => '160', 'link' => true, 'class' => 'student-img'));
    $outpt .= html_writer::start_div('col-xs-12 col-sm-6 col-md-4', array("style" => "background-color: bisque; margin: 20px; padding: 20px; border-radius: 20px"));
    $outpt .= $img;
    $outpt .= html_writer::tag('h4', '<b>' . $USER->firstname . ' ' . $USER->lastname . '</b>');
    $outpt .= html_writer::end_div();

    $outpt .= html_writer::start_div('col-xs-3', array("style" => "background-color: bisque; margin: 20px; padding: 20px; border-radius: 20px"));
    $outpt .= html_writer::tag('h3', 'Puntuación');
    $outpt .= html_writer::start_div('', array("style" => "padding: 2em;"));
    $outpt .= html_writer::start_div('', array("style" => "position: relative; margin: 0 auto; padding: 1.5em; width: 120px; height: 120px; border-radius: 50%; border: 5px solid #e84d51;"));
    $outpt .= html_writer::tag('h4', $student_score . '/' . $max_score, array("style" => "padding-top: 0.5em; font-size: 28px; text-align: center; font-weight:bold;"));    
    $outpt .= html_writer::end_div();
    $outpt .= html_writer::end_div();

    $sql = 'SELECT * FROM {block_stack_course} WHERE courseid = ?';
    $consult = $DB->get_records_sql($sql, array($SESSION->course));
    $outpt .= html_writer::end_div();

    $CFG->chart_colorset = ['#77dd77', '#ff0000', '#9b9b9b'];
    $chart = new \core\chart_pie();
    $chart->set_title('Total de cuestionarios superados y no superados.');
    $chart->set_doughnut(true);
    $chart->add_series(new \core\chart_series('Cantidad de alumnos', [$passed, $not_passed]));
    $chart->set_labels(['Superados', 'No Superados']);
    $outpt .= html_writer::start_div('col-xs-4', array("style" => "background-color: bisque; margin: 20px; padding: 20px; border-radius: 20px"));
    $outpt .= $OUTPUT->render_chart($chart, false);
    $outpt .= html_writer::end_div();

    $chart = new \core\chart_line();
    $chart->set_title('Total de cuestionarios superados y no superados.');
    $chart->add_series(new \core\chart_series('Cantidad de alumnos', [$passed, $not_passed, $passed, $not_passed, $passed, $not_passed]));
    $chart->set_labels(['Superados', 'No Superados', 'Superados', 'No Superados', 'Superados', 'No Superados']);
    $outpt .= html_writer::start_div('col-xs-4', array("style" => "background-color: bisque; margin: 20px; padding: 20px; border-radius: 20px; float:center;"));
    $outpt .= $OUTPUT->render_chart($chart, false);
    $outpt .= html_writer::end_div();

    echo $outpt;
}else{
    echo \core\notification::error("No se ha seleccionado ningún curso.");
}

echo $OUTPUT->footer();