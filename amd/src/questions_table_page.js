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
 * Manager for managing table of questions with issues.
 *
 * @module     local_qtracker/IssueManager
 * @class      IssueManager
 * @package    local_qtracker
 * @author     André Storhaug <andr3.storhaug@gmail.com>
 * @copyright  2020 NTNU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import $ from 'jquery';
import Templates from 'core/templates';
import Ajax from 'core/ajax';
import url from 'core/url';
import Sidebar from 'local_qtracker/sidebar';
/**
 * Constructor
 * @constructor
 * @param {String} selector used to find triggers for the new group modal.
 * @param {int} contextid
 *
 * Each call to init gets it's own instance of this class.
 */
class QuestionsTablePage {
    courseid = null;

    constructor(courseid) {
        this.courseid = courseid;
        this.sidebar = new Sidebar('#questions-table-sidebar', false, "right", false, '40%');
        this.init();
    }

    async init() {

        await this.sidebar.render();
        window.showIssuesInPane = async function(id, state = null) {
            this.sidebar.empty();
            this.sidebar.setLoading(true);

            // Get question title.
            let questionData = await this.loadQuestionData(id);
            let question = questionData.question;
            let questionEditUrl = this.getQuestionEditUrl(this.courseid, id);
            let link = $('<a></a>').attr("href", questionEditUrl).html(question.name + " #" + question.id);
            this.sidebar.setTitle(link);
            this.sidebar.show();

            // Get issues data.
            let issuesResponse = await this.loadIssues(id, state);
            let issues = issuesResponse.issues;

            // Get users data.
            let userids = [...new Set(issues.map(issue => issue.userid))];
            let usersData = await this.loadUsersData(userids);

            // Render issue items.
            let promises = [];
            issues.forEach(async issueData => {
                let userData = usersData.find(({id}) => id === issueData.userid);
                promises.push(this.addIssueItem(issueData, userData));
            });

            self = this;
            // When all issue item promises are resolved.
            $.when.apply($, promises).done(function() {
                self.sidebar.setLoading(false);
                $.each(arguments, (index, argument) => {
                    self.sidebar.addTemplateItem(argument.html, argument.js);
                });
            }).catch(e => {
                console.error(e);
            });

        }.bind(this);

        window.closeIssuesPane = function() {this.sidebar.hide()}.bind(this);
        window.toggleIssuesPane = function() {this.sidebar.togglePane()}.bind(this);

    }

    /**
     *
     * @param {object} issueData
     * @param {object} userData
     * @return {Promise}
     */
    async addIssueItem(issueData, userData) {
        // Fetch user data.
        let issueurl = url.relativeUrl('/local/qtracker/issue.php', {
            courseid: this.courseid,
            issueid: issueData.id,
        });
        let userurl = url.relativeUrl('/user/view.php', {
            course: this.courseid,
            id: userData.id,
        });

        // Render issues pane
        let paneContext = {
            issueurl: issueurl,
            userurl: userurl,
            profileimageurl: userData.profileimageurlsmall,
            fullname: userData.fullname,
            timecreated: issueData.timecreated,
            title: issueData.title,
            description: issueData.description,
        };
        let state = issueData.state;
        paneContext[state] = true;

        return Templates.render('local_qtracker/sidebar_item_issue', paneContext)
            .then(function(html, js) {
                return {html: html, js: js};
            });
    }

    async loadIssues(id, state = null) {
        let criteria = [
            {key: 'questionid', value: id},
        ];
        if (state) {
            criteria.push({key: 'state', value: state});
        }
        let issuesData = await Ajax.call([{
            methodname: 'local_qtracker_get_issues',
            args: {criteria: criteria}
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
}

export default QuestionsTablePage;
