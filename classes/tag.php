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


namespace local_qtracker;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/qtracker/lib.php');

use local_qtracker\referable;

/**
 * Issue tag class.
 *
 * @package    local_qtracker
 * @author     Jørgen Finsveen <joergen.finsveen@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tag extends referable {

    /**
     * @var \stdClass
     */
    protected $tag = null;


    /**
     * Constructor.
     *
     * @param int|\stdClass $tag
     * @return void
     */
    public function __construct($tag) {
        global $DB;
        if (is_scalar($tag)) {
            $tag = $DB->get_record('local_qtracker_tag', array('id' => $tag), '*', MUST_EXIST);
            if (!$tag) {
                throw new \moodle_exception('errorunexistingmodel', 'analytics', '', $tag);
            }
        }
        $this->tag = $tag;
    }


    /**
     * Returns the tag id.
     *
     * @return int
     */
    public function get_id() {
        return $this->tag->id;
    }


    /**
     * Returns the tag name.
     *
     * @return string
     */
    public function get_name() {
        return $this->tag->name;
    }


    /**
     * Returns the tag color.
     *
     * @return string
     */
    public function get_color() {
        return $this->tag->color;
    }


    /**
     * Returns a plain \stdClass with the tag data.
     *
     * @return \stdClass
     */
    public function get_tag_obj() {
        return $this->tag;
    }


    /**
     * Loads and returns tag with id $tagid
     *
     * @param int $tagid
     * @return tag|null
     */
    public static function load(int $tagid) {
        global $DB;
        $tagobj = $DB->get_record('local_qtracker_tag', ['id' => $tagid]);
        if ($tagobj === false) {
            return null;
        }
        return new tag($tagobj);
    }


    /**
     * Creates a new tag.
     *
     * @param string $name
     * @param string $color
     *
     * @return tag
     */
    public static function create($name, $color) {
        global $DB;

        $tagobj = new \stdClass();
        $tagobj->name = $name;
        $tagobj->color = $color;

        $id = $DB->insert_record('local_qtracker_tag', $tagobj);

        $tagobj->id = $id;

        return new tag($tagobj);
    }


    /**
     * Sets the name of the tag.
     *
     * @param string $name
     */
    public function set_name($name) {
        global $DB;
        $this->tag->name = $name;
        $DB->update_record('local_qtracker_tag', $this->tag);
    }


    /**
     * Sets the color of the tag.
     *
     * @param string $color
     */
    public function set_color($color) {
        global $DB;
        $this->tag->color = $color;
        $DB->update_record('local_qtracker_tag', $this->tag);
    }


    /**
     * Deletes the tag and all its references.
     *
     * @return void
     */
    public function delete() {
        global $DB;

        $outrefs = $this->get_outgoing_references();
        foreach ($outrefs as $outref) {
            $outref->delete();
        }
        $inrefs = $this->get_incoming_references();
        foreach ($inrefs as $inref) {
            $inref->delete();
        }

        return $DB->delete_records('local_qtracker_tag', array('id' => $this->get_id()));
    }
}
