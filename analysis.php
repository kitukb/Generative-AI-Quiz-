<?php
require_once('../../config.php');
require_once('classes/classes.php');

require_login();

$PAGE->set_url(new moodle_url('/mod/generativeaiv2/analysis.php'));
$PAGE->set_context(context_system::instance());

generativeaiv2_analysis_page();