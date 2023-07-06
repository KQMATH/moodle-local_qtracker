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
 * Manager for managing question issues.
 *
 * @module     local_qtracker/IssueView
 * @class      IssueView
 * @package    local_qtracker
 * @author     David Rise Knotten <david_knotten@hotmail.no>
 * @copyright  2021 NTNU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import $ from 'jquery';

class IssueView {
    constructor(closeissuestring, commentandcloseissuestring) {
        const commentandcommitbtn = $("button[name=closeissue]").get(0);
        const commentEditor = $("#commenteditor").get(0);
        commentEditor.addEventListener("change", event => {
            if (commentEditor.value == ('<p dir="ltr" style="text-align: left;"><br></p>')) {
                commentandcommitbtn.innerText = closeissuestring;
            } else {
                commentandcommitbtn.innerText = commentandcloseissuestring;
            }
        })
    }
}

export default IssueView;
