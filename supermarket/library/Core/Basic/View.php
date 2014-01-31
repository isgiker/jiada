<?php

class Core_Basic_View extends Yaf_View_Simple {

    private $_config;

    public function demoView() {
        echo('Demo: Core_Basic_View');
    }
    
    public function seg($viewPath, $params = array()) {
        return $this->render($viewPath, $params);
    }

    public function _js($file = NULL, $params = array()) {
        $config = Yaf_Registry::get("config");
        if (strpos($file, '//') === FALSE) {
            $strPath = $this->js($file, array('v' => $config->app->version));
        } else {
            $strPath = $file;
        }
        $strParams = '';
        if (!empty($params)) {
            foreach ($params as $key => $value) {
                $strParams .= " {$key}=\"{$value}\"";
            }
        }
        $strPath = '<script src="' . $strPath . '"' . $strParams . '></script>';
        return $strPath;
    }

    public function _css($file = NULL, $params = array()) {
        $config = Yaf_Registry::get("config");
        if (strpos($file, '//') === FALSE) {
            $strPath = $this->css($file, array('v' => $config->app->version));
        } else {
            $strPath = $file;
        }
        $strParams = '';
        if (!empty($params)) {
            foreach ($params as $key => $value) {
                $strParams .= " {$key}=\"{$value}\"";
            }
        }
        $strPath = '<link href="' . $strPath . '" rel="stylesheet"' . $strParams . ' />';
        return $strPath;
    }

    public function _img($file = NULL, $params = array()) {
        $config = Yaf_Registry::get("config");
        if (strpos($file, '//') === FALSE) {
            $strPath = $this->img($file, array('v' => $config->app->version));
        } else {
            $strPath = $file;
        }
        $strParams = '';
        if (!empty($params)) {
            foreach ($params as $key => $value) {
                $strParams .= " {$key}=\"{$value}\"";
            }
        }
        $strPath = '<img src="' . $strPath . '"' . $strParams . ' />';
        return $strPath;
    }

    public static function js($path, $params = array()) {
        $config = Yaf_Registry::get("config");
        $strUrl = $config->domain->static;
        $path = ltrim($path, '/');
        $strReturn = '//' . $strUrl . '/' . $path;
        if (!empty($params)) {
            $strReturn .= '?' . http_build_query($params);
        }
        return $strReturn;
    }

    public static function css($path, $params = array()) {
        $config = Yaf_Registry::get("config");
        $strUrl = $config->domain->static;
        $path = ltrim($path, '/');
        $strReturn = '//' . $strUrl . '/' . $path;
        if (!empty($params)) {
            $strReturn .= '?' . http_build_query($params);
        }
        return $strReturn;
    }

    public static function img($path, $params = array()) {
        $config = Yaf_Registry::get("config");
        $strUrl = $config->domain->static;
        $path = ltrim($path, '/');
        $strReturn = '//' . $strUrl . '/' . $path;
        if (!empty($params)) {
            $strReturn .= '?' . http_build_query($params);
        }
        return $strReturn;
    }

    /**
     * 自动获取验证属性
     */
    public function getInputAttrs($json, $key) {
        $pattern = '/attr\[(\d+)\]/';

        foreach ($json as $vs) {
            if ($key == $vs->value) {
                $val = $vs;
                $filed = $key;
                break;
            } elseif (preg_match($pattern, $key)) {
                if ($vs->value == 'attr[]') {
                    $val = $vs;
                    $filed = $key;
                    break;
                }
            }
        }
        $attrs = array();
        // $filed=$val->value;
        $attrs[] = 'name="' . $filed . '"';
        $label = $val->label;
        $rules = $val->rules;
        foreach ($rules as $k => $v) {
            if (isset($v->message)) {
                $v->message = str_replace('%s%', $label, $v->message);
                $attrs[] = 'data-msg-' . $v->name . '="' . $v->message . '"';
            }
            if (isset($v->name)) {
                $attrs[] = 'data-rule-' . $v->name . '="' . (isset($v->value) ? $v->value : 'true') . '"';
            }

            switch ($v->name) {
                case 'required':
                    //$attrs[]='required';
                    break;
                case 'email':
                    $attrs[] = 'type="email"';
                    break;
                case 'number':
                    $attrs[] = 'type="number"';
                    break;
                case 'url':
                    $attrs[] = 'type="url"';
                    break;
                case 'date':
                    $attrs[] = 'type="date"';
                    break;
                case 'accept':
                    $attrs[] = 'accept="' . $v->value . '"';
                    break;
                case 'rangelength':
                    eval('$arrlength=' . $v->value . ';');
                    $attrs[] = 'maxlength="' . $arrlength[1] . '"';
                    break;
                case 'range':
                    eval('$arrlength=' . $v->value . ';');
                    $attrs[] = 'min="' . $arrlength[0] . '"';
                    $attrs[] = 'max="' . $arrlength[1] . '"';
                    $attrs[] = 'type="range"';
                    break;
                default:
                    # code...
                    break;
            }
        }
        return implode(' ', $attrs);
    }

    /**
     * 显示验证错误信息
     */
    public function showValidateError($error) {
        $html = '<div class="error alert-danger">' . $error . '</div>';
        echo($html);
    }

}
