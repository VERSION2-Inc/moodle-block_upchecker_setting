<?php
/**
 * Settings block of programming question type for Moodle
 *
 * @package    block_upchecker_setting
 * @subpackage upchecker
 * @copyright  VERSION2, Inc.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$capabilities = array(
        'block/upchecker_setting:addinstance' => array(
                'riskbitmask' => RISK_SPAM | RISK_XSS,

                'captype' => 'write',
                'contextlevel' => CONTEXT_BLOCK,
                'archetypes' => array(
                        'editingteacher' => CAP_ALLOW,
                        'manager' => CAP_ALLOW
                ),

                'clonepermissionsfrom' => 'moodle/site:manageblocks'
        ),

        'block/upchecker_setting:managestorageaccount' => array(
                'captype' => 'write',
                'contextlevel' => CONTEXT_COURSE,
                'archetypes' => array(
                        'editingteacher' => CAP_ALLOW,
                        'manager' => CAP_ALLOW
                ),
        ),
);
