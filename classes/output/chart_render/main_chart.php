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

namespace block_stack\output;

use renderable;
use renderer_base;
use templatable;
use html_writer;
use moodle_url;

class main_chart implements renderable {

    public function __construct(){

    }

    private function get_chart_statistics_in_template() {
        $chart = new \core\chart_pie();
        $chart->set_doughnut(true); // Calling set_doughnut(true) we display the chart as a doughnut.
        $chart->add_series(new \core\chart_series('My series title', [400, 460, 1120, 540]));
        $chart->set_labels((['2004', '2005', '2006', '2007']));
        return $chart;
    }

    public function export_for_template(renderer_base $output) {
        $chart = $this->get_chart_statistics_in_template();
        $templatechart = $output->render_chart($chart);
        return $templatechart;
        /*global $DB;
        $render_from_template = null;
        $sql = 'SELECT * FROM {block_stack_quiz} WHERE courseid = ?';
        $passed = 0;
        $not_passed = 0;
        $response =  $DB->get_records_sql($sql, array($id_course));
        foreach ($response as $element) {
            if ($element->passed == 1) {
                $passed++;
            }else{
                $not_passed++;
            }
        }
        try {
            $chart = new \core\chart_pie();
            $chart->set_doughnut(true);
            $chart->add_series(new \core\chart_series('% superados y no superados', [$passed, $not_passed]));
            $chart->set_labels((['Nº superados', 'Nº no superados']));
            $chart->set_title('Estadísticas de un curso');
            $render_from_template = $output->render_chart($chart);
            $new_output = array('id' => $id_course, 'output' => $render_from_template);   
        }catch(Throwable $e) {
            $exception = get_exception_info($e);
            $new_output = array('id' => -1, 'output' => '<p>error in database in table mdl_block_stack_quiz</p>');
        }
        */

        return $new_output;
    }
}