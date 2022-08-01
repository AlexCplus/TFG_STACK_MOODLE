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

$functions = array(
        'block_stack_get_chart_db' => [
            'classname' => 'block_stack_external',
            'methodname' => 'get_data_course',
            'classpath' => '',
            'description' => 'Modify the chart representation',
            'type' => 'read',
            'ajax' => true,
            'capabilities' => '',
            'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE, 'local_mobile']
        ],
        'block_stack_get_table_students' => [
            'classname' => 'block_stack_external',
            'methodname' => 'get_table_students',
            'classpath' => '',
            'description' => 'Get the table of students in a list',
            'type' => 'read',
            'ajax' => true,
            'capabilities' => '',
            'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE, 'local_mobile']
        ],
        'block_stack_get_questions_shown' => [
            'classname' => 'block_stack_external',
            'methodname' => 'get_questions_to_show',
            'classpath' => '',
            'description' => 'Get the question selected',
            'type' => 'read',
            'ajax' => true,
            'capabilities' => '',
            'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE, 'local_mobile']
        ],
        'block_stack_get_questions_graph' => [
            'classname' => 'block_stack_external',
            'methodname' => 'get_graph_question',
            'classpath' => '',
            'description' => 'Get the graph',
            'type' => 'read',
            'ajax' => true,
            'capabilities' => '',
            'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE, 'local_mobile']
        ]
);
