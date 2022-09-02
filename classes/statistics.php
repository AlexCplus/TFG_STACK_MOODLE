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

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/mod/quiz/locallib.php');
require_once($CFG->dirroot . '/mod/quiz/report/reportlib.php');
require_once($CFG->dirroot . '/question/type/stack/renderer.php');
require_once($CFG->dirroot . '/question/type/stack/question.php');
require_once($CFG->dirroot . '/question/type/stack/stack/graphlayout/svgrenderer.php');

define('BLOCK_STACK_FIRST_ATTEMPTS',    1);
define('BLOCK_STACK_GRADES_CALCULATE',  0);

define('BLOCK_STACK_GET_QUIZ_COURSES',

'SELECT qz.*
    FROM  {quiz} qz
    WHERE qz.course=?'

);

define('BLOCK_STACK_GET_QUIZ_QUESTIONS',

'SELECT q.*, qs.slot
    FROM {question} q
    JOIN {quiz_slots} qs ON qs.questionid = q.id
    JOIN {quiz} qz ON qz.id=qs.quizid
    JOIN {quiz_attempts} qa ON qa.quiz = qz.id
    WHERE qz.id= ? AND q.qtype="stack" AND qa.id = ?'

);

define('BLOCK_STACK_GET_STUDENTS_FROM_COURSE',

'SELECT u.id,u.firstname, u.lastname
    FROM {user} u
    JOIN {role_assignments} ra ON u.id = ra.userid
    JOIN {role} r ON ra.roleid = r.id
    JOIN {context} ctx ON ra.contextid = ctx.id AND ctx.contextlevel = 50
    JOIN {course} c ON ctx.instanceid = ?
    WHERE r.archetype = ?
    GROUP BY u.id'

);

define('BLOCK_STACK_GET_STUDENT_MARK_QUIZ',

'SELECT   id, userid, uniqueid, sumgrades, quiz, attempt
    FROM  {quiz_attempts} qtempt
    WHERE qtempt.quiz = ?
    AND   userid = ?'

);


define('BLOCK_STACK_GET_QUIZ_MAX_MARK',

'SELECT   grade
    FROM  {quiz}
    WHERE id = ?'

);

define('BLOCK_STACK_CHECK_UNIQUEID',
    'SELECT *
    FROM {block_stack_quiz} qz
    WHERE qz.uniqueid = ?'
);

define('BLOCK_STACK_GET_CMID',
    'SELECT cm.section
    FROM mdl_course_modules cm 
    JOIN mdl_modules md ON md.id = cm.module JOIN mdl_quiz m ON m.id = ? 
    WHERE cm.course = ? AND md.name = "quiz"'
);

// Necesitamos esta sentencia para crear un new attempt.
define('BLOCK_STACK_GET_STACK_QUESTIONS',

'SELECT
            quba.id AS qubaid,
            quba.contextid,
            quba.component,
            quba.preferredbehaviour,
            qa.id AS questionattemptid,
            qa.questionusageid,
            qa.slot,
            qa.behaviour,
            qa.questionid,
            qa.variant,
            qa.maxmark,
            qa.minfraction,
            qa.maxfraction,
            qa.flagged,
            qa.questionsummary,
            qa.rightanswer,
            qa.responsesummary,
            qa.timemodified,
            qas.id AS attemptstepid,
            qas.sequencenumber,
            qas.state,
            qas.fraction,
            qas.timecreated,
            qas.userid,
            qasd.name,
            qasd.value
FROM
            {question_usages}            quba
LEFT JOIN
            {question_attempts}          qa   ON qa.questionusageid    = quba.id
LEFT JOIN
            {question_attempt_steps}     qas  ON qas.questionattemptid = qa.id
LEFT JOIN
            {question_attempt_step_data} qasd ON qasd.attemptstepid    = qas.id
WHERE
            quba.id = :qubaid
ORDER BY
        qa.slot,
        qas.sequencenumber'
);

class block_stack_quiz {

    private $id;

