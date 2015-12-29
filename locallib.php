<?php
/**
 * Settings block of programming question type for Moodle
 *
 * @package    block_upchecker_setting
 * @subpackage upchecker
 * @copyright  VERSION2, Inc.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace upchecker;

class setting {
	const COMPONENT = 'block_upchecker_setting';

	/**
	 *
	 * @param string $identifier
	 * @param string|\stdClass $a
	 * @return string
	 */
	public static function str($identifier, $a = null) {
		return get_string($identifier, self::COMPONENT, $a);
	}
}

abstract class page {
	/**
	 *
	 * @var \moodle_url
	 */
	protected $url;
	/**
	 *
	 * @var \core_renderer
	 */
	protected $output;
	/**
	 *
	 * @var int
	 */
	protected $courseid;
	/**
	 *
	 * @var \stdClass
	 */
	protected $course;

	/**
	 *
	 * @param string $url
	 */
	public function __construct($url) {
		global $OUTPUT, $PAGE, $DB;

		$this->url = new \moodle_url($url, ['course' => $this->courseid]);
		$this->output = $OUTPUT;

		$this->courseid = required_param('course', PARAM_INT);
		$this->course = $DB->get_record('course', ['id' => $this->courseid], 'id, fullname, shortname', MUST_EXIST);
		require_login($this->courseid);

		require_capability('block/upchecker_setting:managestorageaccount', \context_course::instance($this->courseid));

		$PAGE->set_url($this->url);
		$title = get_string('pluginname', 'block_upchecker_setting');
		$PAGE->set_title($title);
		$PAGE->set_heading($this->course->fullname);
		$PAGE->navbar->add($title);
	}

	public abstract function execute();

	/**
	 *
	 * @param string $title
	 */
	public function set_page_title($title) {
		global $PAGE;
		$PAGE->set_title($this->course->shortname.': '.$title);
		$PAGE->navbar->add($title);
	}
}
