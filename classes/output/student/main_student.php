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
namespace block_stack\output;

defined('MOODLE_INTERNAL') || die();

use renderable;
use renderer_base;
use templatable;
use html_writer;
use moodle_url;

class main_student implements renderable, templatable {

    private $courses;

    public function __construct($display_courses){
        $this->courses = $display_courses;
    }

    public function export_for_template(renderer_base $output) {
        $studentcourses = array();

        foreach ($this->courses as $c) {
            if ($c->role_name === 'student') {
                array_push($studentcourses, $c);
            }
        } 

        $content = (object) [
            'courses' => $studentcourses,
            'selection'   => '<p id="course_not_selected">Todavía no hay seleccionado ningún curso</p>'
        ];

        return $content;
    }
}