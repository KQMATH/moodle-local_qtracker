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
 * Exporter for exporting question issue data.
 *
 * @package    local_qtracker
 * @author     André Storhaug <andr3.storhaug@gmail.com>
 * @copyright  2020 NTNU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_qtracker\external;

defined('MOODLE_INTERNAL') || die();

use \core\external\exporter;
use \renderer_base;

/**
 * Class for displaying a list of issue comment data.
 *
 * @package    local_qtracker
 * @copyright  2020 André Storhaug
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class issue_comment_exporter extends exporter {


    /**
     * Return the list of additional properties.
     *
     * @return array
     */
    protected static function define_other_properties() {
        return [
            'id' => [
                'type' => PARAM_INT,
            ],
            'description' => [
                'type' => PARAM_RAW,
            ],
            'issueid' => [
                'type' => PARAM_INT,
            ],
            'userid' => [
                'type' => PARAM_INT,
            ],
            'timecreated' => [
                'type' => PARAM_INT,
            ],
        ];
    }

    protected static function define_related() {
        return array(
            'context' => 'context',
        );
    }
}
