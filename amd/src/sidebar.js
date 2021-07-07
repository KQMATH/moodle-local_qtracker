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
 * @module     local_qtracker/QuestionsIssue
 * @class      QuestionsIssue
 * @package    local_qtracker
 * @author     André Storhaug <andr3.storhaug@gmail.com>
 * @copyright  2021 NTNU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import $ from 'jquery';
import Templates from 'core/templates';
import Resizer from 'local_qtracker/resizer';

/**
 * Constructor
 * @constructor
 * @param {String} selector used to find triggers for the new group modal.
 * @param {int} contextid
 *
 * Each call to init gets it's own instance of this class.
 */
class Sidebar {
    hidden = null;
    loading = null;
    width = null;
    margin = null;
    container = null;

    constructor(container, show = false, side = 'right', loading = false, width = '40%', margin = '0px') {
        this.container = container; // Container element
        this.hidden = !show;
        this.visible = show;
        this.side = side;
        this.loading = loading;
        this.width = width;
        this.margin = margin;

        this.mql = window.matchMedia('(min-width: 768px)');
        this.mql.addEventListener('change', this.screenTest.bind(this));

        this.render = this.render.bind(this);

    }

    screenTest(e) {
        if (!this.visible) {
            return;
        }
        if (e.matches) {
            /* the viewport is 768px pixels wide or more */
            $('#qtracker-sidebar').css('width', 'calc(' + this.width + ' - -1.25rem)');
            $('.qtracker-push-pane-over').css('padding-' + this.getSide(), 'calc(' + this.width + ' - -' + this.margin + ')');
            //document.body.style.backgroundColor = 'red';
        } else {
            /* the viewport is more than 768px pixels wide or less */
            $('#qtracker-sidebar').css('width', '100%');
            $('.qtracker-push-pane-over').css('padding-' + this.getSide(), '0');

            //document.body.style.backgroundColor = 'blue';
        }
    }

    async render() {
        let context = {
            close: {
                "key": "fa-times",
                "title": "Close",
                "alt": "Close pane",
                "extraclasses": "",
                "unmappedIcon": false
            },
            options: {
                "key": "fa-cog",
                "title": "Options",
                "alt": "Show options",
                "extraclasses": "",
                "unmappedIcon": false
            }
        };
        //let self = this;
        await Templates.render('local_qtracker/sidebar', context).then((html, js) => {
            Templates.replaceNodeContents(this.container, html, js);
            this.setVisibility(!this.hidden);
            this.setLoading(this.loading);
            this.setSide(this.side);
            //this.setWidth(this.width);
            this.screenTest(this.mql)
            /*this.resizer = new Resizer($('#qtracker-sidebar')[0], true, function(x,y) {
                self.setWidth(
                    'calc(' + x + 'px ' + ' - 30px)',
                    'calc(' + x + 'px ' + ' - -' + self.margin + ')'
                )
            });*/
        });
    }


    getSide() {
        return this.side;
    }

    getOppositeSide() {
        return this.side == 'left' ? 'right' : 'left';
    }

    /**
     *
     * @param {*} width The sidebar width
     * @param {*} width2 The existing content width
     */
    setWidth(width, width2) {
        this.width = width;
        this.width2 = width2;

        this.screenTest(this.mql);
    }

    isMobileWidth() {
        return !this.mql.matches;
    }
    setSide(side) {
        if (side == 'right') {
            $('#qtracker-sidebar').addClass('qtracker-sidebar-right');
            $('#qtracker-sidebar').removeClass('qtracker-sidebar-left');
            //$('#qtracker-sidebar').addClass('border-left');
            //$('#qtracker-sidebar').removeClass('border-right');
        } else if (side == 'left') {
            $('#qtracker-sidebar').addClass('qtracker-sidebar-left');
            $('#qtracker-sidebar').removeClass('qtracker-sidebar-right');
            //$('#qtracker-sidebar').addClass('border-right');
            //$('#qtracker-sidebar').removeClass('border-left');
        }
    }

    setTitle(html) {
        $('.qtracker-sidebar-title').html(html);
    }

    setLoading(show = true) {
        if (show) {
            $('.qtracker-sidebar-content .loading').addClass("show");
            this.loading = true;
        } else {
            $('.qtracker-sidebar-content .loading').removeClass("show");
            this.loading = false;
        }
    }

    empty() {
        $('.qtracker-sidebar-content .qtracker-items').empty();
    }

    addTemplateItem(html, js) {
        Templates.appendNodeContents('.qtracker-sidebar-content .qtracker-items', html, js);
    }

    getItems() {
        return $('.qtracker-sidebar-content .qtracker-items').children();
    }

    hide() {
        if (!this.hidden) {
            this.setVisibility(false);
        }
    }

    show() {
        if (this.hidden) {
            this.setVisibility(true);
        }
    }

    setVisibility(show = true) {
        if (show) {
            $('.qtracker-push-pane-over').css('padding-' + this.getSide(), 'calc(' + this.width + ' - -' + this.margin + ')');
            $('#qtracker-sidebar').addClass('show');
            this.screenTest(this.mql);
        } else {
            $('.qtracker-push-pane-over').css('padding-' + this.getSide(),'0');
            $('#qtracker-sidebar').removeClass('show');
        }
        this.hidden = !show;
        this.visible = show;
    }

    togglePane() {
        $('.qtracker-container').toggleClass('qtracker-push-pane-over');
        $('#qtracker-sidebar').toggleClass("show");
        this.hidden = !this.hidden;
    }

    decodeHTML(html) {
        var doc = new DOMParser().parseFromString(html, "text/html");
        return doc.documentElement.textContent;
    }
}

export default Sidebar;
