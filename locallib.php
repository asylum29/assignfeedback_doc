<?php

defined('MOODLE_INTERNAL') || die();

define('ASSIGNFEEDBACK_DOC_FILEAREA', 'feedback_doc');

class assign_feedback_doc extends assign_feedback_plugin {

	public function get_name() {
		return get_string('pluginname', 'assignfeedback_doc');
    }
	
	private function get_feedback_doc($gradeid) {
        global $DB;
        return $DB->get_record('assignfeedback_doc', array('grade' => $gradeid));
    }
	
	private function count_files($gradeid, $area) {
		$fs = get_file_storage();
		$files = $fs->get_area_files($this->assignment->get_context()->id, 'assignfeedback_doc', $area, $gradeid, 'id', false);
        return count($files);
    }
	
	private function get_user_by_id($userid) {
		global $DB;
		return $DB->get_record('user', array('id' => $userid));
	}
	
	private function get_groups_string($userid) {
		global $DB;
		$groups = groups_get_all_groups($this->assignment->get_course()->id, $userid, false, 'g.id, g.name');
		if (empty($groups)) {
			$query = "SELECT c.id, c.name 
						FROM {cohort} c 
						JOIN {cohort_members} cm ON (cm.cohortid = c.id) 
					   WHERE cm.userid = ? AND c.visible = 1";
			$groups = $DB->get_records_sql($query, array($userid));
		}
		$groupnames = array();
		foreach ($groups as $group) {
			$groupnames[] = $group->name;
		}
		return count($groupnames) > 0 ? implode(', ', $groupnames) : '—';		
	}
	
	private function get_coursename() {
		$coursename = $this->get_config('coursename');
		if ($coursename == false) {
			$coursename = $this->assignment->get_course()->fullname;
		}
		return $coursename;
	}
	
	private function get_grade_string($grade) {
		global $CFG;
		
		$str_grade = get_string_manager()->get_string('nograde', '', null, $CFG->lang);
		if (is_numeric($grade->grade) && $grade->grade >= 0) {
			$str_grade = $this->assignment->display_grade($grade->grade, false, $grade->userid);
		}
		return $str_grade;
	}
	
	private function get_control_hash($grade, $commenttext, $commentformat) {
		$assignname = $this->assignment->get_instance()->name;
		$coursename = $this->get_coursename();
		$username = fullname($this->get_user_by_id($grade->userid));
		$groupsname = $this->get_groups_string($grade->userid);
		$gradername = fullname($this->get_user_by_id($grade->grader));
		$grade = $this->get_grade_string($grade);
		return sha1("/$assignname/$coursename/$username/$groupsname/$gradername/$grade/$commenttext/$commentformat");
	}
	
	private function get_filestring($grade, $commenttext, $commentformat = FORMAT_HTML) {
		global $CFG;
		
		$string_manager = get_string_manager();
		$user = $this->get_user_by_id($grade->userid);
		$grader = $this->get_user_by_id($grade->grader);
		
		$header = new stdClass();
		$header->assignname = mb_strtoupper($this->assignment->get_instance()->name, 'UTF-8');
		$header->coursename = mb_strtoupper($this->get_coursename(), 'UTF-8');
		$str_header = $string_manager->get_string('key1', 'assignfeedback_doc', $header, $CFG->lang);
		$str_feedback = $string_manager->get_string('key6', 'assignfeedback_doc', null, $CFG->lang);
		$str_group = $this->get_groups_string($user->id);		
		$str_grade = $this->get_grade_string($grade);
		
		$str_content = '
			<table class="report" cellspacing="0" cellpadding="3" border="1">
				<tr>
					<td width="160">' . $string_manager->get_string('key2', 'assignfeedback_doc', null, $CFG->lang) . '</td>
					<td width="500">' . fullname($user) . '</td>
				</tr>
				<tr>
					<td>' . $string_manager->get_string('key3', 'assignfeedback_doc', null, $CFG->lang) . '</td>
					<td>' . $str_group . '</td>
				</tr>
				<tr>
					<td>' . $string_manager->get_string('key4', 'assignfeedback_doc', null, $CFG->lang) . '</td>
					<td>' . fullname($grader) . '</td>
				</tr>
				<tr>
					<td>' . $string_manager->get_string('key5', 'assignfeedback_doc', null, $CFG->lang) . '</td>
					<td><span style="font-weight: bold; color: red;">' . $str_grade . '</span></td>
				</tr>
				<tr>
					<td colspan="2">
						<div style="text-align: center;"><b>' . mb_strtoupper($str_feedback, 'UTF-8') . '</b></div><br />
						<div class="feedback">' . format_text($commenttext, $commentformat, array('context' => $this->assignment->get_context())) . '</div>
					</td>
				</tr>
			</table>
		';

		$pathscript = get_config('assignfeedback_doc', 'script');
		$script = $pathscript ? '<script src="' . $pathscript . '"></script>' : '';
		
		$html = '
			<!DOCTYPE html>
			<html>
				<head>
					<meta charset="utf-8">
					<title>' . $str_feedback . '</title>
					' . $script . '
				</head>
				<body>
					<div style="width: 660px; margin: 25px auto;">
						<div class="header" style="font-weight: bold; text-align: center;">' . $str_header . '</div>
						<br />' . $str_content . '
					</div>
				</body>
			</html>
		';
		
		return $html;
	}
	