    private $course;

    private $name;

    private $attempts = array();

    public function __construct($id, $course, $name) {
        $this->id = $id;
        $this->course = $course;
        $this->name = $name;
    }

    public function add_new_attempt_quiz($new_attempt) {
        array_push($this->attempts, $new_attempt);
    }

    // Getters. 
    public function get_quiz_id() {
        return $this->id;
    }

    public function get_quiz_course() {
        return $this->course;
    }

    public function get_quiz_name() {
        return $this->name;
    }

    public function get_quiz_attempts() {
        return $this->attempts;
    }
}

class block_stack_quiz_attempt {

    private $id;

    private $quiz;

    private $userid;

    private $attempt;

    private $uniqueid;

    private $mark; // Si es null es que no ha puntuado. 

    private $maxgrade;

    private $passed_or_failed = 0; // 0 Si no lo ha pasado, 1 si si

    private $questions = array();

    public function __construct($id, $quiz, $userid, $attempt, $uniqueid, $mark, $maxgrade, $pf) {
        $this->id = $id;
        $this->quiz = $quiz;
        $this->userid = $userid;
        $this->attempt = $attempt;
        $this->uniqueid = $uniqueid;
        $this->mark = $mark;
        $this->maxgrade = $maxgrade;
        $this->passed_or_failed = $pf;
    }

    public function set_questions($add_question) {
        array_push($this->questions, $add_question);
    }

    public function get_quiz_attempt_id() {
        return $this->id;
    }

    public function get_quiz_attempt_quiz() {
        return $this->quiz;
    }

    public function get_quiz_attempt_userid() {
        return $this->userid;
    }

    public function get_quiz_attempt_attempt() {
        return $this->attempt;
    }

    public function get_quiz_attempt_uniqueid() {
        return $this->uniqueid;
    }

    public function get_quiz_attempt_mark() {
        return $this->mark;
    }

    public function get_quiz_attemtp_maxgrade() {
        return $this->maxgrade;
    }

    public function get_quiz_attempt_passed_or_failed() {
        return $this->passed_or_failed;
    } 

    public function get_quiz_attempt_questions() {
        return $this->questions;
    }
}

class block_stack_questions {

    private $id;

    private $category;

    private $name;

    private $slot;

    private $userid;

    private $mark;

    private $maxgrade;

    private $passed_or_failed = 0;

    private $nodes;

    private $response;

    private $error;

    private $graph;

    public function __construct($id, $category, $name, $slot, $userid, 
                                $mark, $maxgrade, $pf, $nodes, $response, $error, $graph) {
        $this->id = $id;
        $this->category = $category;
        $this->name = $name;
        $this->slot = $slot;
        $this->userid = $userid;
        $this->mark = $mark;
        $this->maxgrade = $maxgrade;
        $this->passed_or_failed = $pf;
        $this->nodes = $nodes;
        $this->response = $response;
        $this->error = $error;
        $this->graph = $graph;
    }

    public function get_question_id() {
        return $this->id;
    }

    public function get_question_category() {
        return $this->category;
    }

    public function get_question_name() {
        return $this->name;
    }

    public function get_question_slot() {
        return $this->slot;
    }

    public function get_question_userid() {
        return $this->userid;
    }

    public function get_question_mark() {
        return $this->mark;
    }

    public function get_question_maxgrade() {
        return $this->maxgrade;
    }

    public function get_question_passed_or_failed() {
        return $this->passed_or_failed;
    }

    public function get_question_nodes() {
        $nodesresponse = array();
        foreach ($this->nodes as $node) {
            array_push($nodesresponse, $node[0]->_answernotes);
        }
        return $nodesresponse;
    }
    
    public function get_question_response() {
        return $this->response;
    }

    public function get_question_error() {
        return $this->error;
    }

    public function get_question_graph() {
        return $this->graph;
    }
}

class block_stack_students {

    private $id;

    private $firstname;

    private $surname;

