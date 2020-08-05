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
 * Renderable for issues page
 *
 * @package    local_qtracker
 * @author     André Storhaug <andr3.storhaug@gmail.com>
 * @copyright  2020 NTNU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_qtracker\output;

defined('MOODLE_INTERNAL') || die();

use coding_exception;
use dml_exception;
use moodle_exception;
use renderable;
use renderer_base;
use stdClass;
use templatable;
use local_qtracker\issue;
use local_qtracker\external\issue_exporter;

/**
 * Class containing data for question issue page.
 *
 * @copyright  2020 André Storhaug
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class question_issue_page implements renderable, templatable {

    /** The default number of results to be shown per page. */
    const DEFAULT_PAGE_SIZE = 20;

    protected $questionissue = null;

    protected $courseid = [];

    /**
     * Construct this renderable.
     *
     * @param \local_qtracker\question_issues_table $questionissuestable
     */
    public function __construct(issue $questionissue, $courseid) {
        $this->questionissue = $questionissue;
        $this->courseid = $courseid;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $output
     * @return stdClass
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function export_for_template(renderer_base $output) {
        $data = new stdClass();

        $context = \context_course::instance($this->courseid);
        $exporter = new issue_exporter($this->questionissue->get_issue_obj(), ['context' => $context]);
        $issuedetails = $exporter->export($output);
        $data->questionissue = $issuedetails;
print_r($data->questionissue);
        return $data;
    }
}
