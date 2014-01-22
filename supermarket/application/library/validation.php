<?php

/**
 * undocumented class
 *
 * @package default
 * @author 
 * */
class validation {

    /**
     * $data={
     * 	'字段名':{
     * 		'value':value,
     * 		'label':'',
     * 		'rules':[
     * 					{
     * 					'name':'',
     * 					'message':''
     * 					},
     * 					{
     * 					}
     * 				]	
     * }
     * }
     */
    public $error_message = array();
    public $error_array = array();
    public $parentCls = NULL;
    protected $filter_array = array();
    public $filter_array_out = array();
    protected $_safe_form_data = true;

    public function validate($data, $GetPost) {
        /**
         * (1)required                必输字段
         * (2)remote      使用ajax方法调用check.php验证输入值
         * (3)email                    必须输入正确格式的电子邮件
         * (4)url                        必须输入正确格式的网址
         * (5)date                      必须输入正确格式的日期 日期校验ie6出错，慎用
         * (6)regex
         * (7)number                 必须输入合法的数字(负数，小数)
         * (8)digits                    必须输入整数
         * (10)equalTo:"#field"          输入值必须和#field相同
         * (11)accept:                       输入拥有合法后缀名的字符串（上传文件的后缀）
         * (12)rangelength:[5,10]      输入长度必须介于 5 和 10 之间的字符串")(汉字算一个字符)
         * (13)range:[5,10]               输入值必须介于 5 和 10 之间
         */
        $datas = json_decode($data);
        $data = $datas->validation;
        //print_r($GetPost);
        //exit;

        $checkType = array('required', 'number', 'remote', 'email', 'url', 'date', 'digits', 'equalTo', 'accept', 'rangelength', 'range', 'regex', 'clearxss');
        foreach ($data as $v) {            
            $val = $v->value;
            if (!isset($GetPost[$val])) {
                continue;
            }
            
            //检测提交内容是否是数组
            if (strstr($val, '[]')) {
                $val = str_replace('[]', '', $val);
                if (isset($GetPost[$val])) {
                    $GetPost[$val] = join(',', $GetPost[$val]);
                } else {
                    $GetPost[$val] = "";
                }
            }
            $label = $v->label;
            $rules = $v->rules;
            foreach ($rules as $key => $value) {
                if (!isset($this->filter_array[$val])) {
                    $getValue = isset($GetPost[$val]) ? trim($GetPost[$val]) : '';
                    $this->filter_array[$val] = $getValue;
                } else {
                    $getValue = $this->filter_array[$val];
                }
                if (in_array($value->name, $checkType)) {
                    switch ($value->name) {
                        case 'clearxss':
                            $this->filter_array[$val] = $getValue = $this->clearxss($getValue);
                            break;
                        case 'regex':
                            $flag = $this->regex_match($getValue, $value->value);
                            break;
                        case 'remote':
                            $flag = $this->is_unique($getValue, $value->value);
                            break;
                        case 'equalTo':
                            $flag = $this->equalTo($getValue, $this->filter_array[str_replace('#', '', $value->value)]);
                            break;
                        case 'required':
                            $flag = $this->required($getValue);
                            break;
                        case 'number':
                            $flag = $this->number($getValue);
                            break;
                        case 'email':
                            $flag = $this->email($getValue);
                            break;
                        case 'url':
                            $flag = $this->url($getValue);
                            break;
                        case 'date':
                            $flag = $this->date($getValue);
                            break;
                        case 'digits':
                            $flag = $this->digits($getValue);
                            break;
                        case 'accept':
                            $flag = $this->accept($getValue, $value->value);
                            break;
                        case 'rangelength':
                            $flag = $this->rangelength($getValue, $value->value);
                            break;
                        case 'range':
                            $flag = $this->range($getValue, $value->value);
                            break;
                        default:
                            # code...
                            break;
                    }
                    /* if('regex'==$value->name)
                      {
                      $flag=$this->regex_match(trim($GetPost[$val]), $value->value);
                      }else if('remote'==$value->name)
                      {
                      $flag=$this->is_unique($value->value, trim($GetPost[$val]));
                      }else if('equalTo'==$value->name)
                      {
                      $flag=$this->equalTo($GetPost[$val],trim($GetPost[str_replace('#','',$value->value)]));
                      }else if('accept'==$value->name||'rangelength'==$value->name||'range'==$value->name)
                      {
                      eval('$flag=$this->'.($value->name).'(\''.trim($GetPost[$val]).'\',\''.$value->value.'\');');
                      }
                      else if('trim'==$value->name||'clearxss'==$value->name)
                      {
                      eval('$this->filter_array["'.$val.'"]=$this->'.($value->name).'(\''.trim($GetPost[$val]).'\');');
                      }
                      else
                      {
                      //echo '$flag=$this->'.($value->name).'("'.trim($GetPost[$val]).'");';//exit;
                      eval('$flag=$this->'.($value->name).'(\''.trim($GetPost[$val]).'\');');
                      } */
                    if (isset($flag) && !$flag) {
                        if (empty($this->error_message[$val])) {
                            if (isset($value->message)) {
                                $this->error_message[$val] = str_replace('%s%', $label, $value->message);
                                $this->error_array[] = str_replace('%s%', $label, $value->message);
                            }
                        }
                    }
                }
            }
        }
        // Did we end up with any errors?
        $total_errors = count($this->error_array);
        if ($total_errors > 0) {
            $this->_safe_form_data = TRUE;
        } else {
            $this->filter_array_out = $this->filter_array;
        }
    }