    public function __construct($id, $firstname, $surname) {
        $this->id = $id;
        $this->firstname = $firstname;
        $this->surname = $surname;
    }

    public function get_student_id() {
        return $this->id;
    }

    public function get_student_firstname() {
        return $this->firstname;
    }

    public function get_student_surname() {
        return $this->surname;
    }
}

class block_stack_course extends qtype_stack_question {

    private $courseid;

    private $coursename;

    private $quiz = array();

    private $students = array();

    public function __construct($id, $name) {
        $this->courseid = $id;
        $this->coursename = $name;
    }

    public function block_stack_course_get_quizzes() {
        global $DB;

        $quizzes = $DB->get_records_sql(BLOCK_STACK_GET_QUIZ_COURSES, array($this->courseid));
        
        foreach($quizzes as $quiz) {
            $newquiz = new block_stack_quiz($quiz->id, $quiz->course, $quiz->name);
            array_push($this->quiz, $newquiz);
        }
    }

    public function block_stack_course_get_students() {
        global $DB;
        
        $students = $DB->get_records_sql(BLOCK_STACK_GET_STUDENTS_FROM_COURSE, array($this->courseid, 'student'));

        foreach($students as $student) {
            $newstudent = new block_stack_students($student->id, $student->firstname, $student->lastname);
            array_push($this->students, $newstudent);
        }
    }

    public function block_stack_course_field_quiz_attempts() {
        global $DB;

        foreach($this->quiz as $quiz) {
            foreach($this->students as $student) {
                $quiz_attempt = $DB->get_records_sql(BLOCK_STACK_GET_STUDENT_MARK_QUIZ, array($quiz->get_quiz_id(), $student->get_student_id()));
                foreach($quiz_attempt as $attempt) {
                    $maxgrade = $DB->get_record_sql(BLOCK_STACK_GET_QUIZ_MAX_MARK, array($quiz->get_quiz_id()));
                    $passed_or_failed = false;
                    if ($attempt->sumgrades > ($maxgrade->grade) / 2) {
                        $passed_or_failed = true;
                    }
                    $new_attempt = new block_stack_quiz_attempt(
                        $attempt->id, $attempt->quiz, $attempt->userid,
                        $attempt->attempt, $attempt->uniqueid, $attempt->sumgrades, $maxgrade->grade, $passed_or_failed
                    );
                    $quiz->add_new_attempt_quiz($new_attempt);
                }
            }
        }
    }

    public function block_stack_course_filed_questions() {
        global $DB, $SESSION;

        foreach($this->quiz as $quiz) {
            foreach($quiz->get_quiz_attempts() as $attempt) {
                $questions_from_attempt = $DB->get_records_sql(BLOCK_STACK_GET_QUIZ_QUESTIONS, array($quiz->get_quiz_id(), $attempt->get_quiz_attempt_id()));
                $qubaid = $attempt->get_quiz_attempt_uniqueid();
                $records = $DB->get_recordset_sql(BLOCK_STACK_GET_STACK_QUESTIONS, array("qubaid" => $qubaid));
                if (!$records->valid()) {
                    throw new coding_exception('Failed to load questions_usage_by_activity ' . $qubaid);
                }
                $quba = question_usage_by_activity::load_from_records($records, $qubaid);
                for ($i = 0; $i < $quba->question_count(); $i++) {
                    $qa = $quba->get_question_attempt($quba->get_slots()[$i]);
                    $cmid = $DB->get_record_sql(BLOCK_STACK_GET_CMID, array($this->courseid, $quiz->get_quiz_id()));
                    $attemptobj = quiz_create_attempt_handling_errors($attempt->get_quiz_attempt_id(), $cmid);
                    $options = $attemptobj->get_display_options(true);
                    $markq = $qa->format_mark($options->markdp);
                    $maxq = $qa->format_max_mark($options->markdp);
                    $passed_or_failed =  false;
                    if((float)$markq > ((float)$maxq / 2)) {
                        $passed_or_failed = true;
                    }
                    $nodes = $this->block_stack_statistics_store_nodes_results_db($quba, $i);
                    
                    $error = array();
                    foreach($nodes as $feedbackerror) {
                        foreach($feedbackerror[0]->_feedback as $f) {
                            array_push($error, $f->feedback);
                        }
                    }

                    $graph = array();
                    foreach($nodes as $abstract_graph) {
                        $newgraph = stack_abstract_graph_svg_renderer::render($abstract_graph[1], $attempt->get_quiz_attempt_id());
                        array_push($graph, $newgraph);
                    } 
                    
                    $newquestion = new block_stack_questions(current($questions_from_attempt)->id, current($questions_from_attempt)->category, current($questions_from_attempt)->name,
                    current($questions_from_attempt)->slot, $attempt->get_quiz_attempt_userid(), (float)$markq, (float)$maxq , $passed_or_failed,
                        $nodes, $SESSION->response, $error, $graph
                    );
                    next($questions_from_attempt);
                    // Creamos el grafo 

                    $attempt->set_questions($newquestion);
                }
            }
        }
    }

