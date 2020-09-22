# moodle-local_qtracker
:bug: Local Moodle plugin providing issue tracking for Moodle questions.

The QTracker system allows students to comment and ask questions about individual questions in a quiz. The idea and design follows the principles of issue tracker systems, although it is still work in progress and some features will be missed still.

In addition to the QTracker module, a separate block-type module is needed to add the interface to a given activity type. Such block modules have been made for
+ [Core Quiz](https://github.com/KQMATH/moodle-block_quizqtracker)
+ [CAPQuiz](https://github.com/KQMATH/moodle-block_capquizqtracker)

The QTracker functionality is normally accessed via the appropriate block plugin.

## Add the tracker to an Activity

1.  Open the activity as teacher.
2.  Turn editing on.
3.  Click «Add Block» in the left hand menu.
4.  Choose the appropriate block - single-click - this is slow to react.

The block should now appear both in the student and teacher interface, with different contents, but it must be tested.  Sometimes it is necessary to tweak the settings, found in the gear menu in the block itself.

## As student

The student interface is straight forward.  Enter a title and a text, and hit submit.

## Managing issues

In the teacher interface to the activity, the block shows a link to manage issues.  

**TODO** list features.

## Note on publishing as LTI

Observe that LTI does not support blocks in the student view.  This module does not work when students access the acitivity over LTI.

For our own use, we have patched the core moodle installation to show block in LTI, and we are contemplating more permanent solutions.
