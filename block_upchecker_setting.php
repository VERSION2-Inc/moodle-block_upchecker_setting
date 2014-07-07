<?php
defined('MOODLE_INTERNAL') || die();

class block_upchecker_setting extends block_list {
    public function init() {
        $this->title = get_string('pluginname', 'block_upchecker_setting');
    }

    public function get_content() {
        global $OUTPUT, $COURSE;

        $this->content = (object)array(
                'text' => '',
                'footer' => '',
                'items' => array(),
                'icons' => null,
        );

        if (has_capability('block/upchecker_setting:managestorageaccount', context_course::instance($COURSE->id))) {
            $this->content->items[] = $OUTPUT->action_link(
                    new moodle_url('/blocks/upchecker_setting/authorize.php', array('course' => $COURSE->id)),
                    get_string('dropboxaccount', 'block_upchecker_setting'));
            $this->content->items[] = $OUTPUT->action_link(
            		new moodle_url('/blocks/upchecker_setting/resync.php', ['course' => $COURSE->id]),
            get_string('resyncfiles', 'block_upchecker_setting'));
        }

        return $this->content;
    }

    public static function get_upchecker_setting() {
        global $DB, $COURSE;

        $courseid = $COURSE->id;

        $setting = $DB->get_record('block_upchecker_setting_crs', array('course' => $courseid));
        if (!$setting) {
            $setting = (object)array(
                    'course' => $courseid,
                    'timemodified' => time()
            );
            $setting->id = $DB->insert_record('block_upchecker_setting_crs', $setting);
        }

        return $setting;
    }
}
