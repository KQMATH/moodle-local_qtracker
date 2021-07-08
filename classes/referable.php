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
 * Referable class
 *
 * @package    local_qtracker
 * @author     André Storhaug <andr3.storhaug@gmail.com>
 * @copyright  2021 NTNU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_qtracker;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/qtracker/lib.php');

/**
 * Referable class.
 *
 * @package    local_qtracker
 * @copyright  2021 André Storhaug
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class referable {
    /**
     * @var array
     */
    protected $incomingrefs = array();

    /**
     * @var array
     */
    protected $outgoingrefs = array();

    /**
     * Get this referable's id.
     */
    abstract public function get_id();

    /**
     * Create a new outgoing reference.
     * The source is $this referable.
     * @param referable $target The target referable to create an connection to
     * @param string $type The reference type to create
     */
    public function make_outgoing_reference(referable $target, string $type) {
        $reference = reference::create($this->get_id(), $target->get_id(), $type);
        array_push($this->outgoingrefs, $reference);
    }

    /**
     * Create a new ingoing reference.
     * The target is $this referable.
     * @param referable $source The source referable to make an reference from
     * @param string $type The reference type to create
     */
    public function make_incoming_reference(referable $source, string $type) {
        $reference = reference::create($source->get_id(), $this->get_id(), $type);
        array_push($this->outgoingrefs, $reference);
    }

    /**
     * Get all outcoing references from this referable.
     *
     * @return array
     */
    public function get_outgoing_references() {
        global $DB;
        $this->otugoingrefs = array();
        $otugoingrefs = $DB->get_records('local_qtracker_reference', ['sourceid' => $this->get_id()]);
        foreach ($otugoingrefs as $otugoingref) {
            array_push($this->otugoingrefs, new reference($otugoingref));
        }
        return $this->otugoingrefs;
    }

    /**
     * Get all incomming references to this issue.
     *
     * @return array
     */
    public function get_incoming_references() {
        global $DB;
        $this->incomingrefs = array();
        $incomingrefs = $DB->get_records('local_qtracker_reference', ['targetid' => $this->get_id()]);
        foreach ($incomingrefs as $incomingref) {
            array_push($this->incomingrefs, new reference($incomingref));
        }
        return $this->incomingrefs;
    }
}
