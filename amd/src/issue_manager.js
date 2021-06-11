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
 * @module     local_qtracker/IssueManager
 * @class      IssueManager
 * @package    local_qtracker
 * @author     André Storhaug <andr3.storhaug@gmail.com>
 * @copyright  2020 NTNU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', 'local_qtracker/issue'], function($, Issue) {

    /**
     * Constructor
     * @constructor
     * @param {String} selector used to find triggers for the new group modal.
     * @param {int} contextid
     *
     * Each call to init gets it's own instance of this class.
     */
    var IssueManager = function() {};

    /**
     * @var {Form} form
     * @private
     */
    IssueManager.prototype.issues = new Map();

    IssueManager.prototype.activeIssue = null;

    IssueManager.prototype.getActiveIssue = function() {
        return this.activeIssue;
    };

    IssueManager.prototype.setActiveIssue = function(slot) {
        let newIssue = this.getIssueBySlot(slot);
        this.activeIssue = newIssue;
        return newIssue;
    };

    /**
     * This triggers a form submission, so that any mform elements can do final tricks before the form submission is processed.
     *
     * @method submitForm
     * @param slot
     * @private
     * @return
     */
    IssueManager.prototype.getIssueBySlot = function(slot) {
        return this.issues.get(slot);
    };

    IssueManager.prototype.getIssueById = function(id) {
        for (const [slot, issue] of this.issues) {
            if (issue.getId() !== null && issue.getId() === id) {
                return issue;
            }
        }
        return false;
    };

    IssueManager.prototype.addIssue = function(issue) {
        this.issues.set(issue.getSlot(), issue);
    };

    IssueManager.prototype.loadIssues = function(issueids = []) {
        let promises = [];
        for (let i = 0; i < issueids.length; i++) {
            const id = issueids[i];
            let promise = Issue.load(id).then((response) => {
                let issue = this.getIssueBySlot(response.issue.slot);
                if (!issue) {
                    issue = new Issue(response.issue.id, response.issue.slot);
                }
                issue.setId(response.issue.id);
                issue.setTitle(response.issue.title);
                issue.setDescription(response.issue.description);
                issue.isSaved = true;// ChangeState(Issue.STATES.EXISTING);
                this.addIssue(issue);
            });
            promises.push(promise);
        }
        return Promise.all(promises);
    };

    return IssueManager;
});