	private function save_feedback_file($grade, $commenttext, $commentformat = FORMAT_HTML) {
		global $CFG;
		
		$filestring = '';
		$fs = get_file_storage();
		$cm = $this->assignment->get_context();
		$user = $this->get_user_by_id($grade->userid);
		$grader = $this->get_user_by_id($grade->grader);
		$fs->delete_area_files($cm->id, 'assignfeedback_doc', ASSIGNFEEDBACK_DOC_FILEAREA, $grade->id);
		if ($commenttext) {
			$filename = get_string_manager()->get_string('key6', 'assignfeedback_doc', null, $CFG->lang) . '_' . $user->lastname . '.html';
			$filerecord = array(
				'contextid' => $cm->id, 
				'component' => 'assignfeedback_doc',  
				'filearea' 	=> ASSIGNFEEDBACK_DOC_FILEAREA,    
				'itemid' 	=> $grade->id,
				'filepath' 	=> '/', 
				'filename' 	=> $filename,
				'userid' 	=> $grader->id,
				'author' 	=> fullname($grader)
			);
			$filestring = $this->get_filestring($grade, $commenttext, $commentformat);
			$fs->create_file_from_string($filerecord, $filestring);
		}
		return $filestring;
	}
	
	public function get_form_elements_for_user($grade, MoodleQuickForm $mform, stdClass $data, $userid) {
		$feedback = false;
		if ($grade) {
			$feedback = $this->get_feedback_doc($grade->id);
		}
		if ($feedback && !empty($feedback->commenttext)) {
			$data->assignfeedbackdoc_editor['text'] = $feedback->commenttext;
			$data->assignfeedbackdoc_editor['format'] = $feedback->commentformat;
		}

		$mform->addElement('editor', 'assignfeedbackdoc_editor', $this->get_name(), null, null);
		
		return true;
	}
	
    public function is_feedback_modified(stdClass $grade, stdClass $data) {
		$newvalue = $data->assignfeedbackdoc_editor['text'];
		if ($grade) { // если оценка уже была, то обновляем в случае неравенства хешей
			$controlhash = '';
			$commentformat = $data->assignfeedbackdoc_editor['format'];
			$feedback = $this->get_feedback_doc($grade->id);
			if ($feedback) { // $grade может быть даже при первом оценивании, поэтому...
				$controlhash = $feedback->controlhash;
			} else if ($newvalue == '') { // ...если комментарий пустой и отзыва еще не было, то не обновляем
				return false;
			}
			return $this->get_control_hash($grade, $newvalue, $commentformat) != $controlhash;
		} else return $newvalue != ''; // если оценивание в первый раз, то обновляем в случае наличия комментария
    }	
	
	public function save(stdClass $grade, stdClass $data) {
		global $DB;
		
		$feedbackcomment = $data->assignfeedbackdoc_editor['text'];
		$feedbackformat = $data->assignfeedbackdoc_editor['format'];
		
		$feedback = $this->get_feedback_doc($grade->id);
		$this->save_feedback_file($grade, $feedbackcomment, $feedbackformat);		
        if ($feedback) {
            $feedback->commenttext = $feedbackcomment;
			$feedback->commentformat = $feedbackformat;
			$feedback->controlhash = $this->get_control_hash($grade, $feedbackcomment, $feedbackformat);
            return $DB->update_record('assignfeedback_doc', $feedback);
        } else {
            $feedback = new stdClass();
            $feedback->commenttext = $feedbackcomment;
			$feedback->commentformat = $feedbackformat;
			$feedback->controlhash = $this->get_control_hash($grade, $feedbackcomment, $feedbackformat);
            $feedback->grade = $grade->id;
            $feedback->assignment = $this->assignment->get_instance()->id;
            return $DB->insert_record('assignfeedback_doc', $feedback) > 0;
        }
    }
	
	public function supports_quickgrading() {
		return true;
	}	
	
