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
import * as Str from 'core/str';
import ModalFactory from 'core/modal_factory';
import ModalEvents from 'core/modal_events';

/**
 * Constructor
 * @constructor
 * @param {int} commentid
 *
 * Each call gets it's own instance of this class.
 */
class IssueCommentControls {
    constructor(commentid) {
        this.commentid = commentid;
        this.init();
    }

    async init() {
        this.registerDeleteButtonListener()
        this.registerNotifyButtonListener()
    }

    async registerDeleteButtonListener() {

        let trigger = $('#comment_delete_' + this.commentid);
        let strObj = [
            {
                key: 'confirm',
                component: 'local_qtracker'
            },
            {
                key: 'confirmdeletecomment',
                component: 'local_qtracker'
            },
            {
                key: 'deletecomment',
                component: 'local_qtracker'
            }
        ];

        let strings = await Str.get_strings(strObj);

        let modal = await ModalFactory.create({
            type: ModalFactory.types.SAVE_CANCEL,
            title: strings[2],
            body: strings[1],
        }, trigger)

        modal.setSaveButtonText(strings[0])
        modal.getRoot().on(ModalEvents.save, function (e) {
            // Stop the default save button behaviour which is to close the modal.
            e.preventDefault();
            let form = $('#comment_form_'  + this.commentid);
            $('<input>').attr({
                type: "hidden",
                name: "deletecommentid",
                value: this.commentid,
            }).appendTo(form);
            form.submit();
        }.bind(this));
    }

    async registerNotifyButtonListener() {

        let trigger = $('#comment_message_' + this.commentid);
        let strObj = [
            {
                key: 'confirm',
                component: 'local_qtracker'
            },
            {
                key: 'confirmsendcomment',
                component: 'local_qtracker'
            },
            {
                key: 'sendcomment',
                component: 'local_qtracker'
            }
        ];

        let strings = await Str.get_strings(strObj);

        let modal = await ModalFactory.create({
            type: ModalFactory.types.SAVE_CANCEL,
            title: strings[2],
            body: strings[1],
        }, trigger)

        modal.setSaveButtonText(strings[0])
        modal.getRoot().on(ModalEvents.save, function (e) {
            // Stop the default save button behaviour which is to close the modal.
            e.preventDefault();
            let form = $('#comment_form_'  + this.commentid);
            $('<input>').attr({
                type: "hidden",
                name: "notifycommentid",
                value: this.commentid,
            }).appendTo(form);
            form.submit();
        }.bind(this));
    }
}


export default IssueCommentControls;
