<?php

/**
 * @abstract多图片上传类，支持htm5多图上传和html单图上传
 * @author Vic Shi <isgiker@gmail.com>
 */
class File_ImageUpload {
    private $originName='';//源文件名称;
    private $errorNum = 0; //错误号
    private $filesNumber = 0; //此次上传的文件数量
    private $_files; //文件数量
    private $localDestPath; //指定上传文件保存的路径
    private $allowtype = array('image/jpeg', 'image/pjpeg', 'image/png', 'image/x-png', 'image/gif'); //充许上传文件的类型,类型合集
//    private $fileType = array('jpg', 'jpeg', 'png', 'gif'); //充许上传文件的后缀
    private $maxsize = 1024; //单位k;允上传文件的最大长度 1M
    private $uploadSuccessFile = array();

    /**
     * 构造方法，初始化，验证错误;
     * @param type $_file input 上传控件name,如：files[];这里是多文件上传，数组形式。
     */
    public function __construct($_file, $_maxsize = null) {
        //设置文件保存的目标路径,可修改;
        $this->localDestPath = $_SERVER['DOCUMENT_ROOT'] . '/temp/' . date("Y") . '/' . date("m");

        //验证参数
        if (!$_file) {
            $this->setError('errorNum', -1);
            exit();
        }
        if(isset($_FILES["$_file"]))
        {
            $this->_files = $_FILES["$_file"];
        }else{
            $this->setError('errorNum', 1);
            exit();
        }
        
        //单文件上传
        if (!is_array($this->_files['name'])) {
            $NEW_FILES = array(
                'name' => array($this->_files['name']),
                'type' => array($this->_files['type']),
                'tmp_name' => array($this->_files['tmp_name']),
                'error' => array($this->_files['error']),
                'size' => array($this->_files['size'])
            );
            $this->_files = $NEW_FILES;
        }
        if ($_maxsize) {
            $this->maxsize = $_maxsize / 1024; //转换为kb
        }
        
        
    }

    public function checkError() {
        //此次上传的文件数量
        $this->filesNumber = (int) @count($this->_files['name']);
        if ($this->filesNumber <= 0) {
            $this->setError('errorNum', -2);
            return false;
        }

        //check文件大小
        if (!$this->checkFileSize()) {
            return false;
        }

        //check文件类型
        if (!$this->checkFileType()) {
            return false;
        }
        return true;
    }

    private function setError($key, $val) {
        $this->$key = $val;
    }

    /**
     * 获取错误信息
     * @return string
     */
    public function getErrorMsg() {
        if ($this->originName) {
            $errorMsg = "上传文件<font color='red'>{$this->originName}</font>时出错：";
        } else {
            $errorMsg = '';
        }

        switch ($this->errorNum) {
            case 1: $errorMsg .= "参数错误,与Input File控件名不一致！";
                break;
            case -1: $errorMsg .= "参数错误,上传文件控件名不能为空！";
                break;
            case -2: $errorMsg .= "请选择要上传的文件！";
                break;
            case -3: $errorMsg .= "文件过大，上传文件不能超过{$this->maxSize}个kb！";
                break;
            case -4: $errorMsg .= "末充许的文件类型！";
                break;
            case -5: $errorMsg .= "必须指定上传文件的路径！";
                break;
            case -6: $errorMsg .= "建立存放上传文件目录失败，请检查目录权限是否可写或重新指定上传目录！";
                break;
            case -7: $errorMsg .= "上传失败！";
                break;
        }
        return $errorMsg;
    }

    //用来检查文件上传路径
    private function checkFilePath() {
        if (empty($this->localDestPath)) {
            $this->setError('errorNum', -5);
            return false;
        }
        if (!file_exists($this->localDestPath) || !is_writable($this->localDestPath)) {
            if (!@$this->mkdir_r($this->localDestPath, 0755)) {
                $this->setError('errorNum', -6);
                return false;
            }
        }
        return true;
    }

    /**
     * 递归创建多级目录;
     */
    private function mkdir_r($path, $mode = 0755) {
        return is_dir($path) || ( self::mkdir_r(dirname($path), $mode) && @mkdir($path, $mode) );
    }

    //用来检查文件上传的大小
    private function checkFileSize() {
        for ($i = 0; $i < $this->filesNumber; $i++) {
            if ($this->_files['size'][$i] > $this->maxsize*1024) {
                $this->setError('errorNum', '-3');
                //设置不符合条件的当前源文件
                $this->originName = $this->_files['name'][$i];
                return false;
            }
        }
        return true;
    }

    //用于检查文件上传类型
    private function checkFileType() {
        for ($i = 0; $i < $this->filesNumber; $i++) {
            if (!in_array(strtolower($this->_files['type'][$i]), $this->allowtype)) {
                $this->setError('errorNum', '-4');
                //设置不符合条件的当前源文件
                $this->originName = $this->_files['name'][$i];
                return false;
            }
        }
        return true;
    }

    //从系统临时目录移动至本地项目内或本地其它目录;
    public function uploadFile() {
        //
        if(!$this->checkError()){
            return false;
        }

        if ($this->errorNum === 0) {
            for ($i = 0; $i < $this->filesNumber; $i++) {

                //check本地目标文件路径
                if (!$this->checkFilePath()) {
                    return false;
                }
                //系统临时文件
                $sysTempFile = $this->_files['tmp_name'][$i];
                //文件扩展名
                $fileType = $this->getFileExtension($this->_files['name'][$i]);
                //文件名
                $fileName = $this->getNewFileName();
                //组合目标文件
                $localDestFile = rtrim($this->localDestPath, '/') . '/' . $fileName . ".$fileType";

                //移动文件
                if (@move_uploaded_file($sysTempFile, $localDestFile)) {
                    //如果上传成功，记录该文件完整路径
                    $this->uploadSuccessFile[] = $localDestFile;
                } else {
                    $this->setError('errorNum', -7);
                    //设置不符合条件的当前源文件
                    $this->originName = $this->_files['name'][$i];
                    
                    //保证上传数据的原子性，只要其中有一张图上传失败，删除所有图片
                    $this->delFiles();
                    return false;
                }
            }
        } else {
            return false;
        }
        return true;
    }

    /**
     * 获取文件扩展名;
     */
    public function getFileExtension($fileName) {
        return pathinfo($fileName, PATHINFO_EXTENSION);
    }

    /**
     * 设置上传后的文件名称,随机文件名称;
     */
    private function getNewFileName() {
        $fileName = date("YmdHis") . rand(100, 999);
        return $fileName;
    }

    /**
     * 获取此次上传成功后的文件
     */
    public function getUploadSuccessFile() {
        return $this->uploadSuccessFile;
    }
    
    /**
     * 删除文件;
     */
    public function delFiles($files=null){
        if($files){
            $this->uploadSuccessFile=$files;
        }
        if($this->uploadSuccessFile && is_array($this->uploadSuccessFile)){
            foreach($this->uploadSuccessFile as $f){
                @unlink($f);
            }
        }elseif($this->uploadSuccessFile && !is_array($this->uploadSuccessFile)){
            @unlink($this->uploadSuccessFile);
        }
    }

}
