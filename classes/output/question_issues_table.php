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
 * Table of question issues.
 *
 * @package    local_qtracker
 * @author     André Storhaug <andr3.storhaug@gmail.com>
 * @copyright  2020 NTNU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_qtracker\output;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/tablelib.php');

use context_system;
use moodle_url;
use table_sql;

/**
 * Question issues table.
 *
 * @package    local_qtracker
 * @copyright  2020 André Storhaug
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class question_issues_table extends table_sql {

    /**
     * Sets up the table.
     *
     * @param string $uniqueid Unique id of table.
     * @param moodle_url $url The base URL.
     */
    public function __construct($uniqueid, $url, $context, $manuallySorted) {
        global $CFG;
        parent::__construct($uniqueid);

        $this->context = $context;
        // Define columns in the table.
        $this->define_table_columns();
        // Set the baseurl.
        $this->define_baseurl($url);
        // Define configs.
        $this->define_table_configs();
        // Define SQL.
        $this->setup_sql_queries();
    }

    /**
     * Generate the display of the id column.
     * @param object $data the table row being output.
     * @return string HTML content to go inside the td.
     */
    public function col_id($data) {
        global $COURSE;

        if ($data->id) {
            $issueid = $data->id;
            $url = new \moodle_url('/local/qtracker/issue.php');
            $url->param('courseid', $COURSE->id);
            $url->param('issueid', $issueid);
            $id = \html_writer::link($url, $data->id);
            return $id;
        } else {
            return '-';
        }
    }

    /**
     * Generate the display of the question name column.
     * @param object $data the table row being output.
     * @return string HTML content to go inside the td.
     */
    protected function col_questionid($data) {
        if ($data->questionid) {
            return $data->questionid;
        } else {
            return '-';
        }
    }

    /**
     * Generate the display of the title.
     * @param object $data the table row being output.
     * @return string HTML content to go inside the td.
     */
    protected function col_title($data) {
        global $COURSE;
        if ($data->title) {
            $issueid = $data->id;
            $url = new \moodle_url('/local/qtracker/issue.php');
            $url->param('courseid', $COURSE->id);
            $url->param('issueid', $issueid);
            $title = \html_writer::link($url, $data->title);
            return $title;            // need to change it to correct link.
            // return '<a href="/user/profile.php?id='.$data->questionid.'">'.$data->title.'</a>';
        } else {
            return '-';
        }
    }

    /**
     * Generate the display of the description.
     * @param object $data the table row being output.
     * @return string HTML content to go inside the td.
     */
    protected function col_description($data) {
        if ($data->description) {
            return $data->description;
        } else {
            return '-';
        }
    }

    /**
     * The timecreated column.
     * @param stdClass $data The row data.
     * @return string
     */
    public function col_timecreated($data) {
        return userdate($data->timecreated);
    }

    /**
     * TODO: touch up  this
     * Setup the headers for the table.
     */
    protected function define_table_columns() {

        // Define headers and columns.
        // TODO: define strings in lang file.
        $cols = array(
            'id' => get_string('id', 'local_qtracker'),
            'questionid' => get_string('questionid', 'local_qtracker'),
            'title' => get_string('title', 'local_qtracker'),
            'description' => get_string('description', 'local_qtracker'),
            'timecreated' => get_string('timecreated', 'local_qtracker')
        );

        $this->define_columns(array_keys($cols));
        $this->define_headers(array_values($cols));
    }

    /**
     * Define table configs.
     */
    protected function define_table_configs() {
        $this->collapsible(false);
        $this->sortable(true);
        $this->pageable(true);
    }

    /**
     * Builds the SQL query.
     *
     * @return array containing sql to use and an array of params.
     */
    public function setup_sql_queries() {
        global $DB;

        $contextids = explode('/', trim($this->context->path, '/'));
        // Get all child contexts.
        $children = $this->context->get_child_contexts();
        foreach ($children as $c) {
            $contextids[] = $c->id;
        }

        list($insql, $inarams) = $DB->get_in_or_equal($contextids, SQL_PARAMS_NAMED);


        // TODO: Write SQL to retrieve all rows...
        $fields = 'DISTINCT ';
        $fields .= 'qi.*';
        $from = '{local_qtracker_issue} qi';
        $from .= "\nJOIN {context} ctx ON qi.contextid = ctx.id";
        $where = "\nctx.id $insql";
        $params = $inarams;

        // The WHERE clause is vital here, because some parts of tablelib.php will expect to
        // add bits like ' AND x = 1' on the end, and that needs to leave to valid SQL.
        $this->set_count_sql("SELECT COUNT(1) FROM (SELECT $fields FROM $from WHERE $where) temp WHERE 1 = 1", $params);

        list($fields, $from, $where, $params) = $this->update_sql_after_count($fields, $from, $where, $params);
        $this->set_sql($fields, $from, $where, $params);
    }

    /**
     * A chance for subclasses to modify the SQL after the count query has been generated,
     * and before the full query is constructed.
     * @param string $fields SELECT list.
     * @param string $from JOINs part of the SQL.
     * @param string $where WHERE clauses.
     * @param array $params Query params.
     * @return array with 4 elements ($fields, $from, $where, $params) as from base_sql.
     */
    protected function update_sql_after_count($fields, $from, $where, $params) {
        return [$fields, $from, $where, $params];
    }
}
