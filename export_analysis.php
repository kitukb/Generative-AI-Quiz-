<?php
require_once('../../config.php'); // Adjust the path to your Moodle config if needed

global $DB;

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="responses.csv"');

$output = fopen('php://output', 'w');

// Fetch data
$sql = "
     SELECT u.firstname, u.lastname, u.email, m.name,
                COUNT(q.id) AS total_questions,
                SUM(CASE WHEN r.correct = 1 THEN 1 ELSE 0 END) AS correct_answers,
                SUM(CASE WHEN r.correct = 0 THEN 1 ELSE 0 END) AS failed_answers
        FROM {generativeaiv2_questions} q
        JOIN {generativeaiv2_user_response} r ON q.id = r.questionid
        JOIN {user} u ON r.userid = u.id
        JOIN {generativeaiv2} m on q.quizid = m.id
        GROUP BY u.id, r.quizid, u.firstname, u.lastname, u.email, m.name
        ORDER BY u.lastname ASC, u.firstname ASC
    ";

$records = $DB->get_records_sql($sql);

//$records = $DB->get_records('generativeaiv2_user_response');

// Output headers
if (!empty($records)) {
    $first = reset($records);
    fputcsv($output, array_keys((array)$first));
}

// Output rows
foreach ($records as $record) {
    fputcsv($output, (array)$record);
}

fclose($output);
exit;
