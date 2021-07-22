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
 * Issue reference class
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
 * QTracker reference class.
 *
 * @package    local_qtracker
 * @copyright  2021 André Storhaug
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class reference {

    /**
     * @var \stdClass
     */
    protected $reference = null;

    /**
     * Constructor.
     *
     * @param int|\stdClass $reference
     * @return void
     */
    public function __construct($reference) {
        global $DB;
        if (is_scalar($reference)) {
            $reference = $DB->get_record('local_qtracker_reference', array('id' => $reference), '*', MUST_EXIST);
            if (!$reference) {
                throw new \moodle_exception('errorunexistingmodel', 'analytics', '', $reference);
            }
        }
        $this->reference = $reference;
    }

    /**
     * Returns the reference id.
     *
     * @return int
     */
    public function get_id() {
        return $this->reference->id;
    }

    /**
     * Returns the related source id.
     *
     * @return int
     */
    public function get_source_id() {
        return $this->reference->sourceid;
    }

    /**
     * Returns the related target id.
     *
     * @return int
     */
    public function get_target_id() {
        return $this->reference->targetid;
    }

    /**
     * Returns the reference type.
     *
     * @return string
     */
    public function get_reftype() {
        return $this->reference->reftype;
    }

    /**
     * Returns a plain \stdClass with the reference data.
     *
     * @return \stdClass
     */
    public function get_reference_obj() {
        return $this->reference;
    }

    /**
     * Loads and returns reference with id $reference
     *
     * @param int $reference
     *
     * @return reference
     */
    public static function load(int $reference) {
        global $DB;
        $referenceobj = $DB->get_record('local_qtracker_reference', ['id' => $reference]);
        if ($referenceobj === false) {
            return null;
        }
        return new reference($referenceobj);
    }

    /**
     * Creates a new reference.
     *
     * @param int $sourceid reference id
     * @param int $targetid reference id
     * @param string $reftype
     *
     * @return reference
     */
    public static function create(int $sourceid, int $targetid, string $reftype) {
        global $USER, $DB;

        $referenceobj = new \stdClass();
        $referenceobj->sourceid = $sourceid;
        $referenceobj->targetid = $targetid;
        if (is_reference_type($reftype)) {
            $referenceobj->reftype = $reftype;
        } else {
            throw new coding_exception('Not a valid reference type ' . $reftype);
        }
        $id = $DB->insert_record('local_qtracker_reference', $referenceobj);
        $referenceobj->id = $id;

        $reference = new reference($referenceobj);
        return $reference;
    }

    /**
     * Delete this reference.
     *
     * @return void
     */
    public function delete() {
        global $DB;
        return $DB->delete_records('local_qtracker_reference', array('id' => $this->get_id()));
    }

    /**
     * Sets the reference type of this reference.
     *
     * @param string $type
     * @throws \coding_exception
     * @return void
     */
    public function set_reftype($type) {
        global $DB;
        if (is_reference_type($type)) {
            $this->reference->reftype = $type;
            $DB->update_record('local_qtracker_reference', $this->reference);
        } else {
            throw new coding_exception('Not a valid reference type ' . $type);
        }
    }
}
