<?php

$settings->add(new admin_setting_configcheckbox('assignfeedback_doc/default',
                   get_string('default', 'assignfeedback_doc'),
                   get_string('default_help', 'assignfeedback_doc'), 1));
				   
$settings->add(new admin_setting_configtext('assignfeedback_doc/script',
				   get_string('script', 'assignfeedback_doc'), 
				   get_string('script_help', 'assignfeedback_doc'), '', PARAM_URL));