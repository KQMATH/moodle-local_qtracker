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
 * Module for representing a question issue.
 *
 * @module     local_qtracker/Issue
 * @class      Issue
 * @package    local_qtracker
 * @author     André Storhaug <andr3.storhaug@gmail.com>
 * @copyright  2020 NTNU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', 'core/str', 'core/ajax'], function ($, Str, Ajax) {

    /**
     * Constructor
     *
     * @param {int} id
     * @param {int} slot
     * @param {int} contextid
     *
     * Each call to init gets it's own instance of this class.
     */
    var Issue = function (id = null, slot = null, contextid) {
        this.id = id;
        this.slot = slot;
        this.contextid = contextid;
    };

    Issue.STATES = {
        NEW: "new",
        OPEN: "open",
        CLOSED: "closed",
    };

    /**
     * @var {int} id The id of this issue
     * @private
     */
    Issue.prototype.id = null;

    /**
     * @var {int} id The slot for this issue
     * @private
     */
    Issue.prototype.slot = null;

    /**
     * @var {string} title The title for this issue
     * @private
     */
    Issue.prototype.title = "";

    /**
     * @var {string} title The description for this issue
     * @private
     */
    Issue.prototype.description = "";

    Issue.prototype.contextid = null;

    Issue.prototype.isSaved = false;

    Issue.prototype.state = Issue.STATES.NEW;

    /**
     * Initialise the class.
     *
     * @private
     * @param {int} id
     */
    Issue.prototype.setId = function (id) {
        this.id = id;
    };

    /**
     * Initialise the class.
     *
     * @param {String} selector used to find triggers for the new group modal.
     * @private
     * @return {Promise}
     */
    Issue.prototype.getId = function () {
        return this.id;
    };

    Issue.prototype.getSlot = function () {
        return this.slot;
    };

    Issue.prototype.getTitle = function () {
        return this.title;
    };

    Issue.prototype.setTitle = function (title) {
        this.title = title;
    };

    Issue.prototype.getDescription = function () {
        return this.description;
    };


    Issue.prototype.setDescription = function (description) {
        this.description = description;
    };

    Issue.prototype.changeState = function (state) {
        this.state = state;
    };

    Issue.prototype.getState = function () {
        return this.state;
    };

    Issue.prototype.getContextid = function () {
        return this.contextid;
    };

    /**
     * @return {Promise}
     * @param {int} id
     */
    Issue.loadData = function (id) {
        return Ajax.call([
            { methodname: 'local_qtracker_get_issue', args: { issueid: id } }
        ])[0];
    };

    Issue.load = async function (id) {
        let data = await Issue.loadData(id);
        let issueData = data.issue;
        let issue = new Issue(issueData.id, issueData.slot, issueData.contextid);
        issue.setTitle(issueData.title);
        issue.setDescription(issueData.description);
        return issue;
    }

    Issue.prototype.save = async function () {
        let result = await Ajax.call([{
            methodname: 'local_qtracker_edit_issue',
            args: {
                issueid: this.getId(),
                issuetitle: this.getTitle(),
                issuedescription: this.getDescription(),
            },
        }])[0];
        return result
    }

    return Issue;
});
