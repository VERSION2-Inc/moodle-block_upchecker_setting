<?php
require_once '../../config.php';
require_once $CFG->dirroot . '/question/type/upchecker/class/dropbox.php';

class block_upchecker_setting_dropbox {
    /**
     *
     * @var int
     */
    private $courseid;
    /**
     *
     * @var moodle_url
     */
    private $url;
    /**
     *
     * @var \stdClass
     */
    private $setting;
    /**
     *
     * @var qtype_upchecker_dropbox
     */
    private $dropbox;

    public function execute() {
        global $DB, $PAGE;

        $this->courseid = required_param('course', PARAM_INT);

        require_login($this->courseid);

        require_capability('block/upchecker_setting:managestorageaccount', context_course::instance($this->courseid));

        $this->url = new moodle_url('/blocks/upchecker_setting/authorize.php', array(
                'course' => $this->courseid));
        $PAGE->set_url($this->url);
        $strdropboxacc = get_string('dropboxaccount', 'block_upchecker_setting');
        $PAGE->set_title($strdropboxacc);
        $PAGE->set_heading($strdropboxacc);
        $PAGE->navbar->add($strdropboxacc);

        $this->setting = $this->get_setting();

        $this->dropbox = new qtype_upchecker_dropbox(array(
                'access_token' => $this->setting->accesstoken,
                'access_token_secret' => $this->setting->accesssecret,
                'oauth_callback' => (string)$this->url
        ));

        if (optional_param('unauthorize', 0, PARAM_BOOL)) {
            $this->unauthorize();
        }

        if (optional_param('uid', 0, PARAM_INT)) {
            $this->update_access_token();
        }

        $this->output();
    }

    private function output() {
        global $OUTPUT;

        echo $OUTPUT->header();

        echo $OUTPUT->heading(get_string('dropboxaccount', 'block_upchecker_setting'));

        if (!empty($this->setting->accesstoken)) {
            echo \html_writer::tag('div',
                    $OUTPUT->action_link(new moodle_url($this->url, array('unauthorize' => 1)),
                            get_string('unauthorize', 'block_upchecker_setting'))
            );
            echo \html_writer::tag('div', get_string('dropboxauthorized', 'block_upchecker_setting'));
        } else {
            $result = $this->request_token();
            echo \html_writer::tag('div',
                    $OUTPUT->action_link($result['authorize_url'],
                            get_string('authorize', 'block_upchecker_setting'))
            );
            echo \html_writer::tag('div', get_string('dropboxnotauthorized', 'block_upchecker_setting'));
        }

        if ($this->setting->accesstoken) {
            try {
                $info = $this->dropbox->get_info();

                $table = new html_table();
                $table->data = array(
                        array(get_string('owner', 'block_upchecker_setting'), $info->display_name),
                        array(get_string('email'), $info->email),
                        array(get_string('usedsize', 'block_upchecker_setting'), display_size($info->quota_info->normal).' / '.display_size($info->quota_info->quota))
                );
                echo html_writer::table($table);
            } catch (moodle_exception $e) {
                echo $OUTPUT->notification($e->getMessage());
            }
        }

        echo $OUTPUT->footer();
    }

    private function unauthorize() {
        $this->setting->requesttoken = $this->setting->requestsecret = $this->setting->accesstoken
            = $this->setting->accesssecret = '';
        $this->update_setting();

        redirect($this->url);
    }

    /**
     *
     * @return \stdClass
     */
    private function request_token() {
        $result = $this->dropbox->request_token();

        $this->setting->requesttoken = $result['oauth_token'];
        $this->setting->requestsecret = $result['oauth_token_secret'];
        $this->update_setting();

        return $result;
    }

    private function update_access_token() {
        $token = required_param('oauth_token', PARAM_ALPHANUMEXT);

        $access = $this->dropbox->get_access_token($token, $this->setting->requestsecret);

        $this->setting->accesstoken = $access['oauth_token'];
        $this->setting->accesssecret = $access['oauth_token_secret'];
        $this->update_setting();

        redirect($this->url);
    }

    private function get_setting() {
        global $DB;

        $this->setting = $DB->get_record('block_upchecker_setting_crs', array('course' => $this->courseid));
        if (!$this->setting) {
            $this->setting = (object)array(
                    'course' => $this->courseid,
                    'timemodified' => time()
            );
            $this->setting->id = $DB->insert_record('block_upchecker_setting_crs', $this->setting);
            $this->setting = $DB->get_record('block_upchecker_setting_crs', array('id' => $this->setting->id));
        }

        return $this->setting;
    }

    private function update_setting() {
        global $DB;

        $this->setting->timemodified = time();
        $DB->update_record('block_upchecker_setting_crs', $this->setting);
    }
}

$page = new block_upchecker_setting_dropbox();
$page->execute();
