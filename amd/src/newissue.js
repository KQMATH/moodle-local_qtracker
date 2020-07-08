/**
 * Add a create new group modal to the page.
 *
 * @module     core_group/NewIssue
 * @class      NewIssue
 * @package    core_group
 * @copyright  2017 Damyon Wiese <damyon@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', 'core/str', 'core/templates', 'core/modal_events', 'core/fragment', 'core/ajax', 'core/yui'],
        function($, Str, Templates, ModalEvents, Fragment, Ajax, Y, ) {

    /**
     * Constructor
     *
     * @param {String} selector used to find triggers for the new group modal.
     * @param {int} contextid
     *
     * Each call to init gets it's own instance of this class.
     */
    var NewIssue = function(selector) {
        this.init(selector);
    };

    /**
     * @var {Form} form
     * @private
     */
    NewIssue.prototype.form = null;

    /**
     * @var {int} contextid
     * @private
     */
    NewIssue.prototype.contextid = -1;

    /**
     * Initialise the class.
     *
     * @param {String} selector used to find triggers for the new question issue.
     * @private
     * @return {Promise}
     */
    NewIssue.prototype.init = function(selector) {
        var trigger = $(selector);
        this.form = trigger;

        // Fetch the title string.
        return Str.get_string('creategroup', 'core_group').then(function(title) {
            console.log(title);
            this.contextid = 2
        }.bind(this)).then(()=> {

            console.log(trigger)
            // We catch the modal save event, and use it to submit the form inside the modal.
            // Triggering a form submission will give JS validation scripts a chance to check for errors.
            //this.form.getRoot().on(ModalEvents.save, this.submitForm.bind(this));
            // We also catch the form submit event and use it to submit the form with ajax.
            //this.modal.getRoot().on('submit', 'form', this.submitFormAjax.bind(this));
            trigger.on('submit', this.submitFormAjax.bind(this));
        });

    };

    /**
     * @method handleFormSubmissionResponse
     * @private
     * @return {Promise}
     */
    NewIssue.prototype.handleFormSubmissionResponse = function(formData, response) {
        console.log("Success!", formData, response)
        // We could trigger an event instead.
        // Yuk.
        Y.use('moodle-core-formchangechecker', function() {
            M.core_formchangechecker.reset_form_dirty_state();
        });
        //document.location.reload();
    };

    /**
     * @method handleFormSubmissionFailure
     * @private
     * @return {Promise}
     */
    NewIssue.prototype.handleFormSubmissionFailure = function(data, response) {
        // Oh noes! Epic fail :(
        // Ah wait - this is normal. We need to re-display the form with errors!
        console.error("An error occured");
        console.error(response);
    };

    /**
     * Private method
     *
     * @method submitFormAjax
     * @private
     * @param {Event} e Form submission event.
     */
    NewIssue.prototype.submitFormAjax = function(e) {
        // We don't want to do a real form submission.
        e.preventDefault();




        var changeEvent = document.createEvent('HTMLEvents');
        changeEvent.initEvent('change', true, true);

        // Prompt all inputs to run their validation functions.
        // Normally this would happen when the form is submitted, but
        // since we aren't submitting the form normally we need to run client side
        // validation.
        this.form.find(':input').each(function(index, element) {
            element.dispatchEvent(changeEvent);
        });

        // Now the change events have run, see if there are any "invalid" form fields.
        var invalid = $.merge(
            this.form.find('[aria-invalid="true"]'),
            this.form.find('.error')
        );

        // If we found invalid fields, focus on the first one and do not submit via ajax.
        if (invalid.length) {
            invalid.first().focus();
            return;
        }

        // Convert all the form elements values to a serialised string.
        //var formData = this.form.serialize();
        var formData = new FormData(this.form[0]);
        // Now we can continue...
        Ajax.call([{
            methodname: 'local_qtracker_new_issue',
            args: {
                questionid: formData.get('questionid'),
                issuetitle: formData.get('issuetitle'),
                issuedescription: formData.get('issuedescription'),
            },
            done: this.handleFormSubmissionResponse.bind(this, formData),
            fail: this.handleFormSubmissionFailure.bind(this, formData)
        }]);
    };

    /**
     * This triggers a form submission, so that any mform elements can do final tricks before the form submission is processed.
     *
     * @method submitForm
     * @param {Event} e Form submission event.
     * @private
     */
    NewIssue.prototype.submitForm = function(e) {
        e.preventDefault();
        this.form.submit();
    };

    return /** @alias module:core_group/NewIssue */ {
        // Public variables and functions.
        /**
         * Attach event listeners to initialise this module.
         *
         * @method init
         * @param {string} selector The CSS selector used to find nodes that will trigger this module.
         * @param {int} contextid The contextid for the course.
         * @return {Promise}
         */
        init: function(selector) {
            return new NewIssue(selector);
        }
    };
});
