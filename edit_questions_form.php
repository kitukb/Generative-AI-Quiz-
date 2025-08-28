<?php
require_once("$CFG->libdir/formslib.php");

class edit_questions_form extends moodleform {
    public function definition() {
        global $DB;

        $mform = $this->_form;
        $customdata = $this->_customdata;
        $id = $customdata['id'];

        $mform->addElement('hidden', 'id', $id->id);
        $mform->setType('id', PARAM_INT);

        $sql = "SELECT * FROM {generativeaiv2_questions}
                WHERE quizid = :quizid AND approval_status = 0 AND status = :status";
        $params = [
            'quizid' => $id->instance,
            'status' => 'lecturer'
        ];
        $questions = $DB->get_records_sql($sql, $params);

        foreach ($questions as $question) {
            $qid = $question->id;
            $choices = [
                $question->choice1,
                $question->choice2,
                $question->choice3,
                $question->choice4,
            ];

            // 🧱 Start block
            $mform->addElement('html', '<div id="question-block-' . $qid . '" style="border:1px solid #ddd; padding:10px; margin-bottom:15px;">');

            // Question Editor
            $mform->addElement('editor', "question_text[{$qid}]", 'Question Text Editing');
            $mform->setType("question_text[{$qid}]", PARAM_RAW);
            $mform->setDefault("question_text[{$qid}]", array('text' => $question->question_text, 'format' => FORMAT_HTML));

            // Choices
            $mform->addElement('html', '<div id="choices-container-' . $qid . '">');
            foreach ($choices as $index => $choice) {
                $mform->addElement('text', "choices[{$qid}][{$index}]", 'Choice ' . ($index + 1));
                $mform->setType("choices[{$qid}][{$index}]", PARAM_TEXT);
                $mform->setDefault("choices[{$qid}][{$index}]", $choice);
            }
            $mform->addElement('html', '</div>');

            // Correct Answer
            $options = [];
            foreach ($choices as $index => $choice) {
                if (!empty($choice)) {
                    $options[$index + 1] = 'Choice ' . ($index + 1);
                }
            }
            $mform->addElement('select', "correct_answer[{$qid}]", 'Correct Answer', $options);
            $mform->setDefault("correct_answer[{$qid}]", $question->correct_answer);

            // 🚨 Delete Button
            $mform->addElement('html', '<button type="button" class="btn btn-danger" onclick="deleteQuestionAJAX(' . $qid . ')">Delete Question</button>');

            // End block
            $mform->addElement('html', '</div>');
        }

        // Submit button
        $mform->addElement('submit', 'update_answers', 'Save Changes');

        // 🔌 Add AJAX script
        $mform->addElement('html', '
            <script>
                function deleteQuestionAJAX(questionid) {
                    if (!confirm("Are you sure you want to delete this question?")) return;

                    fetch("ajax.php?questionid=" + questionid + "&sesskey=' . sesskey() . '")
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === "success") {
                                document.getElementById("question-block-" + questionid).remove();
                            } else {
                                alert("Error: " + data.message);
                            }
                        })
                        .catch(err => {
                            alert("AJAX error. Could not delete.");
                        });
                }
            </script>
        ');
    }
}


// class edit_questions_form extends moodleform {
//     public function definition() {
//         global $DB;

//         $mform = $this->_form;
//         $customdata = $this->_customdata; // Get custom data
//         $id = $customdata['id']; // Extract single value

//         $mform->addElement('hidden', 'id', $id);
//         $mform->setType('id', PARAM_INT);

//         $questions = $DB->get_records('generativeaiv2_questions');

//         foreach ($questions as $question) {
//             // Question Text Editor
//             $mform->addElement('editor', "question_text[{$question->id}]", 'Question Text');
//             $mform->setType("question_text[{$question->id}]", PARAM_RAW);
//             $mform->setDefault("question_text[{$question->id}]", array('text' => $question->question_text, 'format' => FORMAT_HTML));

//             // Choices Container (Wrapped in a div)
//             $mform->addElement('html', '<div id="choices-container-' . $question->id . '">');

