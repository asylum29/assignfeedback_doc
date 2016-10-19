<?php

defined('MOODLE_INTERNAL') || die();

function xmldb_assignfeedback_doc_install() {
    global $CFG;
    require_once($CFG->dirroot . '/mod/assign/adminlib.php');

    $pluginmanager = new assign_plugin_manager('assignfeedback');
    $pluginmanager->move_plugin('doc', 'down');

    return true;
}