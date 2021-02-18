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
 * Manager for a Question Tracker Block form.
 *
 * @module     local_qtracker/BlockFormManager
 * @class      BlockFormManager
 * @package    local_qtracker
 * @author     André Storhaug <andr3.storhaug@gmail.com>
 * @copyright  2020 NTNU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', 'core/str', 'core/templates', 'core/ajax', 'local_qtracker/issue', 'local_qtracker/issue_manager'],
    function($, Str, Templates, Ajax, Issue, IssueManager) {
        var SELECTORS = {
            SLOT: '[name="slot"]',
            SLOT_SELECT_OPTION: '[name="slot"] option',
            TITLE: '[name="issuetitle"]',
            DESCRIPTION: '[name="issuedescription"]',
            SUBMIT_BUTTON: 'button[type="submit"]',
            DELETE_BUTTON: '#qtracker-delete',
        };

        let VALIDATION_ELEMENTS = [
            SELECTORS.TITLE,
            SELECTORS.DESCRIPTION,
        ];

        var NOTIFICATION_DURATION = 7500;
        var notificationTimeoutHandle = null;

        /**
         * Constructor
         *
         * @param {String} selector used to find triggers for the new group modal.
         * @param {string} issueids
         * @param {int} contextid
         *
         * Each call to init gets it's own instance of this class.
         */
        var BlockFormManager = function(selector, issueids, contextid) {
            this.contextid = contextid;
            this.form = $(selector);
            this.form.closest('.card-text').prepend('<span class="notifications" id="qtracker-notifications"></span>');
            this.issueManager = new IssueManager();
            this.init(JSON.parse(issueids));
        };

        /**
         * @var {Form} form
         * @private
         */
        BlockFormManager.prototype.form = null;

        /**
         * @var {int} contextid
         * @private
         */
        BlockFormManager.prototype.contextid = -1;

        /**
         * @var {int} issueid
         * @private
         */
        BlockFormManager.prototype.issueid = null;

        /**
         * @var {issue[]} issues
         * @private
         */
        BlockFormManager.prototype.issues = [];

        /**
         * @var {issue_manager} issueManager
         * @private
         */
        BlockFormManager.prototype.issueManager = null;

        /**
         * Initialise the class.
         *
         * @param {*[]} issueids selector used to find triggers for the new question issue.
         * @private
         */
        BlockFormManager.prototype.init = function(issueids = []) {
            // Init all slots
            let slots = $(SELECTORS.SLOT_SELECT_OPTION);
            if (slots.length == 0) {
                slots = $(SELECTORS.SLOT);
            }
            slots.map((index, option) => {
                let issue = new Issue(null, parseInt(option.value), this.contextid);
                issue.isSaved = false;// ChangeState(Issue.STATES.NEW);
                this.issueManager.addIssue(issue);
            });


            this.issueManager.loadIssues(issueids).then(() => {

                var formData = new FormData(this.form[0]);
                this.issueManager.setActiveIssue(parseInt(formData.get('slot')));

                this.reflectFormState();

                // Issue title event listener.
                let titleElement = this.form.find(SELECTORS.TITLE);
                titleElement.change((event) => {
                    this.issueManager.getActiveIssue().setTitle(event.target.value);
                });
                /* $(document).on(qtrackerEvents.CHANGED_SLOT_BLOCK_FORM, (event, value) => {
                    titleElement.val(value);
                }); */

                // Issue description event listener.
                let descriptionElement = this.form.find(SELECTORS.DESCRIPTION);
                descriptionElement.change((event) => {
                    this.issueManager.getActiveIssue().setDescription(event.target.value);
                });
                /* $(document).on(qtrackerEvents.CHANGED_SLOT_BLOCK_FORM, (event, value) => {
                    descriptionElement.val(value)
                }); */

                //

                // Load existing issues.
                var slotElement = this.form.find(SELECTORS.SLOT);
                slotElement.change(this.handleSlotChange.bind(this));

                this.form.on('submit', this.submitFormAjax.bind(this));

            }).catch((error) => {
                console.error(error);
            });
        };

        BlockFormManager.prototype.handleSlotChange = function(e) {
            this.issueManager.setActiveIssue(parseInt(e.target.value));
            this.reflectFormState();
            this.resetValidation();
        };

        BlockFormManager.prototype.reflectFormState = function() {
            let issue = this.issueManager.getActiveIssue();
            if (issue.isSaved === true) { // State === Issue.STATES.EXISTING) {
                this.toggleDeleteButton(true);
                this.toggleUpdateButton(true);
            } else if (issue.isSaved === false) { // State === Issue.STATES.NEW) {
                this.clearForm();
            }

            this.restoreForm();
        };

        /**
         * @method handleFormSubmissionResponse
         * @param response
         * @private
         */
        BlockFormManager.prototype.handleFormSubmissionResponse = function(response) {

            // TODO: handle response.status === false
            // TODO: handle response.warning ...

            // We could trigger an event instead.
            // Yuk.
            console.log("jijjijij");
            Y.use('moodle-core-formchangechecker', function() {
                M.core_formchangechecker.reset_form_dirty_state();
            });
            // Document.location.reload();

            this.issueManager.getActiveIssue().setId(response.issueid);
        };

        /**
         * @method handleFormSubmissionFailure
         * @param response
         * @private
         */
        BlockFormManager.prototype.handleFormSubmissionFailure = function(response) {
            // Oh noes! Epic fail :(
            // Ah wait - this is normal. We need to re-display the form with errors!
            console.error("An error occured");
            console.error(response);
        };

        BlockFormManager.prototype.clearForm = function() {
            // Remove delete button.
            this.form.find('#qtracker-delete').remove();
            this.resetValidation();
            Str.get_string('submitnewissue', 'local_qtracker').then(function(string) {
                this.form.find('button[type="submit"]').html(string);
            }.bind(this));
        };

        BlockFormManager.prototype.restoreForm = function() {
            let issue = this.issueManager.getActiveIssue();
            this.form.find('[name="issuetitle"]').val(issue.getTitle());
            this.form.find('[name="issuedescription"]').val(issue.getDescription());

        };

        /**
         * @method editIssue
         * @private
         */
        BlockFormManager.prototype.editIssue = function() {
            var formData = new FormData(this.form[0]);
            Ajax.call([{
                methodname: 'local_qtracker_edit_issue',
                args: {
                    issueid: this.issueManager.getActiveIssue().getId(),
                    issuetitle: formData.get('issuetitle'),
                    issuedescription: formData.get('issuedescription'),
                },
                done: function(response) {
                    Str.get_string('issueupdated', 'local_qtracker').then(function(string) {
                        let notification = {
                            message: string,
                            announce: true,
                            type: "success",
                        };
                        this.notify(notification);
                    }.bind(this));
                    this.handleFormSubmissionResponse(response);
                }.bind(this),
                fail: this.handleFormSubmissionFailure.bind(this)
            }]);
        };

        /**
         * @method editIssue
         * @private
         */
        BlockFormManager.prototype.deleteIssue = function() {
            Ajax.call([{
                methodname: 'local_qtracker_delete_issue',
                args: {
                    issueid: this.issueManager.getActiveIssue().getId(),
                },
                done: function() {
                    Str.get_string('issuedeleted', 'local_qtracker').then(function(string) {
                        let notification = {
                            message: string,
                            announce: true,
                            type: "success",
                        };
                        this.notify(notification);
                    }.bind(this));
                    this.issueManager.getActiveIssue().isSaved = false;// ChangeState(Issue.STATES.NEW);;
                    this.clearForm();
                }.bind(this),
                fail: this.handleFormSubmissionFailure.bind(this)
            }]);
        };

        /**
         * @method handleFormSubmissionFailure
         * @private
         */
        BlockFormManager.prototype.createIssue = function() {
            var formData = new FormData(this.form[0]);
            // Now we can continue...
            Ajax.call([{
                methodname: 'local_qtracker_new_issue',
                args: {
                    qubaid: formData.get('qubaid'),
                    slot: formData.get('slot'),
                    contextid: this.contextid,
                    issuetitle: formData.get('issuetitle'),
                    issuedescription: formData.get('issuedescription'),
                },
                done: function(response) {
                    Str.get_string('issuecreated', 'local_qtracker').then(function(string) {
                        let notification = {
                            message: string,
                            announce: true,
                            type: "success",
                        };
                        this.notify(notification);
                    }.bind(this));
                    this.issueManager.getActiveIssue().isSaved = true;// ChangeState(Issue.STATES.EXISTING)
                    // This.setAction(ACTION.EDITISSUE);
                    // TODO: add delete button.
                    this.toggleUpdateButton(true);
                    this.toggleDeleteButton(true);

                    this.handleFormSubmissionResponse(response);
                }.bind(this),
                fail: this.handleFormSubmissionFailure.bind(this)
            }]);
        };

        /**
         * Cancel any typing pause timer.
         */
        BlockFormManager.prototype.cancelNotificationTimer = function() {
            if (notificationTimeoutHandle) {
                clearTimeout(notificationTimeoutHandle);
            }
            notificationTimeoutHandle = null;
        };

        BlockFormManager.prototype.notify = function(notification) {
            notification = $.extend({
                closebutton: true,
                announce: true,
                type: 'error',
                extraclasses: "show",
            }, notification);

            let types = {
                'success': 'core/notification_success',
                'info': 'core/notification_info',
                'warning': 'core/notification_warning',
                'error': 'core/notification_error',
            };

            this.cancelNotificationTimer();

            let template = types[notification.type];
            Templates.render(template, notification)
                .then((html, js) => {
                    $('#qtracker-notifications').html(html);
                    Templates.runTemplateJS(js);

                    notificationTimeoutHandle = setTimeout(() => {
                        $('#qtracker-notifications').find('.alert').alert('close');
                    }, NOTIFICATION_DURATION);
                })
                .catch((error) => {
                    console.error(error);
                    throw error;
                });
        };
        /**
         * @method handleFormSubmissionFailure
         * @param show
         * @private
         */
        BlockFormManager.prototype.toggleUpdateButton = function(show) {
            if (show) {
                Str.get_string('update', 'core').then(function(updateStr) {
                    this.form.find(SELECTORS.SUBMIT_BUTTON).html(updateStr);
                }.bind(this));
            } else {
                Str.get_string('submitnewissue', 'local_qtracker').then(function(updateStr) {
                    this.form.find(SELECTORS.SUBMIT_BUTTON).html(updateStr);
                }.bind(this));
            }
        };
        /**
         * @method handleFormSubmissionFailure
         * @param show
         * @private
         */
        BlockFormManager.prototype.toggleDeleteButton = function(show) {
            const context = {
                type: "button",
                classes: "col-auto",
                label: "Delete",
                id: "qtracker-delete",
            };

            let deleteButton = this.form.find(SELECTORS.DELETE_BUTTON);
            if (deleteButton.length == 0 && show) {
                Templates.render('local_qtracker/button', context)
                    .then(function(html, js) {
                        var container = this.form.find('button').closest(".form-row");
                        Templates.appendNodeContents(container, html, js);
                        this.form.find('#qtracker-delete').on('click', function() {
                            this.deleteIssue();
                        }.bind(this));
                    }.bind(this));
            } else {
                if (show) {
                    deleteButton.show();
                } else {
                    deleteButton.hide();
                }
            }
        };

        /**
         * @method handleFormSubmissionFailure
         * @param newaction
         * @private
         */
        BlockFormManager.prototype.setAction = function(newaction) {

            this.form.data('action', newaction);
        };

        /**
         * Private method
         *
         * @method submitFormAjax
         * @private
         * @param {Event} e Form submission event.
         */
        BlockFormManager.prototype.submitFormAjax = function(e) {
            // We don't want to do a real form submission.
            e.preventDefault();
            e.stopPropagation();

            if (!this.validateForm()) {
                return;
            }


            if (this.issueManager.getActiveIssue().isSaved === true) {
                this.editIssue();
            } else {
                this.createIssue();
            }
/*
            Var state = this.issueManager.getActiveIssue().getState();
            switch (state) {
                case Issue.STATES.NEW:
                    this.createIssue();
                    break;
                case Issue.STATES.EXISTING:
                    this.editIssue();
                    break;
                case Issue.STATES.DELETED:
                    this.issueManager.getActiveIssue().changeState(Issue.STATES.NEW)
                    this.createIssue();
                    break;
                default:
                    break;
            }*/
        };

        BlockFormManager.prototype.validateForm = function() {
            let valid = true;
            VALIDATION_ELEMENTS.forEach(selector => {
                let element = this.form.find(selector);
                if (element.val() != "" && element.prop("validity").valid) {
                    element.removeClass("is-invalid").addClass("is-valid");
                } else {
                    element.removeClass("is-valid").addClass("is-invalid");
                    valid = false;
                }
            });
            return valid;
        };

        BlockFormManager.prototype.resetValidation = function() {
            VALIDATION_ELEMENTS.forEach(selector => {
                let element = this.form.find(selector);
                element.removeClass("is-invalid").removeClass("is-valid");
            });
        };

        /**
         * This triggers a form submission, so that any mform elements can do final tricks before the form submission is processed.
         *
         * @method submitForm
         * @param {Event} e Form submission event.
         * @private
         */
        BlockFormManager.prototype.submitForm = function(e) {
            e.preventDefault();
            this.form.submit();
        };

        return /** @alias module:local_qtracker/BlockFormManager */ {

            /**
             * Initialise the module.
             *
             * @method init
             * @param {string} selector The selector used to find the form for to use for this module.
             * @param {string} issueids The ids of existing issues to load.
             * @param {int} contextid
             * @return {BlockFormManager}
             */
            init: function(selector, issueids, contextid) {
                return new BlockFormManager(selector, issueids, contextid);
            }
        };
    });
