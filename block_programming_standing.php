<?php

include_once($CFG->dirroot.'/mod/programming/lib.php');
include_once($CFG->dirroot.'/lib/tablelib.php');

class block_programming_standing extends block_base {

    function init() {
        $this->title = get_string('pluginname', 'block_programming_standing');
    }

    function instance_allow_config() {
        return true;
    }

    function default_config() {
        if (empty($this->config)) {
            $this->config = new stdClass;
        }

        if (!isset($this->config->listhowmany)) {
            $this->config->listhowmany = 20;
        }

        if (!isset($this->config->perpageonfulllist)) {
            $this->config->perpageonfulllist = 50;
        }

		if (!isset($this->config->shownames)) {
            $this->config->shownames = 50;
        }

        if (!isset($this->config->roleforstanding)) {
            $this->config->roleforstanding = 5; // default role id of students
        }

        if (!isset($this->config->wrongsubmitminutes)) {
            $this->config->wrongsubmitminutes = 120;
        }

        if (!isset($this->config->showdetail)) {
           $this->config->showdetail = 1;
        }
    }

    function get_content() {
        global $PAGE;

        $context = get_context_instance(CONTEXT_BLOCK, $this->instance->id);

        if ($this->content != NULL) {
            return $this->content;
        }

        if (!isset($this->instance)) {
            return '';
        }

        $this->default_config();

        $course = $this->page->context;

        $tops = programming_calc_standing($course->instanceid, $this->config->roleforstanding, $this->config->wrongsubmitminutes, 0, $this->config->listhowmany);
        $renderer = $PAGE->get_renderer('block_programming_standing');
        $this->content = new stdClass;
        $this->content->text = $renderer->block_list($this->config, $tops, $course->instanceid);
        $this->content->footer = $renderer->footer($this->config, $this->instance->id, $course->instanceid);

        return $this->content;
    }

}

?>
