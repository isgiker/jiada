<?php

class Core_View_Basic extends Yaf_View_Simple {

    private $_config;

    public function demoView() {
        echo('Demo: Core_Basic_View');
    }
    
    public function seg($viewPath, $params = array()) {
        return $this->render($viewPath, $params);
    }

    public function _js($files) {
        $config = Yaf_Registry::get("_CONFIG");
        $domain = @$config->domain->js;
        $v = @$config->system->version;
        $htmlTag='';
        if($files && is_array($files)){
            foreach($files as $key=>$file){
                
                if(isset($file['path']) && $file['path']){
                    //文件路径
                    $files_path=$file['path'];
                    if (strpos($files_path, '//') === FALSE) {
                        $files_path = ltrim($files_path, '/');
                        $src = '//'.$domain . '/' . $files_path;
                        if ($v) {
                            if(strpos($files_path,'?')){
//                                $src .= '&' . http_build_query(array('v'=>$v));
                            }else{
                                $src .= '?' . http_build_query(array('v'=>$v));
                            }                
                        }
                    } else {
                        $src = $files_path;
                    }
                    
                    //html script/link属性
                    $attribute = '';
                    if(isset($file['attr']) && $file['attr']){
                        $files_attr=$file['attr'];                    
                        
                        if (!empty($files_attr) && is_array($files_attr)) {
                            foreach ($files_attr as $key => $value) {
                                $attribute .= " {$key}=\"{$value}\"";
                            }
                        }
                    }
                    $htmlTag .= '<script src="' . $src . '"' . $attribute . ' type="text/javascript"></script>';
                    $htmlTag .="\n";
                }
                
            }
            
        }
        
        return $htmlTag;
    }

    public function _css($files) {
        $config = Yaf_Registry::get("_CONFIG");
        $domain = @$config->domain->css;
        $v = @$config->system->version;
        $htmlTag='';
        if($files && is_array($files)){
            foreach($files as $key=>$file){
                
                if(isset($file['path']) && $file['path']){
                    //文件路径
                    $files_path=$file['path'];
                    if (strpos($files_path, '//') === FALSE) {
                        $files_path = ltrim($files_path, '/');
                        $src = '//'.$domain . '/' . $files_path;
                        if ($v) {
                            if(strpos($files_path,'?')){
//                                $src .= '&' . http_build_query(array('v'=>$v));
                            }else{
                                $src .= '?' . http_build_query(array('v'=>$v));
                            }                
                        }
                    } else {
                        $src = $files_path;
                    }
                    
                    //html script/link属性
                    $attribute = '';
                    if(isset($file['attr']) && $file['attr']){
                        $files_attr=$file['attr'];                    
                        
                        if (!empty($files_attr) && is_array($files_attr)) {
                            foreach ($files_attr as $key => $value) {
                                $attribute .= " {$key}=\"{$value}\"";
                            }
                        }
                    }
                    $htmlTag .= '<link href="' . $src . '"' . $attribute . ' rel="stylesheet"/>';
                    $htmlTag .="\n";
                }
                
            }
            
        }
        
        return $htmlTag;
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
    
    /**
     * js跳转
     * @param type $eventUrl
     * @param type $eventMsg
     * @param type $flag
     */
    public function jsRedirect($_event, $flag=true) {
        echo "<script>";
        if ($_event['_eventMsg'])
            echo "alert('{$_event['_eventMsg']}');";
        if ($_event['_eventUrl'])
            echo "window.location.href='{$_event['_eventUrl']}'";
        echo "</script>";
        if ($flag)
            exit;
    }

}
