<?php
require_once('../../config.php');
require_once($CFG->dirroot . '/mod/generativeaiv2/classes/classes.php');
global $DB, $USER;

$quizid = required_param('quizid', PARAM_INT);
$answers = json_decode(required_param('answers', PARAM_RAW), true);  // Decode the answers array
// $answers = required_param_array('answers', PARAM_RAW);

if(count($answers)!==0 ){
    foreach ($answers as $questionid => $user_answer) {
        // Check that the answer exists in the submitted data
        if ($user_answer !== null) {
            // Retrieve the question from the database
            $question = $DB->get_record('generativeaiv2_questions', ['id' => $questionid], '*', MUST_EXIST);
            
            // Determine if the answer is correct
            $is_correct = ($user_answer == (int)$question->correct_answer) ? 1 : 0;

            // Get the current difficulty level for this user
            $user_progress = $DB->get_record('generativeaiv2_user_response', ['userid' => $USER->id, 'quizid' => $quizid, 'questionid' => $questionid]);
            $current_difficulty = $user_progress ? $user_progress->difficulty : 1;

            // Adjust difficulty based on answer correctness
            $new_difficulty = generativeaiv2_next_difficulty($USER->id, $current_difficulty, $is_correct);

            // Insert or update the record in the database
            $data = new stdClass();
            $data->userid = $USER->id;
            $data->quizid = $quizid;
            $data->questionid = $questionid;
            $data->response = $user_answer;
            $data->correct = $is_correct;
            $data->difficulty = $new_difficulty;

            // Insert or update the record in the database
            $DB->insert_record('generativeaiv2_user_response', $data, true); // Insert or update
        }
    }
}
else
{
    $questionid = 0;
    $user_answer = 0;
    $is_correct = 0;
    $new_difficulty = 1;

    // Insert or update the record in the database
    $data = new stdClass();
    $data->userid = $USER->id;
    $data->quizid = $quizid;
    $data->questionid = $questionid;
    $data->response = $user_answer;
    $data->correct = $is_correct;
    $data->difficulty = $new_difficulty;

    // Insert or update the record in the database
    $DB->insert_record('generativeaiv2_user_response', $data, true); // Insert or update
}

// Return results via AJAX
ob_start();
generativeaiv2_display_results($quizid, $USER->id, $cm->id);  // Generate results
$output = ob_get_clean();

echo json_encode(['success' => true, 'html' => $output]);
exit;
?>