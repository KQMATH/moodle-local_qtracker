<?php
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
 * English keywords
 *
 * @package    local_qtracker
 * @author     André Storhaug <andr3.storhaug@gmail.com>
 * @copyright  2020 NTNU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Question Tracker';
$string['qtracker'] = 'Question Tracker';
$string['qtracker:addinstance'] = 'Add a new Question Tracker block';
$string['qtracker:myaddinstance'] = 'Add a new Question Tracker block to the My Moodle page';

$string['blockstring'] = 'Block string';
$string['descconfig'] = 'Description of the config section';
$string['descfoo'] = 'Config description';
$string['headerconfig'] = 'Config section header';
$string['labelfoo'] = 'Config label';

// Question issues table.
$string['id'] = 'ID';
$string['questionid'] = 'Question ID';
$string['title'] = 'Title';
$string['description'] = 'Description';
$string['timecreated'] = 'Created Time';

$string['question_problem_details'] = 'If you have feedback for this question, please type it below.';
$string['cannotcreateissue'] = 'Cannot create a new question issue.';
$string['cannoteditissue'] = 'Cannot edit question issue with ID {$a}.';
$string['cannotdeleteissue'] = 'Cannot delete question issue with ID {$a}.';
$string['cannotgetissue'] = 'Cannot get question issue with ID {$a}.';
$string['cannotgetquestion'] = 'Cannot get question with ID {$a}.';
$string['cannotgetissues'] = 'Cannot get question issues.';
$string['question'] = 'Question';
$string['question_help'] = 'Select the question you want to register a new issue for.';

$string['unknownquestionidnumber'] = 'Unknown question ID "{$a}"';
$string['unknownqubaidnumber'] = 'Unknown question usage ID "{$a}"';
$string['title'] = 'Title';
$string['leavecomment'] = 'Leave a comment';

$string['newissue_questionid'] = 'Question ID';
$string['newissue_questionid_help'] = 'Please select the id for the question you want to register a new issue for. This can be found in the URL of the question page.';

$string['issueupdatednotify'] = 'Issue "{$a}" is updated';
$string['sendmessage'] = 'Notify issue reporter';

$string['issuecreated'] = 'Issue successfully created.';
$string['issueupdated'] = 'Issue successfully updated.';
$string['issuedeleted'] = 'Issue successfully deleted.';

$string['issuesuperseded'] = 'Issue is superseded by {$a}.';

$string['issue'] = 'Issue';
$string['issues'] = 'Issues';
$string['comments'] ='Comments';

$string['submitnewissue'] = 'Submit new issue';
$string['validtitle'] = 'Please provide a valid title.';
$string['validdescription'] = 'Please provide a valid description.';

$string['commentedon'] = 'commented on ';
$string['openedissueon'] = 'opened issue {$a} on ';
$string['errorsubsumingissue'] = 'An error occurred trying to subsume issue {$a}. The issue is probably superseded by another issue.';
$string['name'] = 'Name';

$string['tags'] = 'Tags';
$string['linkedissues'] = 'Linked issues';
$string['newtag'] = 'New tag';
$string['tagid'] = 'Tag ID';
$string['label'] = 'Label';
$string['color'] = 'Color';

$string['new'] = 'New';
$string['open'] = 'Open';
$string['closed'] = 'Closed';
$string['newissue'] = 'New issue';
$string['allissues'] = 'All issues';

$string['reopenissue'] = 'Reopen issue';
$string['closeissue'] = 'Comment and close issue';
$string['comment'] = 'Comment';

//TODO clean up the text
$string['commentandmail'] = 'Comment and forward mail to issue creator';
$string['issuesubject'] = 'some subject';
$string['commentanddm'] = 'Comment and dm issue to creator';
$string['messageprovider:issueresponse'] = 'Response on reported question issues';


$string['confirm'] = 'Confirm';
$string['deletecomment'] = 'Delete comment';
$string['confirmdeletecomment'] = 'Are you sure you want to delete this comment?';
$string['sendcomment'] = 'Notify issue reporter';
$string['confirmsendcomment'] = 'Are you sure you want to notify the issue reporter about this comment?';

$string['preview'] = 'Preview';
$string['edit'] = 'Edit';
$string['questionissues'] = 'Question issues';

$string['noitems'] = 'No items';
$string['createnewissue'] = 'Create new issue';
$string['errornonexistingquestion'] = 'Please provide an valid question ID.';

$string['subsumeissue'] = 'Subsume issue';
$string['subsumeissueconfirm'] = 'Are you sure you want to subsume issue <b>#{$a->child}</b> under <b>#{$a->parent}</b>?<br>This will close issue <b>#{$a->child}</b>.';
$string['subsumedissues'] = 'Subsumed issues';
$string['subsumedescription'] = 'Subsume issues under this issue';
$string['tagsdescription'] = 'Add tags for this issue';

$string['qtracker:addissue'] = 'Add new issue';
$string['qtracker:editmine'] = 'Edit your own issues';
$string['qtracker:editall'] = 'Edit all issues';
$string['qtracker:viewmine'] = 'Edit your own issues';
$string['qtracker:viewall'] = 'View all issues';

$string['privacy:metadata:local_qtracker_issue'] = 'Details about each question issue.';
$string['privacy:metadata:local_qtracker_issue:userid'] = 'The user that created the issue.';
$string['privacy:metadata:local_qtracker_issue:title'] = 'The title of the issue.';
$string['privacy:metadata:local_qtracker_issue:description'] = 'The description of the issue.';
$string['privacy:metadata:local_qtracker_issue:timecreated'] = 'The time the issue was created.';

$string['privacy:metadata:local_qtracker_comment'] = 'Details about each question issue comment.';
$string['privacy:metadata:local_qtracker_comment:userid'] = 'The user that created the issue comment.';
$string['privacy:metadata:local_qtracker_comment:description'] = 'The description of the issue comment.';
$string['privacy:metadata:local_qtracker_comment:timecreated'] = 'The time the issue comment was created.';
