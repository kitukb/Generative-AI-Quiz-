<?php
/**
 * Generate a question using OpenAI's API based on the specified topic and difficulty.
 *
 * @param string $topic The subject or topic for the question.
 * @param int $difficulty The difficulty level (e.g., 1 for easy, 2 for medium, 3 for hard).
 * @return array An associative array containing the question text and options if available.
 */
function generativeaiv2_generate_question($topic, $difficulty) {
    // Define your OpenAI API URL
    $api_url = 'https://api.openai.com/v1/engines/gpt-3.5-turbo/completions';
    
    // Set up the headers with your OpenAI API key (replace with actual key securely)
    $headers = [
        'Authorization: Bearer ' . OPENAI_API_KEY, // Define OPENAI_API_KEY in config.php or secure location
        'Content-Type: application/json'
    ];

    // Generate prompt based on the topic and difficulty level
    $difficulty_text = ($difficulty == 1) ? "easy" : (($difficulty == 2) ? "medium" : "hard");
    $prompt = "Generate a $difficulty_text question on the topic of $topic with 4 multiple-choice options and indicate the correct answer.";


    //  // Define data for API request
    //  $data = [
    //     'model' => 'gpt-3.5-turbo',
    //     'messages' => [
    //         ['role' => 'user', 'content' => $prompt]
    //     ],
    //     'max_tokens' => 150
    // ];

    // Define the prompt and request payload
        // $prompt = "Generate a $difficulty question on the topic of $topic. Provide a multiple-choice question with 4 answer options and indicate the correct answer.";
    $data = [
        'prompt' => $prompt,
        'max_tokens' => 150, // Adjust based on question length needs
        'temperature' => 0.7, // Creativity level (0-1)
        'n' => 1, // Only one question at a time
        'stop' => ["\n"]
    ];

    // Set up the cURL request to OpenAI
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    
    // Execute the request and handle the response
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
        curl_close($ch);
        return null;
    }
    curl_close($ch);

    // Decode the response from OpenAI
    $result = json_decode($response, true);
    
    // Parse the question and options from the API response
    $question_text = $result['choices'][0]['text'] ?? 'No question generated.';
    $options = generativeaiv2_parse_question_options($question_text);
    
    return [
        'text' => $question_text,
        'options' => $options
    ];
}

/**
 * Parses generated question text to extract answer options.
 *
 * @param string $question_text The generated question text from OpenAI.
 * @return array An array of answer options parsed from the question text.
 */
function generativeaiv2_parse_question_options($question_text) {
    // Example parsing logic - customize to fit your needs
    $options = [];
    $pattern = '/\d+\.\s*(.*?)(?:$|\n)/'; // Regular expression to match options in format "1. Option"
    preg_match_all($pattern, $question_text, $matches);
    
    if (!empty($matches[1])) {
        $options = $matches[1];
    }
    
    return $options;
}

/**
 * This function, generativeaiv2_display_question, will generate a question using ChatGPT, render it to the user, and save the question in the database.
 */
function generativeaiv2_display_question($userid, $quizid, $current_difficulty) {
    global $DB;
    
    // Generate the question using ChatGPT
    $question_data = generativeaiv2_generate_question("Algebra", $current_difficulty);
    $question_text = $question_data['choices'][0]['text']; // Assuming GPT's response format

    // Prepare question options (assuming multiple-choice format)
    $options = ['A', 'B', 'C', 'D']; // Placeholder options
    $correct_option = array_rand($options); // Random correct option for demo purposes
    
    // Render the question text and options to the page
    echo "<div class='question'><strong>Question:</strong> $question_text</div>";
    foreach ($options as $key => $option) {
        $checked = $key === $correct_option ? 'correct' : '';
        echo "<div class='option'><input type='radio' name='answer' value='{$key}' /> Option $option $checked</div>";
    }

    // Save the question data to the database
    $question_record = new stdClass();
    $question_record->userid = $userid;
    $question_record->quizid = $quizid;
    $question_record->question_text = $question_text;
    $question_record->difficulty = $current_difficulty;
    $question_record->correct_option = $correct_option;

    $DB->insert_record('generativeaiv2_user_responses', $question_record);
}

/**
 * This function, generativeaiv2_next_difficulty, evaluates whether the user’s response was correct and adjusts the difficulty level accordingly.
 */
function generativeaiv2_next_difficulty($userid, $current_difficulty, $is_correct) {
    // Increment difficulty if correct, otherwise decrease
    // Max difficulty of 3
    // Min difficulty of 1
    return $is_correct ? min($current_difficulty + 1, 3) : max($current_difficulty - 1, 1);
    
}

/**
 * After each question, we process the student’s answer, check if it’s correct, adjust difficulty, and call the next question.
 * Fetch the stored correct answer from the database.
 * Compare it with the student’s selected option.
 * If correct, increment the difficulty level; if incorrect, we decrease it.
 * Finally, Call generativeaiv2_display_question to load the next question with the new difficulty level.
 */

function generativeaiv2_process_response($userid, $quizid, $questionid, $selected_option) {
    global $DB;
    
    // Retrieve the stored correct answer from the database
    $question_record = $DB->get_record('generativeaiv2_user_responses', [
        'id' => $questionid,
        'userid' => $userid,
        'quizid' => $quizid
    ]);

    // Determine if the answer is correct
    $is_correct = ($selected_option == $question_record->correct_option);

    // Update the record with user's response and correctness
    $question_record->response = $selected_option;
    $question_record->correct = $is_correct ? 1 : 0;
    $DB->update_record('generativeaiv2_user_responses', $question_record);

    // Adjust the difficulty for the next question
    $next_difficulty = generativeaiv2_next_difficulty($userid, $question_record->difficulty, $is_correct);

    // Generate and display the next question with the new difficulty
    generativeaiv2_display_question($userid, $quizid, $next_difficulty);
}

// curl https://api.openai.com/v1/completions \
//   -H "Content-Type: application/json" \
//   -H "Authorization: Bearer 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" \
//   -d '{
//   "model": "gpt-3.5-turbo",
//   "prompt": "Generate a question",
//   "max_tokens": 5
// }'

echo "<h5>{$question['text']}</h5>
    <form method='POST' action=''>";
        foreach ($question['options'] as $option):
            echo "<label>
                <input type='radio' name='answer' value='{$option}'> {$option}
            </label><br>";
        endforeach;
        echo '<button type="submit">Submit Answer</button></form>';

    //Handle Form Submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Retrieve the user's answer
        $selectedAnswer = $_POST['answer'] ?? '';

        //Check if the answer is correct and display feedback
        if ($selectedAnswer === $questionData['correct_answer']) {
            echo "<p style='color: green;'>Correct! Well done.</p>";
        } else {
            echo "<p style='color: red;'>Incorrect. The correct answer is: {$questionData['correct_answer']}</p>";
        }
    }