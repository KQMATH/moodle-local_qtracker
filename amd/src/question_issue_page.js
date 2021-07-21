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
 * Class for handling question issue page.
 *
 * @module     local_qtracker/QuestionIssuePage
 * @class      QuestionIssuePage
 * @package    local_qtracker
 * @author     André Storhaug <andr3.storhaug@gmail.com>
 * @copyright  2021 NTNU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import $ from 'jquery';
import Templates from 'core/templates';
import Ajax from 'core/ajax';
import url from 'core/url';
import * as Str from 'core/str';
import Sidebar from 'local_qtracker/sidebar';
import Dropdown from 'local_qtracker/dropdown';
import Issue from 'local_qtracker/issue';
import ModalFactory from 'core/modal_factory';
import ModalEvents from 'core/modal_events';
import DropdownEvents from 'local_qtracker/dropdown_events';
import { loadIssuesData } from 'local_qtracker/api_helpers';

var SELECTORS = {
    TITLE: '[data-region="issuetitle"]',
    TITLE_TEXT: '[data-region="issuetitle-text"]',
    TITLE_INPUT: '[data-region="issuetitle-input"]',
};

/**
 * Constructor
 * @constructor
 * @param {String} courseid
 * @param {int} questionid
 * @param {int} issueid
 *
 * Each call gets it's own instance of this class.
 */
class QuestionIssuePage {
    courseid = null;
    questionid = null;
    issueid = null;
    issue = null;
    parents = [];
    filter = new Set(['Open', 'New'])

    constructor(courseid, questionid, issueid) {
        this.courseid = courseid;
        this.questionid = questionid;
        this.issueid = parseInt(issueid);
        this.issue = null;
        this.loadSettings()

        this.editingTitle = false;
        this.init();
    }

    async init() {
        this.checkForParents()
        this.initSidebar()
        this.initDropdowns();
        this.registerEditTitleButtonListener()
    }

    async checkForParents() {

        let parentsData = await this.loadIssueParents(this.issueid);
        if (parentsData.parents.length > 0) {
            this.parents = parentsData.parents;
            let supersededids = this.parents.map((parent) => {
                return $('<a></a>')
                    .attr("href", this._getIssueUrl(parent.id))
                    .html("#" + parent.id).prop('outerHTML');
            }).join(", ");
            this.notify({
                message: await Str.get_string('issuesuperseded', 'local_qtracker', supersededids),
                announce: false,
                type: "warning",
            }, '#qtracker-superseded');
        }
    }

    loadSettings() {
        let filterData = JSON.parse(sessionStorage.getItem('local_qtracker_issue_page_filter'))
        if (filterData !== null) {
            filterData.forEach(this.filter.add, this.filter)
        }
    }

    saveSettings() {
        let filterData = Array.from(this.filter);
        sessionStorage.setItem('local_qtracker_issue_page_filter', JSON.stringify(filterData))
    }


    async loadChildren() {
        let childrenData = await this.loadIssueChildren(this.issueid);
        let children = childrenData.children;
        let items = children.map((item) => [item.id, item.title]);
        return items;
    }

    async getIssue() {
        if (this.issue === null) {
            this.issue = await Issue.load(this.issueid);
        }
        return this.issue;
    }

    registerEditTitleButtonListener() {
        $(".edittitle").children("button").on('click', async function (e) {
            let button = $(e.target);
            if (!this.editingTitle) {
                button.html(await Str.get_string('save', 'core'));
                $(SELECTORS.TITLE_INPUT).show();
                $(SELECTORS.TITLE_TEXT).parent("div").hide();
                this.editingTitle = true;
            } else {
                let title = $(SELECTORS.TITLE_INPUT).val();
                let issue = await this.getIssue();
                issue.setTitle(title);
                let response = await issue.save();
                if (response.status) {
                    button.html(await Str.get_string('edit', 'core'));
                    $(SELECTORS.TITLE_INPUT).hide();
                    $(SELECTORS.TITLE_TEXT).parent("div").show();
                    $(SELECTORS.TITLE_TEXT).text(title);
                    this.editingTitle = false;
                }
            }
        }.bind(this));
    }

