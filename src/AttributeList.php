<?php

namespace PhpCalendar;

/*
 * Data structure to display XML style attributes
 * see function attributes() below for usage
 */
class AttributeList {
        var $list;

        function __construct() {
                $this->list = array();
                $args = func_get_args();
                $this->add($args);
        }

        function add() {
                $args = func_get_args();
                foreach($args as $arg) {
                        if(is_array($arg)) {
                                foreach($arg as $attr) {
                                        $this->add($attr);
                                }
                        } else {
                                $this->list[] = $arg;
                        }
                }
        }

        function toString() {
                return implode(' ', $this->list);
        }
}

?>
