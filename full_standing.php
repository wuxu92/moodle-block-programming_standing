<?php

    include_once('../../config.php');
    include_once('../../mod/programming/lib.php');
    include_once('../../lib/tablelib.php');

    $id = required_param('id', PARAM_INT);    // Block ID
    $courseid = required_param('course', PARAM_INT);    // Course ID
    $page = optional_param('page', 0, PARAM_INT);

    if (!$course = $DB->get_record('course', array('id' => $courseid))) {
        print_error('course misconfigured');
    }

    $instance = $DB->get_record('block_instances', array('id' => $id));
    $block = block_instance('programming_standing', $instance);
    $block->default_config();
    $blockcontext = get_context_instance(CONTEXT_BLOCK, $id);

    $perpage = $block->config->perpageonfulllist;
    $params = array('id' => $id, 'course' => $courseid, 'page' => $page, 'perpage' => $perpage);

    require_login($course->id, true);
    $context = get_context_instance(CONTEXT_COURSE, $courseid);
    $PAGE->set_course($course);
    $PAGE->set_url('/mod/programming/view.php', $params);

/// Print the page header
    $PAGE->set_heading(format_string($course->fullname));
    echo $OUTPUT->header();

/// Print the main part of the page
    $tops = programming_calc_standing($course->id, $block->config->roleforstanding, $block->config->wrongsubmitminutes, $page * $perpage, $perpage);
    $totalcount = programming_count_standing($course->id, $block->config->roleforstanding);

    $progs = array();
    foreach (get_all_instances_in_course('programming', $course) as $prog) {
        $cm = get_coursemodule_from_instance('programming', $prog->id, $course->id);
        if ($cm->visible) {
            $progs[] = $prog;
        }
    }
    
    echo '<h1 align="center">'.get_string('pluginname', 'block_programming_standing').'</h1>';

    $table = new flexible_table('programming-standing-full-standing');
    $def = array('number', 'user', 'accepted');
    if ($course->id != 1) {
        $def[] = 'penalty';
    };
    if (! $block->config->showdetail) {
        $def[] = 'submitcount';
        $def[] = 'timeused'; 
        $def[] = 'institution';
        $def[] = 'department';
    } else {
        foreach ($progs as $prog) {
            $def[] = $prog->id;
        }
    }

    $table->define_columns($def);
    $headers = array(
        get_string('no.', 'block_programming_standing'),
        get_string('who', 'block_programming_standing'),
        get_string('accepted', 'block_programming_standing')
    );
    if ($course->id != 1) {
        $headers[] = get_string('penalty', 'block_programming_standing');
    }
    if (! $block->config->showdetail) {
        $headers[] = get_string('submitcount', 'block_programming_standing');
        $headers[] = get_string('timeused', 'block_programming_standing');
        $headers[] = get_string('institution');
        $headers[] = get_string('department');
    } else {
        foreach ($progs as $prog) {
            $headers[] = $OUTPUT->action_link(new moodle_url('/mod/programming/view.php', array('pid' => $prog->id)), $prog->name);
        }
    }

    $table->define_headers($headers);
    $table->set_attribute('cellspacing', '0');
    $table->set_attribute('id', 'programming-standing-full-standing');
    $table->set_attribute('class', 'generaltable generalbox');
    $table->set_attribute('align', 'center');
    $table->set_attribute('cellpadding', '3');
    $table->set_attribute('cellspacing', '1');
    $table->define_baseurl(new moodle_url('/blocks/programming_standing/full_standing.php', $params));
    $table->pagesize($perpage, $totalcount);
    $table->setup();

    $i = $page * $perpage;
    foreach ($tops as $t) {
        $p = $t->penalty;
        $tu = $t->timeused;
        $data = array(
            ++$i,
            $i <= $block->config->shownames || has_capability('block/programming_standing:view', $context) || $t->user->id == $USER->id ? '<a href="'.$CFG->wwwroot.'/user/view.php?id='.$t->user->id.'&amp;course='.$course->id.'">'.fullname($t->user).'</a>' : '???',
            $t->ac);
        if ($course->id != 1) {
            $data[] = sprintf('%d:%02d:%02d', $p / 3600, ($p % 3600) / 60, ($p % 60));
        }
        if (! $block->config->showdetail) {
            $data[] = $t->submitcount;
            $data[] = sprintf('%d:%02d:%02d', $tu / 3600, ($tu % 3600) / 60, ($tu % 60));
            $data[] = has_capability('block/programming_standing:view') ? $t->user->institution : '???';
            $data[] = has_capability('block/programming_standing:view') ? $t->user->department : '???';
        } else {
            if ($progs) {
                foreach ($progs as $prog) {
                    $pr = $DB->get_record('programming_result', array('programmingid' => $prog->id, 'userid' => $t->user->id));
                    if ($pr) {
                        $ps = $DB->get_record('programming_submits', array('id' => $pr->latestsubmitid));
                        $html = '';
                        if ($ps->passed) {
                            $tu = $ps->timemodified - $prog->timeopen;
                            $html = sprintf('%d:%02d:%02d', $tu / 3600, ($tu % 3600) / 60, ($tu % 60));
                            if ($pr->submitcount > 1) {
                                $html .= '<br />(-'.($pr->submitcount - 1).')';
                            }
                        } else {
                            $html = '(-'.($pr->submitcount).')';
                        }
                        $data[] = $html;
                    } else {
                        $data[] = '&nbsp;';
                    }
                }
            }
        }
        $table->add_data($data);
    }

    $table->print_html();

/// Finish the page
    echo $OUTPUT->footer($course);
?>
