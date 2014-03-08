<?php

class Template {

    /**
     * where assigned template vars are kept
     *
     * @var array
     */
    private $_tpl_vars = array();

    function assign($tpl_var, $value = null) {
        if (is_array($tpl_var)) {
            foreach ($tpl_var as $key => $val) {
                if ($key != '') {
                    $this->_tpl_vars[$key] = $val;
                }
            }
        } else {
            if ($tpl_var != '')
                $this->_tpl_vars[$tpl_var] = $value;
        }
    }

    /**
     * display
     *
     * @param string $filename the template
     */
    function display($filename) {

        extract($this->_tpl_vars);
        unset($this->_tpl_vars);
        $filePath = JPATH_THEMES . DS . $filename . '.phtml';
        if (file_exists($filePath)) {
            include_once($filePath);
        } else {
            //trigger_error("{$template} template file not exist!",E_USER_ERROR);
            //die();
        }
    }

    static public function render($filename, array $param= array()) {
        extract($param);
        unset($param);
        $filePath = JPATH_THEMES . DS . $filename . '.phtml';
        if (file_exists($filePath)) {
            include_once($filePath);
        }
    }

    static public function _include($filename, array $param= array()) {
        $filename = JPATH_THEMES . DS . $filename . '.phtml';
        if (is_file($filename)) {
            ob_start();
            extract($param);
            include($filename);
            $contents = ob_get_contents();
            ob_end_clean();
            return $contents;
        }
        return false;
    }

}