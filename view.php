<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Prints an instance of mod_generativeaiv2.
 *
 * @package     mod_generativeaiv2
 * @copyright   2024 Ken M. Mbabu <kencerm08@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/classes/classes.php');

// Course module id.
$id = optional_param('id', 0, PARAM_INT);

// Activity instance id.
$g = optional_param('g', 0, PARAM_INT);

if ($id) {
    $cm = get_coursemodule_from_id('generativeaiv2', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $moduleinstance = $DB->get_record('generativeaiv2', array('id' => $cm->instance), '*', MUST_EXIST);
} else {
    $moduleinstance = $DB->get_record('generativeaiv2', array('id' => $g), '*', MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $moduleinstance->course), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('generativeaiv2', $moduleinstance->id, $course->id, false, MUST_EXIST);
}

require_login($course, true, $cm);

$modulecontext = context_module::instance($cm->id);

$event = \mod_generativeaiv2\event\course_module_viewed::create(array(
    'objectid' => $moduleinstance->id,
    'context' => $modulecontext
));
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('generativeaiv2', $moduleinstance);
$event->trigger();

$PAGE->set_url('/mod/generativeaiv2/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);

echo $OUTPUT->header();

// Get the quiz data from the plugin instance.
$generativeaiv2 = $DB->get_record('generativeaiv2', array('id' => $cm->instance), '*', MUST_EXIST);
$topic = $generativeaiv2->topic;  // Assuming you have set a topic field in your plugin.
$startdifficulty = $generativeaiv2->startdifficulty ?? 1; // Default to 1 if not set.

// Check if the quiz is complete.
$userid = $USER->id;
$courseid = $COURSE->id;

$quiz_complete = generativeaiv2_is_quiz_complete($cm->instance, $userid);

// Check if a response was submitted.
if (data_submitted()) {

    // Check if the form was submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_answers'])) {
        foreach ($_POST['question_text'] as $id => $text) {
            $update = new stdClass();
            $update->id = $id;
            $update->question_text = $text['text'];
            $update->correct_answer = $_POST['correct_answer'][$id];
            $update->approval_status = 1;
            
            // Handle dynamically added choices
            $choices = $_POST['choices'][$id] ?? [];
            $update->choice1 = $choices[0] ?? '';
            $update->choice2 = $choices[1] ?? '';
            $update->choice3 = $choices[2] ?? '';
            $update->choice4 = $choices[3] ?? '';

            $DB->update_record('generativeaiv2_questions', $update);
        }
        // foreach ($_POST as $key => $value) {
        //     if (strpos($key, 'q') === 0) { // Identify the question IDs from the POST data
        //         $question_id = substr($key, 1); // Extract question ID (e.g., 'q1' becomes '1')
        //         $new_correct_answer = intval($value);

        //         // Update the correct answer in the database
        //         $DB->update_record('generativeaiv2_questions', [
        //             'id' => $question_id,
        //             'correct_answer' => $new_correct_answer,
        //             'approval_status' => 1,
        //         ]);
        //     }
        // }

        echo "<p>Correct answers updated successfully!</p>";
    }
    else{
        // Process all submitted question answers
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'q') === 0) { // Ensure we're dealing with question inputs
                $questionid = substr($key, 1); // Extract the question ID from the input name
                $user_answer = (int)$value; // Get the user's selected answer
                $quizid = $cm->instance;

                // Retrieve the correct answer from the database
                $question = $DB->get_record('generativeaiv2_questions', ['id' => $questionid], '*', MUST_EXIST);

                // Determine if the answer is correct
                $is_correct = ($user_answer === (int)$question->correct_answer) ? 1 : 0;

                // Get the current difficulty level for this user
                $user_progress = $DB->get_record('generativeaiv2_user_response', array('userid' => $userid, 'quizid' => $quizid, 'questionid' => $questionid));
                $current_difficulty = $user_progress ? $user_progress->difficulty : 1;

                // Adjust difficulty based on answer correctness
                $new_difficulty = generativeaiv2_next_difficulty($userid, $current_difficulty, $is_correct);

                // Record the user's response and difficulty progression
                $data = new stdClass();
                $data->userid = $userid;
                $data->quizid = $quizid;
                $data->questionid = $questionid;
                $data->response = $user_answer;
                $data->correct = $is_correct;
                $data->difficulty = $new_difficulty;

                // Insert or update the record in the database
                $DB->insert_record('generativeaiv2_user_response', $data, true); // Update if exists, insert if not

                // Save the completion status
                // $data = new stdClass();
                // $question->id = $questionid;
                // $question->status = 'completed';
                //$DB->update_record('generativeaiv2_questions', $question);
            }
        }
        generativeaiv2_display_results($cm->instance, $userid,$cm->id);
    }
}
else{

    // If no response was submitted, show the questions.

    if ($quiz_complete === 'complete') {
        // If the quiz is complete, display the results.
        generativeaiv2_display_results($cm->instance, $userid,$cm->id);
        echo html_writer::link(new moodle_url('/mod/generativeaiv2/view.php', ['id' => $cm->id]), '← Back to Home', ['class' => 'btn btn-primary']);
    } 
    elseif ($quiz_complete === 'incomplete') {
        // Display old question.
        echo "<h2>" . format_string($cm->name) . "</h2>";
        echo "<p>Welcome to the Generative AI Quiz on <strong>$topic</strong>!</p>";
        echo "<p>The following questions were previously generated. </p>";
        
        generativeaiv2_display_old_question($cm, $userid,$courseid);
        echo html_writer::link(new moodle_url('/mod/generativeaiv2/view.php', ['id' => $cm->id]), '← Back to Home', ['class' => 'btn btn-primary']);
    }
    elseif ($quiz_complete === 'generatenew') {
        // Display new question.
        echo "<h2>" . format_string($cm->name) . "</h2>";
        echo "<p>Welcome to the Generative AI Quiz on <strong>$topic</strong>!</p>";
        // echo "<p>Answer the questions based on your understanding.</p>";
            
        generativeaiv2_display_question($cm, $userid, $courseid);
        echo html_writer::link(new moodle_url('/mod/generativeaiv2/view.php', ['id' => $cm->id]), '← Back to Home', ['class' => 'btn btn-primary']);
        
    }elseif ($quiz_complete === 'redo') {

        // Display new question for wrongly answered.
        echo "<h2>" . format_string($cm->name) . "</h2>";
        echo "<p>Welcome to the Generative AI Quiz on <strong>$topic</strong>!</p>";
        // echo "<p>The following questions are generated from previous incorrect answers. Reattempt the questions based on your understanding.</p>";

        generativeaiv2_display_results($cm->instance, $userid,$cm->id);
        echo html_writer::link(new moodle_url('/mod/generativeaiv2/view.php', ['id' => $cm->id]), '← Back to Home', ['class' => 'btn btn-primary']);
        //generativeaiv2_redisplay_wrong_question($cm->instance, $userid);
    }
}
echo $OUTPUT->footer();
