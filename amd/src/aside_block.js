
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
 * Class to handle aside blocks
 *
 * @module     local_qtracker/AsideBlock
 * @class      AsideBlockManager
 * @package    local_qtracker
 * @author     David Rise Knotten <david_knotten@hotmail.no>
 * @copyright  2021 NTNU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class AsideBlockManager {
    /**
     * Pointer to the menu element the aside block manager handles
     * @type {element}
     * @private
     */
    menu = null;

    /**
     * Pointer to the searchfield element the aside block manager handles
     * @type {element}
     * @private
     */
    searchfield = null;

    /**
     * Pointer to the list element the aside block manager handles
     * @type {element}
     * @private
     */
    list = null;

    /**
     * The name of the item type the aside block manager handles
     * @type {?string}
     */
    type = null;

    /**
     * The label of the item type the aside block manager handles
     * @type {?string}
     */
    label = null;

    /**
     * SVG path to draw a cross symbol
     * @type {string}
     */
    cross = '<path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/>';

    /**
     * SVG path to draw a checkmark
     * @type {string}
     */
    checkmark = '<path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.267.267 0 0 1 .02-.022z"/>';

    /**
     * AsideBlockManager constructor
     * @param type {string} the type of the item
     * @param label {string} the items label
     */
    constructor(type, label) {
        this.type = type;
        this.label = label
        this.menu = document.getElementById(type+'menu');
        this.searchfield = this.menu.children.item(0);
        this.list = document.getElementById(type+'list');
        if( this.menu === null ) {
            throw new Error(type+' menu does not exist')
        }
        if( this.searchfield === null) {
            throw new Error(type+' searchfield does not exist')
        }
        if( this.list === null) {
            throw new Error(type+' list does not exist')
        }
    }

    /**
     * Method to set an item to enabled and add to the list
     * @param {element} element the element to activate
     */
    activateElement(element) {
        element.children.item(0).innerHTML = this.cross;
        element.setAttribute('data-state', 'active');
        let listelement = document.createElement('div');
        listelement.classList.add('list-group-item');
        let ignorelength = (this.type+'menuitem').length;
        let index = element.id.slice(ignorelength);
        listelement.innerHTML = element.children.item(1).innerHTML;
        listelement.id = this.type+'listitem'+index;
        this.list.appendChild(listelement);
    }

    /**
     * Method to set an item to disabled and remove from the list
     * @param {element} element the element to deactivate
     */
    deactivateElement(element) {
        element.children.item(0).innerHTML = this.checkmark;
        element.setAttribute('data-state', 'inactive');
        let ignorelength = (this.type+'menuitem').length;
        let index = element.id.slice(ignorelength);
        document.getElementById(this.type+'listitem'+index).remove();

    }

    /**
     *
     * @param {int} ignorelength
     */
    sortlist(ignorelength) {
        //TODO sort list
        for (let i = 0; i < this.list.children.length; i++) {
            this.list.chil
        }
    }

    /**
     * Catches and overrides the handling of clicks to all items in the dropdown menu
     */
    handleClicks() {
        if (this.menu.children.item(2).textContent == 'no '+ this.label) return;
        for (let i = 2; i < this.menu.children.length; i++) {
            let anchor = this.menu.children.item(i)
            let url = anchor.getAttribute('href');
            anchor.addEventListener('click', event => {
                event.preventDefault();
                event.stopPropagation();
                console.log(anchor);
                if (anchor.getAttribute('data-state') === 'active') {
                    this.deactivateElement(anchor);
               } else {

                    this.activateElement(anchor);
                }
            }, true);
            /*
            anchor.addEventListener('click',(event) => {
                event.preventDefault();

                fetch(url, {
                    method: 'POST'
                }).then(response => {
                    return response.json();
                }).then(response => {
                    //TODO handle stuff
                }).catch(error => {
                    //TODO handle errors
                });
            },false);
            */
        }
    };

    /**
     * Initializes the list and ensures that items are correctly hidden/not hidden
     */
    setup() {
        let invalids = 0;
        for (let i = 2; i < this.menu.children.length; i++) {
            let listchild = this.list.children.item(i-2);
            let child = this.menu.children.item(i);
            listchild.id = this.type+'listitem'+(i-2);
            child.id = this.type+'menuitem'+(i-2);
            if (child.getAttribute('data-state') != 'active') {
                let svg = child.children.item(0);
                svg.innerHTML = this.checkmark;
                this.list.children.item(i-2-invalids).remove();
                invalids += 1;
            }
        }
    }

}


/**
 * Initializes an aside block manager
 * @param type {string} the type of the item
 * @param label {string} the items label
 */
export default function init(type, label) {
    let aside = new AsideBlockManager(type,label);
    aside.handleClicks();
    aside.setup();
};



