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

use block_recentlyaccesseditems\external;
use mod_lti\local\ltiservice\response;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/classes/statistics.php');
require_once("$CFG->libdir/externallib.php");
require_once("$CFG->libdir/excellib.class.php");

class block_stack_external extends external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_data_course($id_course) {
        global $DB, $SESSION;
        
        $SESSION->course = $id_course;
        //Parameters validation
        $params = self::validate_parameters(self::get_data_course_parameters(),
                array('id_course' => $id_course));

        //Note: don't forget to validate the context and check capabilities
        $sql = 'SELECT * FROM {block_stack_quiz} WHERE course = ?';
        $passed = 0;
        $not_passed = 0;
        $quizs =  $DB->get_records_sql($sql, array($id_course));
        foreach ($quizs as $element) {
            $sql = 'SELECT * FROM {block_stack_quiz_attempt} WHERE quiz = ?';
            $attempts = $DB->get_records_sql($sql, array($element->quizid));
            foreach ($attempts as $attempt) {
                if ($attempt->pf == 0) {
                    $not_passed++;
                }else {
                    $passed++;
                }
            }
        }
        $new_output = array('id' => $id_course, 'passed' => $passed, 'not_passed' => $not_passed);

        return $new_output;
    }

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_data_course_parameters() {
        // FUNCTIONNAME_parameters() always return an external_function_parameters(). 
        // The external_function_parameters constructor expects an array of external_description.
        return new external_function_parameters(
            // a external_description can be: external_value, external_single_structure or external_multiple structure
            array('id_course' => new external_value(PARAM_INT, 'id_course for change the chart.')) 
        );
    }

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_data_course_returns() {
        return new external_single_structure(
            array(
                'id' => new external_value(PARAM_INT,'The new id changed to the new couse clicked.'),
                'passed' => new external_value(PARAM_INT,'Number of students that passed the course.'),
                'not_passed' => new external_value(PARAM_INT,'Number of students that didnt passed the course.')
            )
        );
    }

    public static function get_table_students($input_value) {
        global $DB, $SESSION;
        $params = self::validate_parameters(self::get_table_students_parameters(),
                array('input_value' => $input_value));
        $sql = 'SELECT * FROM {block_stack_students} WHERE courseid = ?';
        $students = $DB->get_records_sql($sql, array($SESSION->course));
        $names = array();
        foreach ($students as $student) {
            $name = $student->firstname . ' ' . $student->surname;
            $length_input = strlen($input_value);
            if(strnatcasecmp($input_value, substr($name, 0 , $length_input)) == 0) {
                $object = new stdClass();
                $object->name = $name;
                array_push($names, $object);
            } 
        }
        return $names;
    }

    public static function get_table_students_parameters() {
        return new external_function_parameters(
            // a external_description can be: external_value, external_single_structure or external_multiple structure
            array('input_value' => new external_value(PARAM_TEXT, 'name of the student to process')) 
        );
    }

    public static function get_table_students_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'name' => new external_value(PARAM_TEXT,'The new id changed to the new couse clicked.')
                )
            )
        );
    }

    public static function get_questions_to_show($selecteditem) {
        global $DB, $SESSION;
        $params = self::validate_parameters(self::get_questions_to_show_parameters(),
        array('selected_item' => $selecteditem));
        $sql_quiz = 'SELECT * FROM {block_stack_quiz} WHERE name = ?';
        $quizid = $DB->get_record_sql($sql_quiz, array($selecteditem));
        if(isset($SESSION->studentselected)) {
            $sql_student = 'SELECT * FROM {block_stack_students} WHERE firstname = ? AND surname = ?';
            $student_split = explode(' ', $SESSION->studentselected);
            if(!isset($student_split[0])) {
                $student_split[0] = '';
            }else if(!isset($student_split[1])) {
                $student_split[1] = '';
            }
            $studenid = $DB->get_record_sql($sql_student, array($student_split[0], $student_split[1]));
            $sql_attempt = 'SELECT * FROM {block_stack_quiz_attempt} WHERE quiz = ? AND userid = ?';
            $attempts = $DB->get_records_sql($sql_attempt, array($quizid->quizid, $studenid->studentid));
            $returnquestions = array();
            foreach ($attempts as $attempt) {
                $sql_question = 'SELECT * FROM {block_stack_question} WHERE uniqueid = ?';
                $questions = $DB->get_records_sql($sql_question, array($attempt->uniqueid));
                foreach ($questions as $question) {
                    $question->attempt = $attempt->attempt;
                    //$question->nodes = json_decode($question->nodes);
                    //$question->response = (array)json_decode($question->response);
                    //$question->error = json_decode($question->error);
                    //$question->graph = json_decode($question->graph);
                    /*$arraynode = array();
                    $arrayquestionnode = array();
                    foreach ($question->nodes as $nodes) {
                        foreach ($nodes as $rnode) {
                            $resultnodes = new stdClass();
                            $resultnodes->node = $rnode;
                            array_push($arraynode, $resultnodes);
                        }
                        array_push($arrayquestionnode, $arraynode);
                        $arraynode = array();
                    }

                    $iterator = 1;
                    $arrayans = array();
                    foreach ($question->response as $r) {
                        if(($iterator % 2) == 0) {
                            $iterator++;
                            continue;
                        }
                        $answer = new stdClass();
                        $answer->ans = $r;
                        array_push($arrayans, $answer);
                    }

                    $arrayerror = array();
                    foreach ($question->error as $err) {
                        $error = new stdClass();
                        $error->sentence = $err;
                        array_push($arrayerror, $error);
                    }
                    
                    $arraysvg = array();
                    foreach ($question->graph as $graph) {
                        $svg = new stdClass();
                        $svg->tag = $graph;
                        array_push($arraysvg, $svg);
                    }

                    $question->nodes = $arrayquestionnode;
                    $question->response = $arrayans;
                    $question->error = $arrayerror;
                    $question->graph = $arraysvg;
                    */
                    //$questionjs = new stdClass();
                    //$questionjs->response = '';
                    //$iterator = 1;
                    //foreach ($question->response as $r) {
                    //    if(($iterator % 2) == 0) {
                    //        $iterator++;
                    //        continue;
                    //    }
                    //    $questionjs->response .= '<p>La pregunta ' . $question->name . ' parte '. $iterator .' tiene la siguiente respuesta: ' . $r . '</p>';
                    //    $iterator++;
                    //}
                    //$iterator = 1;
                    //foreach ($question->error as $e) {
                    //    $questionjs->error .= '<p>Las respuesta para la parte'. $iterator.' por parte del sistema es ' . $e . '</p>';
                    //}
                    //$iterator = 1;
                    //foreach ($question->nodes as $answergraph) {
                    //    $questionjs->graphanswer .= 'La respuesta del grafo a la parte ' . $iterator . ' de la pregunta es:';
                    //    foreach ($answergraph as $node) {
                    //        $split = explode('-', $node);
                    //        if(isset($split[1])) {
                    //            $questionjs->graphanswer .= $split[1];
                    //        }
                    //        if(isset($split[2])) {
                    //            $questionjs->graphanswer .= $split[2];
                    //        }
                    //        $questionjs->graphanswer .= '-';
                    //    }
                    //    $questionjs->graphanswer .= '.';
                    //    $iterator++;
                    //}
                    //$iterator = 1;
                    //foreach ($question->graph as $graphsvg) {
                    //    $questionjs->svg .= '<p> El grafo resultante de la parte '. $iterator . ' es:' . $graphsvg . '</p>';
                    //    $iterator++;
                    //} 
                    array_push($returnquestions, $question);
                }
            }
        }
        return $returnquestions;
    }

    public static function get_questions_to_show_parameters() {
        return new external_function_parameters(
            // a external_description can be: external_value, external_single_structure or external_multiple structure
            array('selected_item' => new external_value(PARAM_TEXT, 'Selected item in the selection tag')) 
        );
    }

    public static function get_questions_to_show_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT,'The new id changed to the new couse clicked.'),
                    'questionid' => new external_value(PARAM_INT,'The new id changed to the new couse clicked.'),
                    'category' => new external_value(PARAM_INT,'The new id changed to the new couse clicked.'),
                    'name' => new external_value(PARAM_TEXT,'The new id changed to the new couse clicked.'),
                    'uniqueid' => new external_value(PARAM_INT,'The new id changed to the new couse clicked.'),
                    'slot' => new external_value(PARAM_INT,'The new id changed to the new couse clicked.'),
                    'mark' => new external_value(PARAM_FLOAT,'The new id changed to the new couse clicked.'),
                    'maxgrade' => new external_value(PARAM_FLOAT,'The new id changed to the new couse clicked.'),
                    'pf' => new external_value(PARAM_INT,'The new id changed to the new couse clicked.'),
                    'nodes' => new external_value(PARAM_RAW,'The new id changed to the new couse clicked.'),
                    'response' => new external_value(PARAM_RAW,'The new id changed to the new couse clicked.'),
                    'error' => new external_value(PARAM_RAW,'The new id changed to the new couse clicked.'),
                    'graph' => new external_value(PARAM_RAW,'The new id changed to the new couse clicked.'),
                    'attempt' => new external_value(PARAM_RAW,'The new id changed to the new couse clicked.')
                )
            )
        );
    }

    public static function get_student_chart($id_course) {
        global $DB, $USER, $SESSION;
        $SESSION->course = $id_course;
        $params = self::validate_parameters(self::get_student_chart_parameters(),
                array('id_course' => $id_course));
        $sql = 'SELECT * FROM {block_stack_quiz} WHERE course = ?';
        $passed = 0;
        $not_passed = 0;
        $quizs =  $DB->get_records_sql($sql, array($id_course));
        foreach ($quizs as $element) {
            $sql = 'SELECT * FROM {block_stack_quiz_attempt} WHERE quiz = ? AND userid = ?';
            $attempts = $DB->get_records_sql($sql, array($element->quizid, $USER->id));
            foreach ($attempts as $attempt) {
                if ($attempt->pf == 0) {
                    $not_passed++;
                }else {
                    $passed++;
                }
            }
        }
        $new_output = array('id' => $id_course, 'passed' => $passed, 'not_passed' => $not_passed);

        return $new_output;
    }

    public static function get_student_chart_parameters() {
        return new external_function_parameters(
            // a external_description can be: external_value, external_single_structure or external_multiple structure
            array('id_course' => new external_value(PARAM_INT, 'id_course for change to the chart student.')) 
        );
    }

    public static function get_student_chart_returns() {
        return new external_single_structure(
            array(
                'id' => new external_value(PARAM_INT,'The new id changed to the new couse clicked.'),
                'passed' => new external_value(PARAM_INT,'Number of students that passed the course.'),
                'not_passed' => new external_value(PARAM_INT,'Number of students that didnt passed the course.')
            )
        );
    }

    public static function excel_parameters() {
        return new external_function_parameters(
            array(
                'default_parameter' => new external_value(PARAM_INT,'Default id parameter'),
            )
        );
    }

    public static function excel_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'name' => new external_value(PARAM_TEXT,'Punctuation obtained by the percentage of the student.'),
                    'quiz' => new external_multiple_structure(
                        new external_single_structure(
                            array(
                                'quiz_name' => new external_value(PARAM_TEXT,'Punctuation obtained by the percentage of the student.'),
                                'mark' => new external_value(PARAM_TEXT,'Punctuation obtained by the percentage of the student.')
                            )
                        )
                    ),
                    "punctuation" => new external_value(PARAM_INT,'Punctuation obtained by the percentage of the student.')
                )
            )
        );
    }

    public static function excel() {
        global $SESSION, $DB;

        // Sacamos los quiz que estan en ese curso
        $course = $DB->get_record_sql('SELECT * FROM {block_stack_course} WHERE courseid = ?', array($SESSION->course));
        $students = $DB->get_records_sql(BLOCK_STACK_GET_STUDENTS_FROM_COURSE, array($SESSION->course, 'student'));
        $quizs = $DB->get_records_sql('SELECT * FROM {block_stack_quiz} WHERE course = ?', array($SESSION->course));
        $returnstudents = array();
        $returnstudents[0] = ['name' => $course->name, 'quiz' => array(), 'punctuation' => -1];
        foreach ($quizs as $quiz) {
            array_push($returnstudents[0]['quiz'], ['quiz_name' => $quiz->name, 'mark' => '-1']);
        }
        foreach ($students as $student) {
            $result = array();
            $score = 0;
            $scoremax = 0;
            $name = $student->firstname . ' ' . $student->lastname;
            foreach ($quizs as $quiz) {
                $marks = $DB->get_record_sql('SELECT mark, maxgrade FROM {block_stack_quiz_attempt} 
                                                WHERE quiz = ? AND attempt = ? AND userid = ?', array($quiz->quizid, 1, $student->id));
                if (isset($marks) && isset($marks->mark)) {
                    array_push($result, ['quiz_name' => $quiz->name , 'mark' => $marks->mark . '/' . $marks->maxgrade]);
                    $score += $marks->mark;
                }
                // Sumamos 10 porque las notas estan sobre 10, entonces no hace falta hacer una consulta a la base de datos.
                // De todas formas: REVISAR. 
                $scoremax += 10; 
            }
            $calculatescore = ($scoremax * $course->percentage) / 100;
            $punctuation = 0;
            if ($score >= $calculatescore) {
                $punctuation = $course->topgrade;
            }
            //array_push($returnstudents, [$name => $result, "punctuation" => $punctuation]);
            array_push($returnstudents, ['name' => $name, 'quiz' => $result ,'punctuation' => $punctuation]);
        }
        return $returnstudents;
    }

    public static function get_graph_question() {
        
    }

    public static function get_graph_question_parameters() {

    }
    
    public static function get_graph_question_returns() {
        
    }
}