    // aside blocks
    async initDropdowns() {
        let issuesDropdown = new Dropdown('#linkedissues-dropdown');
        issuesDropdown.setItems(await this.loadChildren(), true);
        this.updateIssueAsideBlock(issuesDropdown.getActiveItems());

        // TODO: Remove the right margin of the content container when the grading panel is hidden so that it expands to full-width.
        let dropdown = issuesDropdown;
        dropdown.getRoot().on(DropdownEvents.search, async function (e, str) {
            let criteria = [];
            if (str.startsWith('#')) {
                let id = parseInt(str.substr(1));
                criteria.push({ key: 'id', value: id });
            } else {
                if (str.length > 2) str += "%"
                criteria.push({ key: 'title', value: str });
            }
            let issuesResponse = await loadIssuesData(criteria);
            let issues = issuesResponse.issues;
            let items = issues.map((item) => {
                let html = item.title + ' <span class="text-muted"> #' + item.id + '</span>';
                return [item.id, html];
            });
            issuesDropdown.setItems(items);
            issuesDropdown.renderItems();
        }.bind(this));


        dropdown.getRoot().on(DropdownEvents.click, async function (e, element) {
            let parentid = parseInt(this.issueid);
            let childid = parseInt(element.attr("data-value"));
            let active = dropdown.isActiveItem(childid);
            if (active) {
                let response = await this.deleteIssueRelation(parentid, childid);
                if (response.status) {
                    this.renderSidebarContent();//TODO: update sidebar issues if exists with new status.
                    issuesDropdown.setItemStatus(childid, !active)
                    issuesDropdown.reset();
                    this.updateIssueAsideBlock(issuesDropdown.getActiveItems())
                }
            } else {
                let modal = await ModalFactory.create({
                    title: await Str.get_string('subsumeissue', 'local_qtracker'),
                    body: await Str.get_string('subsumeissueconfirm', 'local_qtracker', { child: childid, parent: parentid }),
                    type: ModalFactory.types.SAVE_CANCEL,
                    large: false,
                })
                modal.setSaveButtonText(await Str.get_string('confirm', 'core'));
                modal.getRoot().on(ModalEvents.save, async e => {
                    // Don't close the modal yet.
                    e.preventDefault();
                    // Submit form data.
                    let response = await this.setIssueRelation(parentid, childid);
                    if (response.status) {
                        this.renderSidebarContent();//TODO: update sidebar issues if exists with new status.
                        issuesDropdown.setItemStatus(childid, !active)
                        issuesDropdown.reset();
                        this.updateIssueAsideBlock(issuesDropdown.getActiveItems())
                    } else {
                        let issueurl = $('<a></a>')
                            .attr("href", this._getIssueUrl(childid))
                            .html("#" + childid).prop('outerHTML');

                        this.notify({
                            message: await Str.get_string('errorsubsumingissue', 'local_qtracker', issueurl),
                            announce: true,
                            closebutton: true,
                            type: "error",
                        }, '#qtracker-notifications');
                    }
                    modal.destroy();

                    //submitEditFormAjax(link, getBody, modal, userEnrolmentId, container.dataset);
                });
                // Handle hidden event.
                modal.getRoot().on(ModalEvents.hidden, () => {
                    // Destroy when hidden.
                    modal.destroy();
                });
                // Show the modal.
                modal.show();
            }
        }.bind(this))

        issuesDropdown.renderItems();
    }

    async updateIssueAsideBlock(items) {
        let elements = []

        if (items.size === 0) {
            let element = $('<div></div>')
                .addClass("dropdown-item disabled")
                .html(await Str.get_string('noitems', 'local_qtracker'))
                .prop('outerHTML');
            elements.push(element)
        }

        items.forEach((html, key) => {
            let element = $('<a></a>')
                .addClass("list-item border-0 p-1")
                .attr("href", this._getIssueUrl(key))
                .html(html).prop('outerHTML');
            //<div class="list-group-item border-0 {{state}}">{{{text}}}</div>
            elements.push(element);
        });
        $(".linkedissues-list").html(elements);
    }

