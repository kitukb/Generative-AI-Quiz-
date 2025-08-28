<?php
require_once('../../config.php');

global $DB;

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="questions.csv"');

$output = fopen('php://output', 'w');

// Fetch data
$records = $DB->get_records('generativeaiv2_questions');

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

