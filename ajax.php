<?php
define('AJAX_SCRIPT', true);
require('../../config.php');

require_sesskey();
global $DB;

header('Content-Type: application/json');

$questionid = required_param('questionid', PARAM_INT);

try {
    if ($DB->record_exists('generativeaiv2_questions', ['id' => $questionid])) {
        $DB->delete_records('generativeaiv2_questions', ['id' => $questionid]);
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Question not found']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
