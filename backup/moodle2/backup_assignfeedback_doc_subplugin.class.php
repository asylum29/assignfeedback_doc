<?php

defined('MOODLE_INTERNAL') || die();

class backup_assignfeedback_doc_subplugin extends backup_subplugin {

    protected function define_grade_subplugin_structure() {

        $subplugin = $this->get_subplugin_element();
        $subpluginwrapper = new backup_nested_element($this->get_recommended_name());
        $subpluginelement = new backup_nested_element('feedback_doc', null, array('commenttext', 'commentformat', 'controlhash', 'grade'));

        $subplugin->add_child($subpluginwrapper);
        $subpluginwrapper->add_child($subpluginelement);

        $subpluginelement->set_source_table('assignfeedback_doc', array('grade' => backup::VAR_PARENTID));
        $subpluginelement->annotate_files('assignfeedback_doc', 'feedback_doc', 'grade');
        return $subplugin;
    }

}
