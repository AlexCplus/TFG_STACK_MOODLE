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
 * @package    block_stack
 * @copyright  2022 University of Alicante
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


use block_mockblock\search\area;
use mod_lti\local\ltiservice\response;

define('NO_OUTPUT_BUFFERING', true);
require_once(__DIR__. '/../../config.php');
require_once("$CFG->libdir/excellib.class.php");
require_once(__DIR__. '/classes/statistics.php');

$CFG->cachejs = false;

$PAGE->set_url(new moodle_url('/../blocks/stack/porcentage.php'));
$PAGE->set_context(\context_system::instance());
$PAGE->set_title('Datos del curso');

require_login();

//$PAGE->requires->js( new moodle_url('http://code.jquery.com/jquery-3.2.1.min.js') , true);
//$PAGE->requires->js( new moodle_url('https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.15.6/xlsx.core.min.js'), true);
$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/blocks/stack/js/xlsx.js'), true);

echo $OUTPUT->header();
    

$PAGE->requires->js_call_amd('block_stack/main','excel');

if ($SESSION->course) {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (!empty($_POST['porcentaje']) && $_POST['porcentaje'] >= 0 && $_POST['porcentaje'] <= 100 && is_numeric($_POST['porcentaje'])) {
            $DB->set_field('block_stack_course', 'percentage', $_POST['porcentaje']);
            $DB->set_field('block_stack_course', 'topgrade', $_POST['puntuacion']);
        }else{
            echo \core\notification::error("Se ha introducido un valor erróneo o un valor menor a 0 o mayor a 100.");
        }
    }
    $links = array("render_statistics.php","students.php", "porcentage.php");
    $links_names = array("Estadísticas","Estudiantes", "Porcentaje");

    $sql = 'SELECT * from {block_stack_course} WHERE courseid = ?';
    $result = $DB->get_record_sql($sql, array($SESSION->course));
    $coursename = $result->name;

    $output = $OUTPUT->heading('Porcentaje del curso ' . $result->name);

    $output  .= html_writer::start_div();
    $output  .= html_writer::start_div('d-flex flex-row-reverse');
    $output  .= html_writer::start_tag('ul', array("class" => "nav nav-tabs", "role" => "tablist", "style" => "float:right;"));
    for ($i = 0; $i < count($links_names); $i++) {
        $output .= html_writer::start_tag('li', array("class" => "nav-item"));
        $url = new moodle_url("../../blocks/stack/". $links[$i] );
        $output .= html_writer::link($url, $links_names[$i], array("class" => "nav-link"));
        $output .= html_writer::end_tag('li');
    }
    $output .= html_writer::end_div();

    $output .= html_writer::start_div('d-flex flex-row');
    $output .= html_writer::start_div('p-2', array("style" => "background-color: bisque; margin: 20px; padding: 20px; border-radius: 10px; width:30em; height:15em;"));
    $output .= html_writer::tag('h4', 'Puntuaciones del Curso', array("style" => "font-weight:bold; margin: 1em;"));
    $output .= html_writer::start_tag('form', array("method" => "POST" ,"style" => "margin-top:100px; width: 50%; max-width: 400px; margin: 0 auto 10px; display: flex; flex-direction: column; justify-content: center; align-items: center;"));
    $output .= html_writer::start_div('form-row', array("style" => "margin-top:0.5em;"));
    $output .= html_writer::tag('input', '', array("name" => "porcentaje", "id" => "input", "class" => "form-control", "placeholder" => "Introduce un porcentaje"));
    $output .= html_writer::tag('input', '', array("name" => "puntuacion", "id" => "input_2", "class" => "form-control", "placeholder" => "Introduce una puntuación", "style" => "margin:3px;"));
    $output .= html_writer::end_div();
    $output .= html_writer::tag('button', 'Enviar', array("type" => "submit", "class" => "btn btn-primary", "style" => "float:right; margin-top:10px;"));
    $output .= html_writer::end_tag('form');
    $output .= html_writer::end_div();

    $output .= html_writer::start_div('p-2', array("style" => "background-color: bisque; margin: 20px; padding: 20px; border-radius: 10px; width:20em; height:15em;"));
    $output .= html_writer::start_div('actual-po    rcentage', array("style" => "margin-top:3em; width: 100%; max-width: 400px; display: flex; flex-direction: column; justify-content: center; align-items: center;"));
    $output .= html_writer::tag('label', 'Porcentaje de alumnos que han canjeado sus puntos', array("for" => "input", "style" => "font-weight:bold; font-size:large; text-align:center;"));
    $output .= html_writer::end_div();
    $output .= html_writer::start_div('', array('style' => 'text-align: center; margin: 1em;'));
    $output .= html_writer::tag('button', 'Descargar Excel', array('class' => 'btn btn-success', 'id' => 'exchange'));
    $output .= html_writer::end_div();

    $output .= html_writer::end_div();

    $output .= html_writer::start_div('p-2', array("style" => "background-color: bisque; margin: 20px; padding: 20px; border-radius: 10px; width:20em;"));
    $output .= html_writer::start_div('actual-porcentage', array("style" => "margin-top:2em; width: 100%; max-width: 400px; display: flex; flex-direction: column; justify-content: center; align-items: center;"));
    $output .= html_writer::tag('label', 'Porcentaje del curso', array("for" => "input", "style" => "font-weight:bold; font-size:large;"));
    $output .= html_writer::start_div('', array("style" => "position: relative; margin: 0 auto; padding: 1.5em; width: 120px; height: 120px; border-radius: 50%; border: 5px solid #e84d51;"));
    $output .= html_writer::tag('h4', round($result->percentage, 2) . '%', array("style" => "padding-top: 0.5em; font-size: 28px; text-align: center; font-weight:bold;"));    
    $output .= html_writer::end_div();
    $output .= html_writer::end_div();
    $output .= html_writer::start_div('actual-puntuacion', array("style" => "margin-top:2em; width: 100%; max-width: 400px; display: flex; flex-direction: column; justify-content: center; align-items: center;"));
    $output .= html_writer::tag('label', 'Puntuación del curso', array("for" => "input", "style" => "font-weight:bold; font-size:large;"));
    $output .= html_writer::start_div('', array("style" => "position: relative; margin: 0 auto; padding: 1.5em; width: 120px; height: 120px; border-radius: 50%; border: 5px solid #e84d51;"));
    $output .= html_writer::tag('h4', round($result->topgrade, 2), array("style" => "padding-top: 0.5em; font-size: 28px; text-align: center; font-weight:bold;"));    
    $output .= html_writer::end_div();
    $output .= html_writer::end_div();
    $output .= html_writer::end_div();

    echo $output;

}

echo $OUTPUT->footer();
