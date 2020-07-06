/**
 * Add a create new group modal to the page.
 *
 * @module     core_group/NewIssue
 * @class      NewIssue
 * @package    core_group
 * @copyright  2017 Damyon Wiese <damyon@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', 'core/str', 'core/modal_factory', 'core/modal_events', 'core/fragment', 'core/ajax', 'core/yui'],
        function($, Str, ModalFactory, ModalEvents, Fragment, Ajax, Y) {

    /**
     * Constructor
     *
     * @param {String} selector used to find triggers for the new group modal.
     * @param {int} contextid
     *
     * Each call to init gets it's own instance of this class.
     */
    var NewIssue = function() {
        console.log("issue created")
        this.init(selector);
    };

    /**
     * @var {Modal} modal
     * @private
     */
    NewIssue.prototype.modal = null;

    /**
     * @var {int} contextid
     * @private
     */
    NewIssue.prototype.contextid = -1;

    /**
     * Initialise the class.
     *
     * @param {String} selector used to find triggers for the new group modal.
     * @private
     * @return {Promise}
     */
    NewIssue.prototype.init = function(selector) {
        console.log("issue initiated");
    };

    return new Issue(selector);
});