//             // Load Existing Choices
//             $choices = [
//                 $question->choice1,
//                 $question->choice2,
//                 $question->choice3,
//                 $question->choice4,
//             ];

//             foreach ($choices as $index => $choice) {
//                 $mform->addElement('text', "choices[{$question->id}][{$index}]", 'Choice '. ' ' . ($index + 1));
//                 $mform->setType("choices[{$question->id}][{$index}]", PARAM_TEXT);
//                 $mform->setDefault("choices[{$question->id}][{$index}]", $choice);
//             }

//             $mform->addElement('html', '</div><br>');
           
//             // Buttons to Add/Remove Choices
//             $mform->addElement('html', '
//                 <button type="button" onclick="addChoice(' . $question->id . ')">+ Add Choice</button>
//                 <button type="button" onclick="removeChoice(' . $question->id . ')">- Remove Choice</button><br><br>
//             ');

//             // Correct Answer Dropdown
//             $options = [];
//             foreach ($choices as $index => $choice) {
//                 if (!empty($choice)) {
//                     $options[$index + 1] = 'Choice ' . ($index + 1);
//                 }
//             }

//             $mform->addElement('select', "correct_answer[{$question->id}]", 'Correct Answer', $options);
//             $mform->setDefault("correct_answer[{$question->id}]", $question->correct_answer);

//             $mform->addElement('html', '<hr>');
//         }
//         // echo '<button type="submit" name="update_answers">Save Changes</button>';
//         $mform->addElement('submit', 'update_answers', 'Save Changes');
//     }
// }

//old code

// require_once("$CFG->libdir/formslib.php");

// class edit_questions_form extends moodleform {
//     public function definition() {
//         global $DB;

//         $mform = $this->_form;
//         $customdata = $this->_customdata; // Get custom data
//         $id = $customdata['id']; // Extract single value

//         $mform->addElement('hidden', 'id', $id->id);
//         $mform->setType('id', PARAM_INT);
        
//         // $questions = $DB->get_records('generativeaiv2_questions');
//         $sql = "SELECT * FROM {generativeaiv2_questions}
//         WHERE quizid = :quizid AND approval_status = 0 AND status = :status";
//         $params = [
//             'quizid' => $id->instance,
//             'status' => 'lecturer'
//         ];
//         $questions = $DB->get_records_sql($sql, $params);

//         foreach ($questions as $question) {
//             // Question Text (With Moodle Editor)
//             $mform->addElement('editor', "question_text[{$question->id}]", 'Question Text Editing');
//             $mform->setType("question_text[{$question->id}]", PARAM_RAW);
//             $mform->setDefault("question_text[{$question->id}]", array('text' => $question->question_text, 'format' => FORMAT_HTML));

//             // Choices Container
//             $choices = [
//                 $question->choice1,
//                 $question->choice2,
//                 $question->choice3,
//                 $question->choice4,
//             ];

//             $mform->addElement('html', '<div id="choices-container-' . $question->id . '">');
//             foreach ($choices as $index => $choice) {
//                 $mform->addElement('text', "choices[{$question->id}][{$index}]", 'Choice '. ' ' . ($index + 1));
//                 $mform->setType("choices[{$question->id}][{$index}]", PARAM_TEXT);
//                 $mform->setDefault("choices[{$question->id}][{$index}]", $choice);
//             }
//             $mform->addElement('html', '</div>');

//             // Correct Answer Selection
//             $options = [];
//             foreach ($choices as $index => $choice) {
//                 if (!empty($choice)) {
//                     $options[$index + 1] = 'Choice ' . ($index + 1);
//                 }
//             }
//             $mform->addElement('select', "correct_answer[{$question->id}]", 'Select correct answer', $options);
//             $mform->setDefault("correct_answer[{$question->id}]", $question->correct_answer);

//             // Delete button (submit type with unique name)
//             $buttonname = 'delete_question_' . $question->id;
//             $mform->addElement('submit', $buttonname, 'Delete Question', ['class' => 'btn btn-danger', 'onclick' => "return confirm('Are you sure you want to delete this question?')"]);


//             $mform->addElement('html', '<hr>');
//         }

//         $mform->addElement('submit', 'update_answers', 'Save Changes');
//     }
// }