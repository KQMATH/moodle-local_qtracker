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
import Ajax from 'core/ajax';
import { get_string as getString } from 'core/str';

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
    container = null;
    items = new Map();
    activeItems = new Map();
    isSearching = false;
    constructor(container) {
        this.container = $(container); // Container element dropdown-menu
        this.init();
        this.render = this.renderItems.bind(this);
        this.addListeners = this.addListeners.bind(this);

    }

    init() {
        this.addListeners();
    }

    addListeners() {
        let self = this;
        this.container.find('input[type="search"]').each(function () {
            $(this).on('input', async function (event) {
                await this.search($(event.target).val());
                this.render();
            }.bind(self))
        });

        this.container.on('click', function (event) {
            let element = $(event.target);
            if (!element.hasClass("dropdown-item") || element.hasClass("disabled"))  {
                return;
            }
            this.handleClicked(element);
        }.bind(this));
    }

    setActiveItems(items) {
        this.activeItems = new Map();
        items.forEach(item => this.activeItems.set(item.id, item));
    }

    getActiveItems() {
        return this.activeItems;
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
            Templates.replaceNodeContents(this.container, html, js);
        });
    }

    async renderItems() {
        console.log("RENDER")
        this.empty();
        console.warn(this)
        let elements = [];
        let items = this.isSearching ? this.getItems() : this.getActiveItems();

        if (items.size === 0) {
            let element = $('<div></div>')
            .addClass("dropdown-item disabled")
            .html(await getString('noitems', 'local_qtracker'))
            .prop('outerHTML');
            elements.push(element)
        }

        items.forEach((item, id, map) => {
            let element = $('<div></div>')
            .addClass("dropdown-item")
            .addClass(() => {
                if (this.isActiveItem(item)) {
                    return "active";
                }
            })
            .attr( "data-value", item.id )
            .html(item.title).prop('outerHTML');
            elements.push(element)
        });
        this.container.append(elements);
        //<a class="dropdown-item" data-value={{value}} href="#">{{{text}}}</a>
    }

    setItems(items) {
        this.items = new Map();
        items.forEach(item => this.items.set(item.id, item));
    }

    getItems() {
        return this.items;
    }

    getAllItems() {
        return Array.prototype.concat(this.items, this.getActiveItems());
    }

    isActiveItem(item) {
        return this.getActiveItems().has(item.id);
    }

    async handleClicked(element) {
        let selected = element.hasClass("active") ? true : false;
        let id = parseInt(element.attr("data-value"));
        let response = await this.onclick(id, selected);
        if (response.status) {
            if (this.getActiveItems().has(id)) {
                this.getActiveItems().delete(id);
            } else {
                this.getActiveItems().set(id, this.getItems().get(id));
            }
        }
        this.onchange(this.getActiveItems());
        this.renderItems();
    }

    async search(str) {
        if (str.length > 0 ) {
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
            criteria.push({ key: 'title', value: str  });
        }

        let issuesResponse = await this.loadIssuesData(criteria);
        let issues = issuesResponse.issues;
        this.setItems(issues);
        return issues;
    }



    async loadIssuesData(criteria) {
        let issuesData = await Ajax.call([{
            methodname: 'local_qtracker_get_issues',
            args: { criteria: criteria }
        }])[0];
        return issuesData;
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
        this.container.find('.dropdown-item').remove();
    }

    addTemplateItem(html, js) {
        Templates.appendNodeContents('.qtracker-sidebar-content .qtracker-items', html, js);
    }

    getElements() {
        return $('.qtracker-sidebar-content .qtracker-items').children();
    }

}

export default Dropdown;
