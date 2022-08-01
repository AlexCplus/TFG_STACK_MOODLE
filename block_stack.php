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

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/blocks/stack/classes/statistics.php');

class block_stack extends block_base {

    private $stackinfo = array();

    public function init() {
        global $CFG;
        $this->title = get_string('pluginname', 'block_stack');
        $CFG->cachejs = false; // Poner en el config.php sive para la cache pq si no no se actualizan los archivos js
    }

    public function get_content() {
        global $DB, $PAGE;

        if ($this->content !== null) {
            return $this->content;
        }
        if (!empty($this->config->text)) {
            $this->content->text = $this->config->text;
        }
        $courses = $DB->get_records('course');

        // Filtrar el curso por si existen preguntas de Stack en alguno de los cuestionarios.
        foreach ($courses as $course) {
            if ($course != null && $course->format != "site") {
                $stackinfo = new block_stack_course($course->id, $course->shortname);
                $stackinfo->block_stack_course_get_quizzes();
                $stackinfo->block_stack_course_get_students();
                $stackinfo->block_stack_course_field_quiz_attempts();
                $stackinfo->block_stack_course_filed_questions();
                $stackinfo->block_stack_course_store_info_db();
            }
        }

        $PAGE->requires->js_call_amd('block_stack/main','init');

        $this->content = new stdClass;

        $renderable = new \block_stack\output\main($courses);
        $renderer = $this->page->get_renderer('block_stack');

        $this->content = (object) [
            'text' => $renderer->render($renderable),
        ];

        $url = new moodle_url("../blocks/stack/render_statistics.php");
        $this->content->footer = html_writer::div(
            html_writer::link($url, "Ver más"),
            'block_stack'
        );

        return $this->content;
    }

    public function specialization() {
        if (isset($this->config)) {
            if (empty($this->config->title)) {
                $this->title = get_string('defaulttitle', 'block_stack');
            } else {
                $this->title = $this->config->title;
            }

            if (empty($this->config->text)) {
                $this->config->text = get_string('defaulttext', 'block_stack');
            }
        }
    }

    public function instance_allow_multiple() {
        return true;
    }
}
