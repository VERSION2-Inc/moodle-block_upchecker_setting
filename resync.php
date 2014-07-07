<?php
namespace upchecker;

require_once '../../config.php';
require_once $CFG->dirroot . '/blocks/upchecker_setting/locallib.php';
require_once $CFG->libdir . '/formslib.php';
require_once $CFG->dirroot . '/mod/quiz/locallib.php';
require_once $CFG->dirroot . '/question/engine/datalib.php';

class page_resync extends page {
	/**
	 *
	 * @var form_resync
	 */
	private $resyncform;

	public function execute() {
		$this->set_page_title(setting::str('resyncfiles'));

		$this->resyncform = new form_resync(null, (object)['courseid' => $this->courseid]);

		if ($this->resyncform->is_submitted()) {
			$this->resync_files();
		}
		$this->view();
	}

	private function view() {
		echo $this->output->header();
		echo $this->output->heading(setting::str('resyncfiles'));

		if ($message = optional_param('message', '', PARAM_ALPHA)) {
			echo $this->output->notification(setting::str($message), 'notifysuccess');
		}

		$this->resyncform->display();

		echo $this->output->footer();
	}

	private function resync_files() {
		global $DB;

		$data = $this->resyncform->get_data();

		list($quizid, $slot) = explode('_', $data->question);

		$attempts = $DB->get_records('quiz_attempts', ['quiz' => $quizid]);

		foreach ($attempts as $attempt) {
			$quba = \question_engine::load_questions_usage_by_activity($attempt->uniqueid);
			/* @var $question \qtype_upchecker_question */
			$question = $quba->get_question($slot);
			$question->resyncfileuser = $DB->get_record('user', ['id' => $attempt->userid]);
			if ($file = $question->get_uploaded_file()) {
				$question->store_file($file);

			}
		}

		redirect(new \moodle_url($this->url, ['course' => $this->courseid, 'message' => 'syncedfiles']));
	}
}

class form_resync extends \moodleform {
	protected function definition() {
		global $DB;

		$f = $this->_form;

		$f->addElement('hidden', 'course', $this->_customdata->courseid);
		$f->setType('course', PARAM_INT);

		$f->addElement('header', 'resync', setting::str('resyncfiles'));

		$courseid = $this->_customdata->courseid;
		$quizzes = get_coursemodules_in_course('quiz', $courseid, 'm.questions');

		/* @var $questionsel \MoodleQuickForm_selectgroups */
		$questionsel = $f->createElement('selectgroups', 'question', get_string('pluginname', 'qtype_upchecker'));
		foreach ($quizzes as $quiz) {
// 			echo "$quiz->id:$quiz->name<br>";
			if ($quiz->questions != '') {
				$questionids = explode(',', quiz_questions_in_quiz($quiz->questions));
				$opts = null;
				foreach ($questionids as $questionid) {
					$question = $DB->get_record('question', ['id' => $questionid]);
					if ($question->qtype == 'upchecker') {
						$value = $quiz->instance . '_' . quiz_get_slot_for_question($quiz, $question->id);
						$opts[$value] = $question->name;
					}
				}
				if ($opts) {
					$questionsel->addOptGroup($quiz->name, $opts);
				}
			}
		}
		$f->addElement($questionsel);

		$this->add_action_buttons(false, setting::str('resyncfiles'));
	}
}

$page = new page_resync('/blocks/upchecker_setting/resync.php');
$page->execute();
