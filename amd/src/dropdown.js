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
 * @module     local_qtracker/Dropdown
 * @class      Dropdown
 * @package    local_qtracker
 * @author     André Storhaug <andr3.storhaug@gmail.com>
 * @copyright  2021 NTNU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import $ from 'jquery';
import Templates from 'core/templates';
import { get_string as getString } from 'core/str';
import DropdownEvents from 'local_qtracker/dropdown_events';
import { loadIssuesData } from 'local_qtracker/api_helpers';

var SELECTORS = {
    DROPDOWN: '[data-region="dropdown"]',
    SEARCH: 'input[type="search"]',
    ITEM: '[data-region="item"]',
};

/**
 * Constructor
 * @constructor
 * @param {String} selector used to find triggers for the new group modal.
 * @param {int} contextid
 *
 * Each call to init gets it's own instance of this class.
 */
class Dropdown {
    loading = null;
    root = null;
    items = new Map();
    activeItems = new Map();
    isSearching = false;

    constructor(root, search = true) {
        this.root = $(root); // Root element dropdown-menu
        this.dropdown = this.root.find(SELECTORS.DROPDOWN);
        this.search = search;

        this.render = this.renderItems.bind(this);
        this.registerEventListeners = this.registerEventListeners.bind(this);

        if (this.search) {
            this.registerEventListeners();
        }
    }



    /**
     * Get the dropdown element of this dropdown.
     *
     * @method getDropdown
     * @return {object} jQuery object
     */
    getDropdown() {
        return this.dropdown;
    };

    /**
     * Get the dropdown element of this dropdown.
     *
     * @method getDropdown
     * @return {object} jQuery object
     */
    getRoot() {
        return this.root;
    };

    /**
     * Set up all of the event handling for the modal.
     *
     * @method registerEventListeners
     */
    registerEventListeners() {
        // Handle the clicking of an item.
        this.getDropdown().on('click', SELECTORS.ITEM, function (e) {
            let element = $(e.currentTarget);
            if (element.hasClass("disabled")) {
                return;
            }
            var clickEvent = $.Event(DropdownEvents.click);
            this.getRoot().trigger(clickEvent, [element]);

            if (!clickEvent.isDefaultPrevented()) {
                e.preventDefault();
            }
        }.bind(this));

        this.registerSearchListener();
    }


    /**
     * Register a listener to close the dialogue when the save button is pressed.
     *
     * @method registerSearch
     */
    registerSearchListener() {

        this.getDropdown().find(SELECTORS.SEARCH).on('input', function (e) {

            let str = $(e.target).val();
            var searchEvent = $.Event(DropdownEvents.search, str);
            this.getRoot().trigger(searchEvent, [str]);

            if (!searchEvent.isDefaultPrevented()) {
                e.preventDefault();
                if (str.length > 0) {
                    this.isSearching = true;
                } else {
                    this.isSearching = false;
                }
                this.renderItems();
            }
        }.bind(this));
    };


    getActiveItems() {

        return this.activeItems;
    }

    setItemStatus(key, active = true) {
        if (active) {
            let item = this.getItems().get(key);
            this.activeItems.set(key, item);
        } else {
            this.activeItems.delete(key);
        }
    }

    reset() {
        this.isSearching = false;
        this.getDropdown().find(SELECTORS.SEARCH).val("");
        this.renderItems()
    }

    async render() {
        let context = {
            trigger: {
                "key": "fa-times",
                "title": "Close",
                "alt": "Close pane",
                "extraclasses": "",
                "unmappedIcon": false
            }
        };
        //let self = this;
        await Templates.render('local_qtracker/dropdown', context).then((html, js) => {
            Templates.replaceNodeContents(this.getDropdown(), html, js);
        });
    }

    async generateItems() {
        let elements = [];
        let items = this.isSearching ? this.getItems() : this.getActiveItems();
        if (items.size === 0) {
            let element = $('<div></div>')
                .addClass("dropdown-item disabled")
                .attr("data-region", 'item')
                .html(await getString('noitems', 'local_qtracker'))
                .prop('outerHTML');
            elements.push(element)
        } else {
            // map ;; index(id), html
            items.forEach((html, key) => {
                let element = $('<div></div>')
                    .addClass("dropdown-item")
                    .addClass(() => {
                        if (this.isActiveItem(key)) {
                            return "active";
                        }
                    })
                    .attr("data-value", key)
                    .attr("data-region", 'item')
                    .html(html).prop('outerHTML');
                elements.push(element)
            });
        }
        return elements;
    }

    async renderItems() {
        this.empty();
        let elements = await this.generateItems();
        this.getDropdown().append(elements);
        //<a class="dropdown-item" data-value={{value}} href="#">{{{text}}}</a>
    }

    /**
     *
     * @param {*} items tuples [id, html]
     * @param {*} active
     */
    setItems(items, active = false) {
        if (active) {
            this.activeItems = new Map();
            items.forEach(item => this.activeItems.set(item[0], item[1]));
        } else {
            this.items = new Map();
            items.forEach(item => this.items.set(item[0], item[1]));
        }
    }

    getItems() {
        return this.items;
    }

    getAllItems() {
        return Array.prototype.concat(this.items, this.getActiveItems());
    }

    isActiveItem(key) {
        return this.getActiveItems().has(key);
    }

    async search(str) {
        if (str.length > 0) {
            this.isSearching = true;
        } else {
            this.isSearching = false;
        }

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
        this.setItems(issues);
        return issues;
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
        this.getDropdown().find(SELECTORS.ITEM).remove();
    }

    addTemplateItem(html, js) {
        Templates.appendNodeContents('.qtracker-sidebar-content .qtracker-items', html, js);
    }

    getElements() {
        return $('.qtracker-sidebar-content .qtracker-items').children();
    }

}

export default Dropdown;