    public function block_stack_statistics_store_nodes_results_db($quba, $i) {
        global $SESSION;
        
        $qa = $quba->get_question_attempt($quba->get_slots()[$i]);
        $question = $qa->get_question();
        $response = $qa->get_last_qt_data();
        $SESSION->response = json_encode($response);
        $nodes = array();

        if (isset($question->inputs)) {
            $questiontext = $question->questiontextinstantiated;
            foreach ($question->inputs as $name => $input) {
                // Get the actual value of the teacher's answer at this point.
                $tavalue = $question->get_ta_for_input($name);

                $fieldname = $qa->get_qt_field_name($name);
                $state = $question->get_input_state($name, $response);

                $questiontext = str_replace("[[input:{$name}]]",
                        $input->render($state, $fieldname, true, $tavalue),
                        $questiontext);

                $questiontext = $input->replace_validation_tags($state, $fieldname, $questiontext);

                if ($input->requires_validation()) {
                    $inputstovaldiate[] = $name;
                }
            }

            foreach ($question->prts as $index => $prt) {
                // The behaviour name test here is a hack. The trouble is that interactive
                // behaviour or adaptivemulipart does not show feedback if the input
                // is invalid, but we want to show the CAS errors from the PRT.
                $result = $this->get_block_prt_result($question, $index, $response, $qa->get_state()->is_finished());
                $feedback = html_writer::nonempty_tag('span', $result[0]->errors,
                        array('class' => 'stackprtfeedback stackprtfeedback-' . $name));
                $questiontext = str_replace("[[feedback:{$index}]]", $feedback, $questiontext);
                
                array_push($nodes, $result);
            }
        }
        return $nodes;
    }

    /**
     * Evaluate a PRT for a particular response.
     * @param string $index the index of the PRT to evaluate.
     * @param array $response the response to process.
     * @param bool $acceptvalid if this is true, then we will grade things even
     *      if the corresponding inputs are only VALID, and not SCORE.
     * @return stack_potentialresponse_tree_state the result from $prt->evaluate_response(),
     *      or a fake state object if the tree cannot be executed.
     */
    public function get_block_prt_result($question, $index, $response, $acceptvalid) {
        $question->validate_cache($response, $acceptvalid);

        if (array_key_exists($index, $question->prtresults)) {
            return $this->prtresults[$index];
        }

        // We can end up with a null prt at this point if we have question tests for a deleted PRT.
        if (!array_key_exists($index, $question->prts)) {
            // Bail here with an empty state to avoid a later exception which prevents question test editing.
            return new stack_potentialresponse_tree_state(null, null, null, null);
        }
        $prt = $question->prts[$index];

        if (!$question->has_necessary_prt_inputs($prt, $response, $acceptvalid)) {
            $question->prtresults[$index] = new stack_potentialresponse_tree_state(
                    $prt->get_value(), null, null, null);
            return $question->prtresults[$index];
        }

        $prtinput = $question->get_prt_input($index, $response, $acceptvalid);

        $question->prtresults[$index] = $prt->evaluate_response($question->session,
                $question->options, $prtinput, $question->seed);
        
        $graph = $prt->get_prt_graph();

        return array($question->prtresults[$index], $graph);
    }