    _getIssueUrl(issueid) {
        let issueurl = url.relativeUrl('/local/qtracker/issue.php', {
            courseid: this.courseid,
            issueid: issueid,
        });
        return issueurl;
    }

    async renderSidebarContent() {
        let state = null;
        this.sidebar.setLoading(true);
        this.sidebar.empty();

        // Get issues data.
        let issuesResponse = await this.loadIssues(this.questionid, state);
        let issues = issuesResponse.issues;

        // Get users data.
        let userids = [...new Set(issues.map(issue => issue.userid))];
        let usersData = await this.loadUsersData(userids);

        // Render issue items.
        let promises = [];
        issues.forEach(async issueData => {
            let userData = usersData.find(({ id }) => id === issueData.userid);
            if (issueData.id == this.issueid) {
                return;
            }
            promises.push(this.addIssueItem(issueData, userData));
        });

        self = this;
        // When all issue item promises are resolved.
        $.when.apply($, promises).done(function () {
            self.sidebar.setLoading(false);
            $.each(arguments, (index, argument) => {
                self.sidebar.addTemplateItem(argument.html, argument.js);
            });
            self.applyFilter();
        }).catch(e => {
            console.error(e);
        });
    }

    async initSidebar() {
        let active = this.filter.has("Closed");
        let sidebarOptions = [{ "name": "toggleclosed", "text": "Show closed issues", "value": 0, "checkbox": true, "active": active }];
        this.sidebar = new Sidebar('#question-issues-sidebar', true, "left", false, '30%', '1.25rem', false, sidebarOptions);

        await this.sidebar.render();

        this.sidebar.empty();
        this.sidebar.setLoading(true);

        // Get question title.
        let questionData = await this.loadQuestionData(this.questionid);
        let question = questionData.question;
        let questionEditUrl = this.getQuestionEditUrl(this.courseid, this.questionid);
        let link = $('<a></a>').attr("href", questionEditUrl).html(question.name + " #" + question.id);
        this.sidebar.setTitle(link);
        this.sidebar.show();

        await this.renderSidebarContent();

        // Add logic to sidebar actions (dropdowns)
        this.sidebar.getContainer().on('click', async function (e) {
            let element = $(e.target);
            if (element.hasClass("dropdown-item")) {
                let dropdownItem = element.attr("data-name");
                let itemValue = parseInt(element.attr("data-value"));
                switch (dropdownItem) {
                    case "toggleclosed": // Sidebar toolbar filter
                        if (element.is(':checked')) {
                            this.filter.add('Closed');
                            element.prop('checked', true);
                        } else {
                            this.filter.delete('Closed');
                            element.prop('checked', false);
                        }
                        this.applyFilter();
                        this.saveSettings();
                        break;
                    case "subsume": // Sidebar item action
                        //TODO: add issue menu to the sidebar items
                        let parentid = this.issueid;
                        let childid = itemValue;
                        let response = await this.setIssueRelation(parentid, childid);
                        if (response.status) {
                            this.renderSidebarContent()
                        }
                        break;
                    default:
                        break;
                }
            }
        }.bind(this))

        window.closeIssuesPane = function () { this.sidebar.hide() }.bind(this);
        window.toggleIssuesPane = function () { this.sidebar.togglePane() }.bind(this);
    }

    applyFilter() {
        this.resetFilter();
        let self = this;
        this.sidebar.getItems().each(function () {
            if (!self.filter.has($(this).find(".badge").text())) {
                $(this).hide();
            }
        });
    }

    resetFilter() {
        this.sidebar.getItems().each(function () {
            $(this).show();
        });
    }