    // --------------------------------------------------------------------

    /**
     * Error String
     *
     * Returns the error messages as a string, wrapped in the error delimiters
     *
     * @access	public
     * @param	string
     * @param	string
     * @return	str
     */
    public function error_string($prefix = '', $suffix = '') {
        // No errrors, validation passes!
        if (count($this->error_array) === 0) {
            return '';
        }

        // Generate the error string
        $str = '';
        foreach ($this->error_array as $val) {
            if ($val != '') {
                $str .= $prefix . $val . $suffix . "\n";
            }
        }

        return $str;
    }

    // --------------------------------------------------------------------
    /**
     * trim
     *
     * @access	public
     * @param	string
     * @return	string
     */
    public function trim($str) {
        $str = trim($str);
        return $str;
    }

    /**
     * equalTo
     *
     * @access	public
     * @param	string
     * @param	string
     * @return	bool
     */
    public function equalTo($str1, $str2) {

        return (trim($str1) == trim($str2)) ? TRUE : FALSE;
    }

    // --------------------------------------------------------------------

    /**
     * accept
     *
     * @access	public
     * @param	string
     * @param	string
     * @return	bool
     */
    public function accept($str, $extend) {
        if (empty($extend))
            return true;
        $extends = explode(',', $extend);
        $ex = pathinfo($str);
        $ext = strtolower($ex['extension']);
        return (in_array($ext, $extends)) ? TRUE : FALSE;
    }

    // --------------------------------------------------------------------

    /**
     * Rangelength
     *
     * @access	public
     * @param	string
     * @param	string
     * @return	bool
     */
    public function rangelength($str, $extend) {
        if (empty($extend))
            return true;
        // 将字符串分解为单元  
        preg_match_all("/./us", $str, $match);
        // 返回单元个数  
        $num = count($match[0]);
        $p = explode(',', $extend);
        $pre = str_replace('[', '', $p[0]);
        $aft = str_replace(']', '', $p[1]);
        return ((intval(trim($pre)) <= $num) && (intval(trim($aft)) >= $num)) ? TRUE : FALSE;
    }

    // --------------------------------------------------------------------

    /**
     * range
     *
     * @access	public
     * @param	string
     * @param	string
     * @return	bool
     */
    public function range($str, $extend) {
        if (empty($extend))
            return true;
        $p = explode(',', $extend);
        $pre = str_replace('[', '', $p[0]);
        $aft = str_replace(']', '', $p[1]);
        return (bool) preg_match('/^[0-9]{' . $pre . ',' . $aft . '}$/', $str);
    }

    // --------------------------------------------------------------------

    /**
     * Date
     *
     * @access	public
     * @param	string
     * @return	bool
     */
    public function date($str) {
        if (empty($str))
            return true;
        return (bool) preg_match("/^[0-9]{4}(\-|\/)[0-9]{1,2}(\\1)[0-9]{1,2}(|\s+[0-9]{1,2}(:[0-9]{1,2}){0,2})$/", $str);
    }

    // --------------------------------------------------------------------

    /**
     * Url
     *
     * @access	public
     * @param	string
     * @return	bool
     */
    public function url($str) {
        if (empty($str))
            return true;
        $array = get_headers($str, 1);
        if (preg_match('/200/', $array[0])) {
            return true;
        } else {
            return false;
        }
    }

    // --------------------------------------------------------------------

    /**
     * Required
     *
     * @access	public
     * @param	string
     * @return	bool
     */
    public function required($str) {
        if (!is_array($str)) {
            return (trim($str) == '') ? FALSE : TRUE;
        } else {
            return (!empty($str));
        }
    }

    // --------------------------------------------------------------------

    /**
     * Decimal number
     *
     * @access	public
     * @param	string
     * @return	bool
     */
    public function number($str) {
        return (bool) preg_match('/^[\-+]?[0-9]+(\.[0-9]+)?$/', $str);
    }

    // --------------------------------------------------------------------

