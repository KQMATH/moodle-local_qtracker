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
 * Table of questions with registered issues.
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
 * Questions table.
 *
 * @package    local_qtracker
 * @copyright  2020 André Storhaug
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class questions_table extends table_sql {

    /**
     * Sets up the table.
     *
     * @param string $uniqueid Unique id of table.
     * @param $context
     * @param moodle_url $url The base URL.
     */
    public function __construct($uniqueid, $url, $context) {
        global $CFG;
        parent::__construct($uniqueid);
        // TODO: determine which context to use...
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
     * Generate the display of the question name column.
     * @param object $data the table row being output.
     * @return string HTML content to go inside the td.
     */
    protected function col_id($data) {
        if ($data->id) {
            return $data->id;
        } else {
            return '-';
        }
    }

    /**
     * Generate the display of the title.
     * @param object $data the table row being output.
     * @return string HTML content to go inside the td.
     */
    protected function col_name($data) {
        if ($data->name) {
            $id = $data->id;
            $name = \html_writer::link("#", $data->name, array('onclick' => "showIssuesInPane($id);return false;"));
            return $name;            // need to change it to correct link.
            // return '<a href="/user/profile.php?id='.$data->questionid.'">'.$data->title.'</a>';
        } else {
            return '-';
        }
    }

    /**
     * Generate the display of the new, open and close column
     * @param $cols extra_colums (new, open and close)
     * @param $data the table row being output
     * @return |null string html content to go inside the td.
     */
    public function other_cols($cols, $data) {
        switch ($cols) {
            case 'new':
            case 'open':
            case 'closed':
                $nrofstate = $data->{$cols};
                if ($nrofstate < 1) {
                    return $nrofstate;
                }
                $id = $data->id;
                $closed = \html_writer::link("#", $nrofstate, array('onclick' => "showIssuesInPane($id, '$cols');return false;"));
                return $closed;
            default:
                return null;
        }
    }

    /**
     * TODO: touch up  this
     * Setup the headers for the table.
     */
    protected function define_table_columns() {

        // Define headers and columns.
        // TODO: define strings in lang file.
        $cols = array(
            'id' => get_string('questionid', 'local_qtracker'),
            'name' => get_string('name', 'local_qtracker'),
            'new' => get_string('new', 'local_qtracker'),
            'open' => get_string('open', 'local_qtracker'),
            'closed' => get_string('closed', 'local_qtracker')
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

        $fields = 'q.id,
                    q.name,';
        $fields .= "COUNT(case qi.state when 'new' then 1 else null end) AS new,
                    COUNT(case qi.state when 'open' then 1 else null end) AS open,
                    COUNT(case qi.state when 'closed' then 1 else null end) AS closed";
        $from = '{qtracker_issue} qi';
        $from .= "\nJOIN {question} q ON q.id = qi.questionid";
        $from .= "\nJOIN {context} ctx ON qi.contextid = ctx.id";
        $where = "\nctx.id $insql";
        $where .= "\nGROUP BY q.id";
        $params = $inarams; // TODO: find a way to only get the correct contexts.. For now just get everything (keep this empty)...

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


    /**
     * Not in use
     */
    public function wrap_html_start() {
        if ($this->is_downloading()) {
            return;
        }

        // echo '<div id="questions-table-wrapper" class="push-pane-over">';
        // echo '<div id="questions-table-wrapper">';
        // echo '<div id="questions-table-sidebar"></div>';
        // echo '<div class="border-bottom">';
        // echo '<div class="no-overflow">';
        // echo '<div class="questions-table">';

    }

    /**
     * Not in use
     */
    public function wrap_html_finish() {
        global $PAGE;
        if ($this->is_downloading()) {
            return;
        }

        // echo '</div>';
        // echo '</div>';
        // echo '</div>';
        // echo '</div>';
    }
}
