<?php
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