    /**
     * digits
     *
     * @access	public
     * @param	string
     * @return	bool
     */
    public function digits($str) {
        return (bool) preg_match('/^[\-+]?[0-9]+$/', $str);
    }

    /**
     * email
     *
     * @access	public
     * @param	string
     * @return	bool
     */
    public function email($str) {
        return $this->valid_email($str);
    }

    // --------------------------------------------------------------------

    /**
     * Performs a Regular Expression match test.
     *
     * @access	public
     * @param	string
     * @param	regex
     * @return	bool
     */
    public function regex_match($str, $regex) {
        if (!preg_match($regex, $str)) {
            return FALSE;
        }

        return TRUE;
    }

    // --------------------------------------------------------------------

    /**
     * Match one field to another
     *
     * @access	public
     * @param	string
     * @param	field
     * @return	bool
     */
    public function matches($str, $field) {
        if (!isset($GetPost[$field])) {
            return FALSE;
        }

        $field = $GetPost[$field];

        return ($str !== $field) ? FALSE : TRUE;
    }

    // --------------------------------------------------------------------

    /**
     * Match one field to another
     *
     * @access	public
     * @param	string
     * @param	field
     * @return	bool
     */
    public function is_unique($attr, $action) {
        if (!empty($this->parentCls)) {
            eval('$flag=$this->parentCls->' . $action . 'Action("' . $attr . '");');
            return $flag;
        }
        return 0;
    }

    // --------------------------------------------------------------------

    /**
     * Minimum Length
     *
     * @access	public
     * @param	string
     * @param	value
     * @return	bool
     */
    public function min_length($str, $val) {
        if (preg_match("/[^0-9]/", $val)) {
            return FALSE;
        }

        if (function_exists('mb_strlen')) {
            return (mb_strlen($str) < $val) ? FALSE : TRUE;
        }

        return (strlen($str) < $val) ? FALSE : TRUE;
    }

    // --------------------------------------------------------------------

    /**
     * Max Length
     *
     * @access	public
     * @param	string
     * @param	value
     * @return	bool
     */
    public function max_length($str, $val) {
        if (preg_match("/[^0-9]/", $val)) {
            return FALSE;
        }

        if (function_exists('mb_strlen')) {
            return (mb_strlen($str) > $val) ? FALSE : TRUE;
        }

        return (strlen($str) > $val) ? FALSE : TRUE;
    }

    // --------------------------------------------------------------------

    /**
     * Exact Length
     *
     * @access	public
     * @param	string
     * @param	value
     * @return	bool
     */
    public function exact_length($str, $val) {
        if (preg_match("/[^0-9]/", $val)) {
            return FALSE;
        }

        if (function_exists('mb_strlen')) {
            return (mb_strlen($str) != $val) ? FALSE : TRUE;
        }

        return (strlen($str) != $val) ? FALSE : TRUE;
    }

    // --------------------------------------------------------------------