    /**
     *
     * @param {object} issueData
     * @param {object} userData
     * @return {Promise}
     */
    async addIssueItem(issueData, userData, extraClasses = "") {
        // Fetch user data.
        let issueurl = url.relativeUrl('/local/qtracker/issue.php', {
            courseid: this.courseid,
            issueid: issueData.id,
        });
        let userurl = url.relativeUrl('/user/view.php', {
            course: this.courseid,
            id: userData.id,
        });

        let actions = {
            "trigger": {
                "key": "fa-ellipsis-h",
                "title": "Options",
                "alt": "Show options",
                "extraclasses": "",
                "unmappedIcon": false
            },
            "header": false,
            "items": [
                { "name": "subsume", "text": "Subsume", "value": issueData.id },
            ]
        }
        // Render issues pane
        let paneContext = {
            issueurl: issueurl,
            userurl: userurl,
            profileimageurl: userData.profileimageurlsmall,
            fullname: userData.fullname,
            timecreated: issueData.timecreated,
            id: issueData.id,
            title: issueData.title,
            description: issueData.description,
            extraclasses: extraClasses
            //actions: actions // TODO: finish this
        };
        let state = issueData.state;
        paneContext[state] = true;

        return Templates.render('local_qtracker/sidebar_item_issue', paneContext)
            .then(function (html, js) {
                return { html: html, js: js };
            });
    }

    async loadIssues(id, state = null) {
        let criteria = [
            { key: 'questionid', value: id },
        ];
        if (state) {
            criteria.push({ key: 'state', value: state });
        }
        let issuesData = await Ajax.call([{
            methodname: 'local_qtracker_get_issues',
            args: { criteria: criteria }
        }])[0];

        return issuesData;
    }

    async loadUsersData(ids) {
        let usersData = await Ajax.call([{
            methodname: 'core_user_get_users_by_field',
            args: {
                field: 'id',
                values: ids
            }
        }])[0];
        return usersData;
    }

    async setIssueRelation(parentid, childid) {
        let result = await Ajax.call([{
            methodname: 'local_qtracker_set_issue_relation',
            args: {
                parentid: parentid,
                childid: childid,
            }
        }])[0];
        return result
    }


    async deleteIssueRelation(parentid, childid) {
        let result = await Ajax.call([{
            methodname: 'local_qtracker_delete_issue_relation',
            args: {
                parentid: parentid,
                childid: childid,
            }
        }])[0];
        return result
    }

    getQuestionEditUrl(courseid, questionid) {
        let returnurl = encodeURIComponent(location.pathname + location.search);
        let editurl = url.relativeUrl('/question/question.php', {
            courseid: courseid,
            id: questionid,
            returnurl: returnurl,
        });
        return editurl;
    }

    decodeHTML(html) {
        var doc = new DOMParser().parseFromString(html, "text/html");
        return doc.documentElement.textContent;
    }

    async loadQuestionData(id) {
        let userData = await Ajax.call([{
            methodname: 'local_qtracker_get_question',
            args: {
                id: id
            }
        }])[0];
        return userData;
    }

    async loadIssueParents(id) {
        let userData;
        userData = await Ajax.call([{
            methodname: 'local_qtracker_get_issue_parents',
            args: {
                issueid: id
            }
        }])[0];
        return userData;
    }

    async loadIssueChildren(id) {
        let userData;
        userData = await Ajax.call([{
            methodname: 'local_qtracker_get_issue_children',
            args: {
                issueid: id
            }
        }])[0];
        return userData;
    }

    notify(notification, selector = null) {
        notification = $.extend({
            closebutton: false,
            announce: false,
            type: 'error',
            extraclasses: "show",
        }, notification);

        let types = {
            'success': 'core/notification_success',
            'info': 'core/notification_info',
            'warning': 'core/notification_warning',
            'error': 'core/notification_error',
        };

        let template = types[notification.type];
        Templates.render(template, notification)
            .then((html, js) => {
                if (selector === null) {
                    $('#qtracker-notifications').append(html);
                } else {
                    $(selector).append(html);
                }
                Templates.runTemplateJS(js);
            })
            .catch((error) => {
                console.error(error);
                throw error;
            });
    };
}

export default QuestionIssuePage;
