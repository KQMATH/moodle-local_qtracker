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

namespace local_qtracker\output;

use Error;
use moodle_url;
use plugin_renderer_base;
use templatable;

defined('MOODLE_INTERNAL') || die();

/**
 * @package     local_qtracker
 * @author      Aleksander Skrede <aleksander.l.skrede@ntnu.no>
 * @author      Sebastian S. Gundersen <sebastian@sgundersen.com>
 * @copyright   2019 NTNU
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends plugin_renderer_base
{

    public function render_issue_table(templatable $block) {

    }

    /**
     * Renders a block.
     *
     * @param templatable $block Renderable of block content.
     * @return string
     * @throws \moodle_exception
     */
    public function render_block(templatable $block) {

        $content = '';
        //$editor = \editors_get_preferred_editor(); // This gets the default editor for your site
        //$editor->use_editor("someidhere"); // This is used to set the id of the html element
        // This creates the html element
        //$content.= \html_writer::tag('textarea', 'somedefaultvalue',
        //array('id' => "someidhere", 'name' => 'somenamehere', 'rows' => 5, 'cols' => 10));
        /*$formdata = $form->get_data();
        if ($formdata) {
            $this->process_rating_configuration($formdata);
        }
        */

        $content.= \html_writer::tag('div', 'oYESSSSSSkokok');
        $data = $block->export_for_template($this);

        $content .= $this->render_from_template('local_qtracker/issue_registration_block', $data);
        return $content;
    }
}
