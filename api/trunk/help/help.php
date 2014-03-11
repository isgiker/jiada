<?

require('../global.php');
if (isset($_SERVER['argv'][0]) && $_SERVER['argv'][0]) {
    $modulePath = BASE_PATH . DS . '_api/';
    //$fileArray=scandir($modulePath);
    //foreach($fileArray as $file){
    $file = str_replace(':', DS, $_SERVER['argv'][0]);
    if (!preg_match("/^\./i", $file)) {
        $fileContent = file_get_contents($modulePath . $file);
        preg_match_all("/\/\*\*.*?\*\//is", $fileContent, $remark_array);
        $remark_array = $remark_array[0];
        foreach ($remark_array as $remark) {
            $remarks_tmp[] = parseRemark($remark);
        }
        list($name, $ext) = explode('.', $file);
        $remarks[$name] = $remarks_tmp;
        unset($remarks_tmp);
    }
    //}
    formatShow($remarks);
} else {
    $defaultShow = array('基本信息' => array(array('项目名称:BUSAP接口通用测试程序', '版本:v1.4', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '')));
    formatShow($defaultShow);
}

function parseRemark($remark) {
    $remark_tmp = preg_split("/\r/i", trim(preg_replace("/[\/\*]/i", '', $remark)));
    foreach ($remark_tmp as $value) {
        $value = trim($value);
        if ($value != '') {
            if (preg_match("/[a-zA-z0-9]+\.[a-zA-z0-9]+/i", $value)) {
                list($obj, $parameter) = explode(' ', $value);
                $parameter = urlencode($parameter);
                $value = '<a href="javascript:void(0);" onclick="setObjValue(\'' . $obj . '\',\'' . $parameter . '\');">' . $value . '</a>';
            }
            $result_remark[] = $value;
        }
    }
    return $result_remark;
}

function formatShow($remarks) {
    if(!current($remarks)) die('No code!');
    echo '<div style="color:#fff;background-color:red;padding:10px">BusAP接口速查</div><div style="font-size: 12px;">';
    foreach ($remarks as $key => $remark) {
        echo '<b>' . $key . '</b><br>';
        foreach ($remark as $value) {
            echo '　　' . implode('<br>　　', $value) . '<br><br>';
        }
    }
    echo '</div><div style="color:#fff;background-color:#222;padding:10px">BusAP接口速查</div><div style="font-size: 12px;">';
}

?>