    /**
     * Valid Email
     *
     * @access	public
     * @param	string
     * @return	bool
     */
    public function valid_email($str) {
        return (!preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $str)) ? FALSE : TRUE;
    }

    // --------------------------------------------------------------------

    /**
     * Valid Emails
     *
     * @access	public
     * @param	string
     * @return	bool
     */
    public function valid_emails($str) {
        if (strpos($str, ',') === FALSE) {
            return $this->valid_email(trim($str));
        }

        foreach (explode(',', $str) as $email) {
            if (trim($email) != '' && $this->valid_email(trim($email)) === FALSE) {
                return FALSE;
            }
        }

        return TRUE;
    }

    // --------------------------------------------------------------------

    /**
     * Validate IP Address
     *
     * @access	public
     * @param	string
     * @param	string "ipv4" or "ipv6" to validate a specific ip format
     * @return	string
     */
    public function valid_ip($ip, $which = '') {
        return true;
    }

    // --------------------------------------------------------------------

    /**
     * Alpha
     *
     * @access	public
     * @param	string
     * @return	bool
     */
    public function alpha($str) {
        return (!preg_match("/^([a-z])+$/i", $str)) ? FALSE : TRUE;
    }

    // --------------------------------------------------------------------

    /**
     * Alpha-numeric
     *
     * @access	public
     * @param	string
     * @return	bool
     */
    public function alpha_numeric($str) {
        return (!preg_match("/^([a-z0-9])+$/i", $str)) ? FALSE : TRUE;
    }

    // --------------------------------------------------------------------

    /**
     * Alpha-numeric with underscores and dashes
     *
     * @access	public
     * @param	string
     * @return	bool
     */
    public function alpha_dash($str) {
        return (!preg_match("/^([-a-z0-9_-])+$/i", $str)) ? FALSE : TRUE;
    }

    // --------------------------------------------------------------------

    /**
     * Numeric
     *
     * @access	public
     * @param	string
     * @return	bool
     */
    public function numeric($str) {
        return (bool) preg_match('/^[\-+]?[0-9]*\.?[0-9]+$/', $str);
    }

    // --------------------------------------------------------------------

    /**
     * Is Numeric
     *
     * @access	public
     * @param	string
     * @return	bool
     */
    public function is_numeric($str) {
        return (!is_numeric($str)) ? FALSE : TRUE;
    }

    // --------------------------------------------------------------------

    /**
     * Integer
     *
     * @access	public
     * @param	string
     * @return	bool
     */
    public function integer($str) {
        return (bool) preg_match('/^[\-+]?[0-9]+$/', $str);
    }

    // --------------------------------------------------------------------

    /**
     * Decimal number
     *
     * @access	public
     * @param	string
     * @return	bool
     */
    public function decimal($str) {
        return (bool) preg_match('/^[\-+]?[0-9]+\.[0-9]+$/', $str);
    }

    // --------------------------------------------------------------------

    /**
     * Greather than
     *
     * @access	public
     * @param	string
     * @return	bool
     */
    public function greater_than($str, $min) {
        if (!is_numeric($str)) {
            return FALSE;
        }
        return $str > $min;
    }

    // --------------------------------------------------------------------

    /**
     * Less than
     *
     * @access	public
     * @param	string
     * @return	bool
     */
    public function less_than($str, $max) {
        if (!is_numeric($str)) {
            return FALSE;
        }
        return $str < $max;
    }

    // --------------------------------------------------------------------

    /**
     * Is a Natural number  (0,1,2,3, etc.)
     *
     * @access	public
     * @param	string
     * @return	bool
     */
    public function is_natural($str) {
        return (bool) preg_match('/^[0-9]+$/', $str);
    }

    // --------------------------------------------------------------------

    /**
     * Is a Natural number, but not a zero  (1,2,3, etc.)
     *
     * @access	public
     * @param	string
     * @return	bool
     */
    public function is_natural_no_zero($str) {
        if (!preg_match('/^[0-9]+$/', $str)) {
            return FALSE;
        }

        if ($str == 0) {
            return FALSE;
        }

        return TRUE;
    }

    // --------------------------------------------------------------------

    /**
     * Valid Base64
     *
     * Tests a string for characters outside of the Base64 alphabet
     * as defined by RFC 2045 http://www.faqs.org/rfcs/rfc2045
     *
     * @access	public
     * @param	string
     * @return	bool
     */
    public function valid_base64($str) {
        return (bool) !preg_match('/[^a-zA-Z0-9\/\+=]/', $str);
    }

    // --------------------------------------------------------------------

    /**
     * Prep data for form
     *
     * This function allows HTML to be safely shown in a form.
     * Special characters are converted.
     *
     * @access	public
     * @param	string
     * @return	string
     */
    public function clearxss($data = '') {
        if (is_array($data)) {
            foreach ($data as $key => $val) {
                $data[$key] = $this->clearxss($val);
            }

            return $data;
        }

        if ($this->_safe_form_data == FALSE OR $data === '') {
            return $data;
        }
        //echo str_replace(array("'", '"', '<', '>'), array("&#39;", "&quot;", '&lt;', '&gt;'), stripslashes($data));
        return str_replace(array("'", '"', '<', '>'), array("&#39;", "&quot;", '&lt;', '&gt;'), stripslashes($data));
    }

    // --------------------------------------------------------------------

    /**
     * Prep URL
     *
     * @access	public
     * @param	string
     * @return	string
     */
    public function prep_url($str = '') {
        if ($str == 'http://' OR $str == '') {
            return '';
        }

        if (substr($str, 0, 7) != 'http://' && substr($str, 0, 8) != 'https://') {
            $str = 'http://' . $str;
        }

        return $str;
    }

    // --------------------------------------------------------------------

    /**
     * Strip Image Tags
     *
     * @access	public
     * @param	string
     * @return	string
     */
    function strip_image_tags($str) {
        $str = preg_replace("#<img\s+.*?src\s*=\s*[\"'](.+?)[\"'].*?\>#", "\\1", $str);
        $str = preg_replace("#<img\s+.*?src\s*=\s*(.+?).*?\>#", "\\1", $str);

        return $str;
    }

    // --------------------------------------------------------------------

    /**
     * XSS Clean
     *
     * @access	public
     * @param	string
     * @return	string
     */
    public function xss_clean($str) {
        return true;
    }

    // --------------------------------------------------------------------

    /**
     * Convert PHP tags to entities
     *
     * @access	public
     * @param	string
     * @return	string
     */
    public function encode_php_tags($str) {
        return str_replace(array('<?php', '<?PHP', '<?', '?>'), array('&lt;?php', '&lt;?PHP', '&lt;?', '?&gt;'), $str);
    }

}

