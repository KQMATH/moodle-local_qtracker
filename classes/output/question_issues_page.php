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
 * Class containing data for question issues.
 *
 * @package    local_qtracker
 * @copyright  2020 André Storhaug
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_qtracker\output;
defined('MOODLE_INTERNAL') || die();

use coding_exception;
use dml_exception;
use moodle_exception;
use moodle_url;
use renderable;
use renderer_base;
use single_select;
use stdClass;
use templatable;


/**
 * Class containing data for question issues.
 *
 * @copyright  2020 André Storhaug
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class question_issues_page implements renderable, templatable {

    /** The default number of results to be shown per page. */
    const DEFAULT_PAGE_SIZE = 20;

    protected $questionissuestable = [];

    /**
     * Construct this renderable.
     *
     * @param \local_qtracker\question_issues_table $questionissuestable
     */
    public function __construct(question_issues_table $questionissuestable) {
        $this->questionissuestable = $questionissuestable;
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

        ob_start();
        $this->questionissuestable->out(self::DEFAULT_PAGE_SIZE, true);
        $questionissues = ob_get_contents();
        ob_end_clean();
        $data->questionissues = $questionissues;

        return $data;
    }
}
