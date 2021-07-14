
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
 * @module     local_qtracker/api_helpers
 * @package    local_qtracker
 * @author     André Storhaug <andr3.storhaug@gmail.com>
 * @copyright  2021 NTNU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Ajax from "core/ajax";

/**
 * TODO: dynamically load "from-to" and use limit.
 * @param {*} criteria
 * @param {*} from
 * @param {*} limit
 * @returns
 */
export const loadIssuesData = async (criteria, from = 0, limit = 100) => {
    let issuesData = await Ajax.call([{
        methodname: 'local_qtracker_get_issues',
        args: {
            criteria: criteria,
            from: from,
            limit: limit,
        }
    }])[0];

    return issuesData;
}

