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
    public function __construct($uniqueid, $url) {
        global $CFG;
        parent::__construct($uniqueid);

        // TODO: determine which context to use...
        $context = context_system::instance();
        $this->context = $context;

        // This object should not be used without the right permissions.
        require_capability('moodle/role:manage', $context); // DO WE NEED THIS?

        // Define columns in the table.
        $this->define_table_columns();
        // Set the baseurl
        $this->define_baseurl($url);
        // Define configs.
        $this->define_table_configs();
        // Define SQL.
        $this->setup_sql_queries();
    }

    /**
     * Something name column.
     *
     * @param object $data Row data.
     * @return string
     */
    protected function col_something($data) {
        // TODO: implement one of these for questionid, title, description, and user.
        return $data->something;
    }

    /**
     * The timecreated column.
     *
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
        //TODO: define strings in lang file.
        $cols = array(
            'questionid' => get_string('questionid', 'local_qtracker'),
            'title' => get_string('issuetitle', 'local_qtracker'),
            'description' => get_string('issuedescription', 'local_qtracker'),
            'datecreated' => get_string('datecreated', 'local_qtracker'),
        );

        // Add remaining headers.
        $cols = array_merge($cols, array('actions' => get_string('actions')));

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

        // TODO: Write SQL to retrieve all rows...
        $fields = '';
        $from = '';
        $where = '';
        $params = array(); // TODO: find a way to only get the correct contexts.. For now just get everything (keep this empty)...

        $this->set_sql($fields, $from, $where, $params);
    }
}
