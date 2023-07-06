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
 * Issue class
 *
 * @package    local_qtracker
 * @author     André Storhaug <andr3.storhaug@gmail.com>
 * @copyright  2020 NTNU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_qtracker;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/qtracker/lib.php');

use local_qtracker\referable_interface;

/**
 * Question issue class.
 *
 * @package    local_qtracker
 * @copyright  2020 André Storhaug
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class reference_manager {

    /**
     * @var referable
     */
    protected $referable = null;

    /**
     * @var \array
     */
    protected $incomingrefs = array();

    /**
     * @var \array
     */
    protected $outgoingrefs = array();

    /**
     * Constructs with item details.
     *
     * @param int $userid Userid for modinfo (if used)
     * @param \cm_info $cm Course-module object
     */
    public function __construct(referable $referable) {
        $this->referable = $referable;
    }

    /**
     * Get all incomming references to this issue.
     *
     * @return \stdClass
     */
    public function get_incoming_references() {
        global $DB;
        if (empty($this->incomingrefs)) {
            $this->incomingrefs = array();
            $incomingrefs = $DB->get_records('local_qtracker_reference', ['targetid' => $this->referable->get_id()]);
            foreach ($incomingrefs as $incomingref) {
                array_push($this->incomingrefs, new reference($incomingref));
            }
        }
        return $this->incomingrefs;
    }

    /**
     * Get all outcoing references from this issue.
     *
     * @return \stdClass
     */
    public function get_outgoing_references() {
        global $DB;
        if (empty($this->otugoingrefs)) {
            $this->otugoingrefs = array();
            $otugoingrefs = $DB->get_records('local_qtracker_reference', ['sourceid' => $this->get_id()]);
            foreach ($otugoingrefs as $otugoingref) {
                array_push($this->otugoingrefs, new reference($otugoingref));
            }
        }
        return $this->otugoingrefs;
    }

    /**
     * Filter references by type
     *
     * @return array
     */
    public static function filter_references_by_type(array $references, string $type) {
        $filteredrefs = array();
        foreach ($references as $reference) {
            if ($reference->get_reftype() == $type) {
                array_push($filteredrefs, $reference);
            }
        }
        return $filteredrefs;
    }
}
