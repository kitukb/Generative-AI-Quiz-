<?php
require_once('../../config.php');
global $DB, $USER;

$quizid = required_param('quizid', PARAM_INT);
$qindex = required_param('qindex', PARAM_INT);

$sql = "SELECT * FROM {generativeaiv2_questions} WHERE quizid = :quizid ORDER BY id ASC";
$params = ['quizid' => $quizid];

$questions = array_values($DB->get_records_sql($sql, $params));
$totalQuestions = count($questions);

if ($totalQuestions > 0 && isset($questions[$qindex])) {
    $question = $questions[$qindex];

    $response = [
        'question_text' => $question->question_text,
        'choices' => [
            $question->choice1,
            $question->choice2,
            $question->choice3,
            $question->choice4,
        ],
        'qindex' => $qindex,
        'totalQuestions' => $totalQuestions,
        'question_id' => $question->id
    ];
    echo json_encode($response);
} else {
    echo json_encode(['error' => 'No more questions available.']);
}
exit;