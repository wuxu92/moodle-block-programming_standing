<?php

class block_programming_latest_ac_renderer extends plugin_renderer_base {

    function block_list($tops, $course) {
        global $OUTPUT;

        $c = '';
        $total = 0;
        $tops = programming_latest_ac($course->instanceid, $config->roleforlatestac, $total, 0, $config->listhowmany);

        if (!empty($tops)) {
            $table = new html_table();
            $table->head = array(
                get_string('no.', 'block_programming_standing'),
                get_string('who', 'block_programming_standing'),
                get_string('ac', 'block_programming_standing'));
            $table->data = array();

            $i = 1;
            foreach ($tops as $t) {
                if ($t->ac == 0 || $i > $this->config->listhowmany) break;

                $who = $OUTPUT->action_link(new moodle_url('/user/view.php', array('id' => $t->user->id, 'course' => $course->instanceid)), fullname($t->user));
                $which = $t->ac;
                $table->data[] = array($i++, $who, $which);
            }

            $c = html_writer::table($table);
        } else {
            $c = get_string('nosubmit', 'block_programming_standing');
        }

        return $c;
        $c .= '</table>';

    }

}
