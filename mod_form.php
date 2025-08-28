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
 * The main mod_generativeaiv2 configuration form.
 *
 * @package     mod_generativeaiv2
 * @copyright   2024 Ken M. Mbabu <kencerm08@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

/**
 * Module instance settings form.
 *
 * @package     mod_generativeaiv2
 * @copyright   2024 Ken M. Mbabu <kencerm08@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_generativeaiv2_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $CFG;

        $mform = $this->_form;

        // $mform->addElement('static', 'pluginicon', '', html_writer::empty_tag('img', array('src' => $CFG->wwwroot . '/mod/generativeaiv2/pix/icon.png', 'alt' => 'Generative AI Icon')));

        // Adding the "general" fieldset, where all the common settings are shown.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field.
        $mform->addElement('text', 'name', get_string('generativeaiv2name', 'mod_generativeaiv2'), array('size' => '64'));

        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }

        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('name', 'generativeaiv2name', 'mod_generativeaiv2');

        // Adding the standard "intro" and "introformat" fields.
        if ($CFG->branch >= 29) {
            $this->standard_intro_elements();
        } else {
            $this->add_intro_editor();
        }

        // Adding the rest of mod_generativeaiv2 settings, spreading all them into this fieldset
        // ... or adding more fieldsets ('header' elements) if needed for better logic.
        $mform->addElement('static', 'label1', 'generativeaiv2settings', get_string('generativeaiv2settings', 'mod_generativeaiv2'));
        $mform->addElement('header', 'generativeaiv2fieldset', get_string('generativeaiv2fieldset', 'mod_generativeaiv2'));

        // Field to set the quiz topic
        $mform->addElement('text', 'topic', get_string('topic', 'mod_generativeaiv2'), array('size' => '200'));
        $mform->addRule('topic', get_string('required'), 'required', null, 'client');
        $mform->setType('topic', PARAM_TEXT);
        $mform->addHelpButton('topic', 'topic', 'mod_generativeaiv2'); // Adds a help button for context

        // Field to set the starting difficulty level (1, 2, or 3 as options)
        $difficulty_options = array(
            1 => get_string('easy', 'mod_generativeaiv2'),
            2 => get_string('medium', 'mod_generativeaiv2'),
            3 => get_string('hard', 'mod_generativeaiv2')
        );
        $mform->addElement('select', 'start_difficulty', get_string('startdifficulty', 'mod_generativeaiv2'), $difficulty_options);
        $mform->setDefault('start_difficulty', 1);
        $mform->addHelpButton('start_difficulty', 'startdifficulty', 'mod_generativeaiv2');

        // Field to set the context level (1, 2, or 3 as options)
        $context_options = array(
            '4th Year' => get_string('year4', 'mod_generativeaiv2'),
            '3rd Year' => get_string('year3', 'mod_generativeaiv2'),
            '2nd Year' => get_string('year2', 'mod_generativeaiv2'),
            '1st Year' => get_string('year1', 'mod_generativeaiv2'),
            'Grade 12' => get_string('grade12', 'mod_generativeaiv2'),
            'Grade 11' => get_string('grade11', 'mod_generativeaiv2'),
            'Grade 10' => get_string('grade10', 'mod_generativeaiv2'),
            'Grade 9' => get_string('grade9', 'mod_generativeaiv2'),
            'Grade 8' => get_string('grade8', 'mod_generativeaiv2'),
            'Grade 7' => get_string('grade7', 'mod_generativeaiv2'),
            'Grade 6' => get_string('grade6', 'mod_generativeaiv2'),
            'Grade 5' => get_string('grade5', 'mod_generativeaiv2'),
            'Grade 4' => get_string('grade4', 'mod_generativeaiv2'),
            'Grade 3' => get_string('grade3', 'mod_generativeaiv2'),
            'Grade 2' => get_string('grade2', 'mod_generativeaiv2'),
            'Grade 1' => get_string('grade1', 'mod_generativeaiv2'),            
        );
        $mform->addElement('select', 'context_level', get_string('contextlevel', 'mod_generativeaiv2'), $context_options);
        $mform->setDefault('context_level', 1);
        $mform->addHelpButton('context_level', 'contextlevel', 'mod_generativeaiv2');

        //contextinfo
        $mform->addElement('text', 'contextinfo', get_string('contextinfo', 'mod_generativeaiv2'), array('size' => '200'));
        $mform->setType('contextinfo', PARAM_TEXT);
        $mform->addHelpButton('contextinfo', 'contextinfo', 'mod_generativeaiv2'); // Adds a help button for context

        // Optional field to specify additional information from the lecturer
        $mform->addElement('textarea', 'additional_info', get_string('additionalinfo', 'mod_generativeaiv2'));
        $mform->addHelpButton('additional_info', 'additionalinfo', 'mod_generativeaiv2');

        // Optional field to specify the number of questions (maximum)
        $mform->addElement('text', 'max_questions', get_string('maxquestions', 'mod_generativeaiv2'), array('size' => '4'));
        $mform->setType('max_questions', PARAM_INT);
        $mform->setDefault('max_questions', 10);
        $mform->addHelpButton('max_questions', 'maxquestions', 'mod_generativeaiv2');

        // Optional field to specify the time limit to do the questions
        $mform->addElement('text', 'time_limit', get_string('timelimit', 'mod_generativeaiv2'), array('size' => '4'));
        $mform->setType('time_limit', PARAM_FLOAT);
        $mform->setDefault('time_limit', 5);
        $mform->addHelpButton('time_limit', 'timelimit', 'mod_generativeaiv2');
        $mform->addRule('time_limit', null, 'numeric', null, 'client');
        // $mform->addElement('text', 'time_limit', get_string('timelimit', 'mod_generativeaiv2'), array('size' => '4'));
        // $mform->setType('time_limit', PARAM_INT);
        // $mform->setDefault('time_limit', 5);
        // $mform->addHelpButton('time_limit', 'timelimit', 'mod_generativeaiv2');

        // Field to set the API KEY
        $mform->addElement('text', 'api_key', get_string('apikey', 'mod_generativeaiv2'), array('size' => '300'));
        $mform->setType('api_key', PARAM_TEXT);
        $mform->addHelpButton('api_key', 'apikey', 'mod_generativeaiv2'); // Adds a help button for context
        // Field to set the starting difficulty level (1, 2, or 3 as options)
        $model_options = array(
            'gpt-3.5-turbo' => get_string('gpt-3.5-turbo', 'mod_generativeaiv2'),
            'gpt-4-turbo' => get_string('gpt-4-turbo', 'mod_generativeaiv2'),
        );
        $mform->addElement('select', 'model_options', get_string('modeloptions', 'mod_generativeaiv2'), $model_options);
        $mform->setDefault('model_options', 1);
        $mform->addHelpButton('model_options', 'modeloptions', 'mod_generativeaiv2');

        // Add standard grading elements.
        //$this->standard_grading_coursemodule_elements();

        // Add standard elements.
        $this->standard_coursemodule_elements();

        // Add standard buttons.
        $this->add_action_buttons();
    }
}
