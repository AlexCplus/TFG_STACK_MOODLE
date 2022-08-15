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
 * Class containing data for stack block.
 *
 * @package    block_stack
 * @copyright  2018 Ryan Wyllie <ryan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_stack\output;

defined('MOODLE_INTERNAL') || die();

use renderable;
use renderer_base;
use templatable;
use html_writer;
use moodle_url;
use core_course\external\course_summary_exporter;

require_once($CFG->dirroot . '/course/lib.php');

/**
 * Class containing data for stack block.
 *
 * @copyright  2018 Ryan Wyllie <ryan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class main implements renderable, templatable {

    /**
     * @var
     */
    private $courses;

    /**
     * main constructor.
     *
     * @param string $order Constant sort value from ../stack/lib.php
     * @param string $filter Constant filter value from ../stack/lib.php
     * @param string $limit Constant limit value from ../stack/lib.php
     */
    public function __construct($display_courses) {
        $this->courses = $display_courses;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param \renderer_base $output
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        $teachercourses = array();

        foreach ($this->courses as $c) {
            if ($c->role_name === 'teacher' || $c->role_name === 'editingteacher') {
                array_push($teachercourses, $c);
            }
        }
        //$chart = $this->get_chart_statistics_in_template();
        //$templatechart = $output->render($chart);
        if ($teachercourses != null) {
            $content = (object) [
                'courses' => $teachercourses,
                //'chart' => $templatechart
                'chart'   => '<p id="course_not_selected">Todavía no hay seleccionado ningún curso</p>'
            ];
        }
        return $content;
    }
}
