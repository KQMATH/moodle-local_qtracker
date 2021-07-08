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
 * Renderer
 *
 * @package    local_qtracker
 * @author     André Storhaug <andr3.storhaug@gmail.com>
 * @copyright  2020 NTNU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_qtracker\output;

defined('MOODLE_INTERNAL') || die();

use plugin_renderer_base;
use templatable;

/**
 * Question Tracker renderer.
 *
 * @package    local_qtracker
 * @copyright  2020 André Storhaug
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends plugin_renderer_base {

    /**
     * Render the review page for the deletion of expired contexts.
     *
     * @param question_issues_page $page
     * @return string html for the page
     * @throws moodle_exception
     */
    public function render_question_issues_page(question_issues_page $page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('local_qtracker/question_issues_page', $data);
    }

    /**
     * Render the review page for the deletion of expired contexts.
     *
     * @param questions_page $page
     * @return string html for the page
     * @throws moodle_exception
     */
    public function render_questions_page(questions_page $page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('local_qtracker/questions_page', $data);
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
        $data = $block->export_for_template($this);
        $content .= $this->render_from_template('local_qtracker/issue_registration_block', $data);
        return $content;
    }
}
