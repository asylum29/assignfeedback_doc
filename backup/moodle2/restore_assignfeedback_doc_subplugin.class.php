<?php

defined('MOODLE_INTERNAL') || die();

class restore_assignfeedback_doc_subplugin extends restore_subplugin {

    protected function define_grade_subplugin_structure() {

        $paths = array();

        $elename = $this->get_namefor('grade');
        $elepath = $this->get_pathfor('/feedback_doc');
        $paths[] = new restore_path_element($elename, $elepath);

        return $paths;
    }

    public function process_assignfeedback_doc_grade($data) {
        global $DB;

        $data = (object)$data;
        $data->assignment = $this->get_new_parentid('assign');
        $oldgradeid = $data->grade;
        $data->grade = $this->get_mappingid('grade', $data->grade);

        $DB->insert_record('assignfeedback_doc', $data);

        $this->add_related_files('assignfeedback_doc', 'feedback_doc', 'grade', null, $oldgradeid);
    }

}
