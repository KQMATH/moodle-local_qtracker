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
 * Renderable for questions page
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

/**
 * Class containing data for tags page.
 *
 * @copyright  2020 André Storhaug
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tags_page implements renderable, templatable {

    /** The default number of results to be shown per page. */
    const DEFAULT_PAGE_SIZE = 20;

    /** @var array|tags_table|\local_qtracker\tags_table */
    protected $tagstable = [];

    /**
     * Construct this renderable.
     *
     * @param \local_qtracker\tags_table $tagstable
     * @param int $courseid the id of the course
     */
    public function __construct(tags_table $tagstable, $courseid) {
        $this->tagstable = $tagstable;
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
        global $PAGE;
        $data = new stdClass();

        $newtagbutton = new stdClass();
        $newtagbutton->primary = true;
        $newtagbutton->name = "newtag";
        $newtagbutton->value = true;
        $newtagbutton->label = get_string('newtag', 'local_qtracker');
        $newtagurl = new \moodle_url('/local/qtracker/new_tag.php');
        $newtagurl->param('courseid', $this->courseid);
        $newtagbutton->action = $newtagurl;
        $data->newtagbutton = $newtagbutton;

        ob_start();
        $this->tagstable->out(self::DEFAULT_PAGE_SIZE, true);
        $tags = ob_get_contents();
        ob_end_clean();
        $data->tags = $tags;
        $data->courseid = $this->courseid;

        return $data;
    }
}
