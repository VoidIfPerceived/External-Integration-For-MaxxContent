<?php

namespace mod_extintmaxx\task;
defined('MOODLE_INTERNAL') || die();

require(__DIR__ . '../../../lib.php');

class regular_acci_check extends \core\task\scheduled_task {
    public function get_name() {
        return get_string('regular_acci_check', 'extintmaxx');
    }

    function get_provider_instances() {
        global $DB;
        $providerinstances = $DB->get_records(
            'extintmaxx',
            ['provider' => 'acci']
        );
        return $providerinstances;
    }

    public function execute() {
        $instances = $this->get_provider_instances();
        foreach ($instances as $instance) {
            extintmaxx_update_grades($instance);
        }
    }
}
