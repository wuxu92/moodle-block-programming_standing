<?php

include_once($CFG->dirroot.'/mod/programming/lib.php');
include_once($CFG->dirroot.'/lib/tablelib.php');

class block_programming_standing extends block_base {

    function init() {
        $this->title = get_string('programmingstanding', 'block_programming_standing');
        $this->version = 2007031901;

        $this->config->listhowmany = 20;
        $this->config->perpageonfulllist = 50;
		$this->config->shownames = 50;
        $this->config->roleforstanding = 5; // default role id of students
        $this->config->wrongsubmitminutes = 120;
        $this->config->showdetail = 1;
    }

    function get_content() {
        global $CFG, $USER;

        if ($this->content != NULL) {
            return $this->content;
        }

        if (!isset($this->instance)) {
            return '';
        }
        $tops = programming_calc_standing($this->instance->pageid, $this->config->roleforstanding, $this->config->wrongsubmitminutes, 0, $this->config->listhowmany);
        $this->content = new stdClass;
        $c  = '<div id="block-programming-standing">';
        $c .= '<table align="center" class="generaltable generalbox" cellpadding="3" cellspacing="1">';
        $c .= '<tr align="center">';
        $c .= '<th>'.get_string('no.', 'block_programming_standing').'</th>';
        $c .= '<th>'.get_string('who', 'block_programming_standing').'</th>';
        $c .= '<th>'.get_string('ac', 'block_programming_standing').'</th>';
        $c .= '</tr>';
        $i = 1;
        foreach ($tops as $t) {
            if ($t->ac == 0 || $i > $this->config->listhowmany) break;
            $c .= '<tr align="center">';
            $c .= '<td>'.$i++.'</td>';
            $c .= '<td>';
            $c .= $i <= $this->config->shownames || has_capability('block/programming_standing:view') || $t->user->id == $USER->id ? '<a href="'.$CFG->wwwroot.'/user/view.php?id='.$t->user->id.'&amp;course='.$this->instance->pageid.'">'.fullname($t->user).'</a>' : '???';
            $c .= '</td>';
        
            $c .= '<td>'.$t->ac.'</td>';
            $c .= '</tr>';
        }
        if ($i == 1) {
            $c .= '<tr><td colspan="3">';
            $c .= get_string('nosubmit', 'block_programming_standing');
            $c .= '</td></tr>';
        }
        $c .= '</table>';
        $c .= '</div>';
        $this->content->text = $c;
        $this->content->footer = '<a href="'.$CFG->wwwroot.'/blocks/programming_standing/full_standing.php?id='.$this->instance->id.'">'.get_string('more').'</a>';

        return $this->content;
    }

    function html_attribute() {
        return array('class' => 'sideblock block_'.$this->name);
    }

    function instance_allow_config() {
        return true;
    }
    
}

?>