    public function block_stack_course_store_info_db() {
        $course = new stdClass();
        global $DB;

        $course->courseid = $this->courseid;
        $course->name = $this->coursename;
        $data = $DB->get_record_sql('SELECT * from {block_stack_course} WHERE courseid = ?', array($this->courseid));
        if (!$data) {
            $DB->insert_record('block_stack_course', $course);
        }

        foreach ($this->students as $student) {
            $studentdb = new stdClass();
            $studentdb->studentid = $student->get_student_id();
            $studentdb->courseid = $this->courseid;
            $studentdb->firstname = $student->get_student_firstname();
            $studentdb->surname = $student->get_student_surname();
            $sql = 'SELECT * FROM {block_stack_students} WHERE studentid = ? AND courseid = ?';
            if (!$DB->get_record_sql($sql, array($studentdb->studentid, $this->courseid))) {
                $DB->insert_record('block_stack_students', $studentdb);
            }
        }

        foreach ($this->quiz as $quiz) {
            $quizdb = new stdClass();
            $quizdb->quizid = $quiz->get_quiz_id(); 
            $quizdb->course = $quiz->get_quiz_course();
            $quizdb->name = $quiz->get_quiz_name();
            $sql = 'SELECT * FROM {block_stack_quiz} WHERE quizid = ?';
            if (!$DB->get_record_sql($sql, array($quizdb->quizid))){
                $DB->insert_record('block_stack_quiz', $quizdb);
            }
            foreach ($quiz->get_quiz_attempts() as $attempt) {
                $attemptdb = new stdClass();
                $attemptdb->quiz_attempt_id = $attempt->get_quiz_attempt_id(); $attemptdb->quiz = $attempt->get_quiz_attempt_quiz();
                $attemptdb->userid = $attempt->get_quiz_attempt_userid(); $attemptdb->attempt = $attempt->get_quiz_attempt_attempt();
                $attemptdb->uniqueid = $attempt->get_quiz_attempt_uniqueid(); $attemptdb->mark = $attempt->get_quiz_attempt_mark();
                $attemptdb->maxgrade = $attempt->get_quiz_attemtp_maxgrade(); $attemptdb->pf = $attempt->get_quiz_attempt_passed_or_failed();
                $sql = 'SELECT * FROM {block_stack_quiz_attempt} WHERE uniqueid = ?'; 
                if (!$DB->get_record_sql($sql, array($attemptdb->uniqueid))) {
                    $DB->insert_record('block_stack_quiz_attempt', $attemptdb);
                }

                foreach ($attempt->get_quiz_attempt_questions() as $question) {
                    $questiondb = new stdClass();
                    $questiondb->questionid = $question->get_question_id();
                    $questiondb->category = $question->get_question_category();
                    $questiondb->name = $question->get_question_name();
                    $questiondb->uniqueid = $attempt->get_quiz_attempt_uniqueid();
                    $questiondb->slot = $question->get_question_userid();
                    $questiondb->mark = $question->get_question_mark();
                    $questiondb->maxgrade = $question->get_question_maxgrade();
                    $questiondb->pf = $question->get_question_passed_or_failed();
                    $questiondb->nodes = json_encode($question->get_question_nodes());
                    $questiondb->response = $question->get_question_response();
                    $questiondb->error = json_encode($question->get_question_error());
                    $questiondb->graph = json_encode($question->get_question_graph());
                    $sql = 'SELECT * FROM {block_stack_question} WHERE uniqueid = ? AND name = ?';
                    if (!$DB->get_record_sql($sql, array($questiondb->uniqueid, $questiondb->name))){
                        $DB->insert_record('block_stack_question', $questiondb);
                    }
                }
            }
        }
    }
}