	public function get_quickgrading_html($userid, $grade) {
		$commenttext = '';
		if ($grade) {
			$feedback = $this->get_feedback_doc($grade->id);
			if ($feedback) {
				$commenttext = $feedback->commenttext;
			}
		}

		$pluginname = get_string('pluginname', 'assignfeedback_doc');
		$labeloptions = array('for' => 'quickgrade_doc_' . $userid,
							  'class' => 'accesshide');
		$textareaoptions = array('name' => 'quickgrade_doc_' . $userid,
								 'id' => 'quickgrade_doc_' . $userid,
								 'class' => 'quickgrade');
        return html_writer::tag('label', $pluginname, $labeloptions) . html_writer::tag('textarea', $commenttext, $textareaoptions);
	}
	
	public function is_quickgrading_modified($userid, $grade) {
		$newvalue = optional_param('quickgrade_doc_' . $userid, false, PARAM_RAW);
		if ($newvalue === false) {
			return false; // если параметр отсутствовал, то не обновляем
		}
		if ($grade) { // если оценка уже была, то обновляем в случае неравенства хешей
			$controlhash = '';
			$commentformat = FORMAT_HTML;
			$feedback = $this->get_feedback_doc($grade->id);
			if ($feedback) {
				$controlhash = $feedback->controlhash;
				$commentformat = $feedback->commentformat;
			}
			return $this->get_control_hash($grade, $newvalue, $commentformat) != $controlhash;
		} else return $newvalue != ''; // если оценивание в первый раз, то обновляем в случае наличия комментария
    }
	
	public function save_quickgrading_changes($userid, $grade) {
		global $DB;

        $feedbackcomment = optional_param('quickgrade_doc_' . $userid, null, PARAM_RAW);	
		if (!$feedbackcomment) {
			return true;
		}
		
		$feedback = $this->get_feedback_doc($grade->id);		
		$this->save_feedback_file($grade, $feedbackcomment, $feedback ? $feedback->commentformat : FORMAT_HTML);	
		if ($feedback) {
			$feedback->commenttext = $feedbackcomment;
			$feedback->controlhash = $this->get_control_hash($grade, $feedbackcomment, $feedback->commentformat);
			return $DB->update_record('assignfeedback_doc', $feedback);
		} else {
			$feedback = new stdClass();
			$feedback->commenttext = $feedbackcomment;
			$feedback->commentformat = FORMAT_HTML;
			$feedback->controlhash = $this->get_control_hash($grade, $feedbackcomment, FORMAT_HTML);
			$feedback->grade = $grade->id;
			$feedback->assignment = $this->assignment->get_instance()->id;
			return $DB->insert_record('assignfeedback_doc', $feedback) > 0;
		}
	}
	
	public function get_settings(MoodleQuickForm $mform) {
		$mform->addElement('text', 'assignfeedback_doc_coursename', get_string('coursename', 'assignfeedback_doc'), array('size' => '50'));
		$mform->setType('assignfeedback_doc_coursename', PARAM_NOTAGS);	
		$mform->addHelpButton('assignfeedback_doc_coursename', 'coursename', 'assignfeedback_doc');
		$mform->setDefault('assignfeedback_doc_coursename', $this->get_coursename());
		$mform->disabledIf('assignfeedback_doc_coursename', 'assignfeedback_doc_enabled', 'notchecked');	
	}
	
	public function save_settings(stdClass $data) {
		$this->set_config('coursename', $data->assignfeedback_doc_coursename);
		return true;
	}
	
	public function view_summary(stdClass $grade, & $showviewlink) {
		return $this->view($grade);
    }
	
	public function view(stdClass $grade) {
		return $this->assignment->render_area_files('assignfeedback_doc', ASSIGNFEEDBACK_DOC_FILEAREA, $grade->id);    
    }
	
	public function is_empty(stdClass $grade) {
        return $this->count_files($grade->id, ASSIGNFEEDBACK_DOC_FILEAREA) == 0;
    }
	
	public function delete_instance() {
        global $DB;		
        $DB->delete_records('assignfeedback_doc', array('assignment' => $this->assignment->get_instance()->id));
        return true;
    }
	
	public function get_file_areas() {
        return array(ASSIGNFEEDBACK_DOC_FILEAREA => $this->get_name());                                                              
	}      
	
	public function format_for_gradebook(stdClass $grade) {
		$feedback = $this->get_feedback_doc($grade->id);
		return $feedback ? $feedback->commentformat : FORMAT_MOODLE;
	}
	
	public function text_for_gradebook(stdClass $grade) {
		$feedback = $this->get_feedback_doc($grade->id);
		return $feedback ? $feedback->commenttext : '';
	}
	
}