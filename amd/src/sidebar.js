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
 * Manager for managing a sidebar.
 *
 * @module     local_qtracker/Sidebar
 * @class      Sidebar
 * @package    local_qtracker
 * @author     André Storhaug <andr3.storhaug@gmail.com>
 * @copyright  2021 NTNU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import $ from 'jquery';
import Templates from 'core/templates';

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
    closable = null;
    options = null;

    constructor(container, show = false, side = 'right', loading = false, width = '40%', margin = '0px', closable = true, options = []) {
        this.container = $(container); // Container element
        this.hidden = !show;
        this.visible = show;
        this.side = side;
        this.loading = loading;
        this.width = width;
        this.margin = margin;

        this.closable = closable;
        this.options = options;

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
            //$('#qtracker-sidebar').css('width', 'calc(' + this.width + ' - -1.25rem)');
            //$('.qtracker-push-pane-over').css('padding-' + this.getSide(), 'calc(' + this.width + ' - -' + this.margin + ')');
        } else {
            /* the viewport is more than 768px pixels wide or less */
            //$('#qtracker-sidebar').css('width', '100%');
            //$('.qtracker-push-pane-over').css('padding-' + this.getSide(), '0');
        }
    }

    async render() {
        let self = this;

        let context = {};
        if (this.closable) {
            context.close = {
                "key": "fa-times",
                "title": "Close",
                "alt": "Close pane",
                "extraclasses": "",
                "unmappedIcon": false
            };
        }
        if (this.options.length > 0) {
            context.options = {
                "trigger": {
                    "key": "fa-filter",
                    "title": "Options",
                    "alt": "Show options",
                    "extraclasses": "",
                    "unmappedIcon": false
                },
                "header": false,
                "items": self.options,
            };
        }

        //let self = this;
        await Templates.render('local_qtracker/sidebar', context).then((html, js) => {
            Templates.appendNodeContents(this.container, html, js);
            this.setVisibility(!this.hidden);
            this.setLoading(this.loading);
            this.setSide(this.side);
            this.screenTest(this.mql)
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
        } else if (side == 'left') {
            $('#qtracker-sidebar').addClass('qtracker-sidebar-left');
            $('#qtracker-sidebar').removeClass('qtracker-sidebar-right');
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

    getContainer() {
        return this.container;
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
            //$('.qtracker-push-pane-over').css('padding-' + this.getSide(), 'calc(' + this.width + ' - -' + this.margin + ')');
            $('#qtracker-sidebar').addClass('show');
            $('#page').addClass('qtracker-show-drawer-' + this.side);
            this.screenTest(this.mql);
        } else {
            //$('.qtracker-push-pane-over').css('padding-' + this.getSide(), '0');
            $('#qtracker-sidebar').removeClass('show');
            $('#page').removeClass('qtracker-show-drawer-' + this.side);
        }
        this.hidden = !show;
        this.visible = show;
    }

    togglePane() {
        //$('.qtracker-container').toggleClass('qtracker-push-pane-over');
        $('#qtracker-sidebar').toggleClass("show");
        this.hidden = !this.hidden;
    }

    decodeHTML(html) {
        var doc = new DOMParser().parseFromString(html, "text/html");
        return doc.documentElement.textContent;
    }
}

export default Sidebar;
