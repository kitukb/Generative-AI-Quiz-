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
 * Plugin administration pages are defined here.
 *
 * @package     mod_generativeaiv2
 * @category    admin
 * @copyright   2024 Ken M. Mbabu <kencerm08@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('mod_generativeaiv2_settings', new lang_string('pluginname', 'mod_generativeaiv2'));

    // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedIf
    
    if ($ADMIN->fulltree) {
        $settings->add(new admin_setting_configtext(
            'mod_generativeaiv2/apikey',
            get_string('apikey', 'mod_generativeaiv2'),
            get_string('apikey_desc', 'mod_generativeaiv2'),
            '',
            PARAM_TEXT
        ));
    }
    
    $ADMIN->add('modsettings', new admin_externalpage(
        'modgenerativeaiv2_analysis',
        'Quiz Analysis Report',
        new moodle_url('/mod/generativeaiv2/analysis.php')
    ));
}
