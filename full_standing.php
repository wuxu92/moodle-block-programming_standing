<?php

    include_once('../../config.php');
    include_once('../../mod/programming/lib.php');
    include_once('../../lib/tablelib.php');

    $id = optional_param('id', 0, PARAM_INT);    // block instance id
    $page = optional_param('page', 0, PARAM_INT);

    $instance = get_record('block_instance', 'id', $id);
    $block = block_instance('programming_standing', $instance);
    $context = get_context_instance(CONTEXT_BLOCK, $id);
    $perpage = $block->config->perpageonfulllist;

    if (!$course = get_record('course', 'id', $block->instance->pageid)) {
        error('course misconfigured');
    }

    require_login($course->id);

/// Print the page header
    if (!isset($CFG->scripts) || !is_array($CFG->scripts)) {
        $CFG->scripts = array();
        $CFG->scripts[] = '/mod/programming/programming.js';
    }
    $CFG->stylesheets[] = $CFG->wwwroot.'/mod/programming/programming.css';
    array_unshift($CFG->scripts, $CFG->wwwroot.'/mod/programming/js/MochiKit/MochiKit.js');

    if ($course->category) {
        $navigation = build_navigation(get_string('programmingstanding', 'block_programming_standing'));
    }

    $strprogrammings = get_string('modulenameplural', 'programming');
    $strprogramming  = get_string('modulename', 'programming');

    $meta = '';
    foreach ($CFG->scripts as $script) {
        $meta .= '<script type="text/javascript" src="'.$script.'"></script>';
        $meta .= "\n";
    }

    print_header(
        $course->shortname.': '.get_string('programmingstanding', 'block_programming_standing'),
        $course->fullname,
        $navigation,
        '', // focus
        $meta,
        true,
        '',
        '',
        false);

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
    
    echo '<div class="maincontent generalbox">';
    echo '<h1 align="center">'.get_string('programmingstanding', 'block_programming_standing').'</h1>';

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
            $headers[] = "<a href='{$CFG->wwwroot}/mod/programming/view.php?id={$prog->id}'>$prog->name</a>";
        }
    }

    $table->define_headers($headers);
    $table->set_attribute('cellspacing', '0');
    $table->set_attribute('id', 'programming-standing-full-standing');
    $table->set_attribute('class', 'generaltable generalbox');
    $table->set_attribute('align', 'center');
    $table->set_attribute('cellpadding', '3');
    $table->set_attribute('cellspacing', '1');
    $table->define_baseurl($CFG->wwwroot.'/blocks/programming_standing/full_standing.php?id='.$id);
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
                    $pr = get_record('programming_result', 'programmingid', $prog->id, 'userid', $t->user->id);
                    if ($pr) {
                        $ps = get_record('programming_submits', 'id', $pr->latestsubmitid);
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

    echo '</div>';

/// Finish the page
    print_footer($course);
?>
