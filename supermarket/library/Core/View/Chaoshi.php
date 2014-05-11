<?php

class Core_View_Chaoshi extends Core_View_Basic {

    public function dv() {
        echo('Demo: Core_View_Chaoshi');
    }
    
    /**
     * 重构url
     * @param array $qsArr url参数
     * $qsArr=array(
     *      'k1'=>v1,
     *      'k1_append'=>true,
     *      'k2'=>v2,
     *      'k2_append'=>false,
     *  );
     * @param bool key_append true|false 如果url参数存在是否追加,如果不追加则覆盖
     */
    public function buildUrl($qsArr){
        if(!$qsArr){
            return false;
        }
        $new_query_string = $this->replaceIntoParam($qsArr);
        $url=$this->getUrl();

        $new_url = str_replace($_SERVER['QUERY_STRING'], $new_query_string, $url);
        return $new_url;
    }
    
    public function getUrl() {
        $host=$_SERVER['HTTP_HOST'];
        if($_SERVER['SERVER_PORT']!=80){
            $host=$host.':'.$_SERVER['SERVER_PORT'];
        }

        if ($_SERVER['QUERY_STRING'] == '') {
            $url = 'http://'.$host.$_SERVER['PATH_INFO'];
        } else {
            $url = 'http://'.$host.$_SERVER['PATH_INFO'].'?'.$_SERVER['QUERY_STRING'];
        }

        return $url;
    }
    
    //获取url？后的参数
    public function getUrlParam() {
        $queryString=$_SERVER['QUERY_STRING'];
        $param=array();
        if($queryString){
            parse_str($queryString, $param);
        }        
        return $param;
    }
    
    /**
     * 向当前url中添加参数，如果存在覆盖或追加
     * @param array $qsArr 需要更新的或新增的url参数
     * @return string
     */
    public function replaceIntoParam($qsArr) {
        $urlParam = $this->getUrlParam();
        if ($qsArr && is_array($qsArr)) {
            foreach ($qsArr as $key => $value) {
                if(strpos($key, 'append')){
                    continue;
                } else {
                    $v=explode(':', $value);
                    
//                    $value=rawurlencode($value);
                    if ($qsArr[$key . '_append'] == true && isset($urlParam[$key]) && $urlParam[$key]) {
                        $oldV=explode(',', $urlParam[$key]);
                        foreach($oldV as $k=>$ov){
                            if(strstr($ov, $v[0])){
                                unset($oldV[$k]);
                            }else{
                                continue;
                            }
                        }
                        if($oldV){
                            $urlParam[$key]=implode(',', $oldV);
                            //如果需要更新的值为空则不追加处理;
                            if($v[1]){
                                $urlParam[$key] .= ',' . $value;
                            }
                        }else{
                            $urlParam[$key] = $value;
                        }

                    } else {
                        $urlParam[$key] = $value;
                    }
                    
                }
                
            }
        }
        
        if($urlParam){
            $query_string=http_build_query($urlParam);
        }else{
            $query_string='';
        }
        return $query_string;
    }
}
