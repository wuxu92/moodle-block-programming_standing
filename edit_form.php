<?php
 
class block_programming_latest_ac_edit_form extends block_edit_form {
 
    protected function specific_definition($mform) {
        // Fields for editing HTML block title and contents.
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        $mform->addElement('text', 'listhowmany', get_string('showhowmanyonlist', 'block_programming_standing'));
        $mform->addElement('text', 'perpageonfulllist', get_string('perpageonfulllist', 'block_programming_standing'));
        $mform->addElement('text', 'shownames', get_string('shownames', 'block_programming_standing'));
        $mform->addElement('text', 'wrongsubmitminutes', get_string('howmanyminuteswrongsubmit', 'block_programming_standing'));
        $mform->addElement('text', 'showdetail', get_string('showdetail', 'block_programming_standing'));

    }

    function set_data($defaults) {
        parent::set_data($defaults);

    }

}
