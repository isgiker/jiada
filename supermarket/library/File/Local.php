<?php
/**
 * @abstract 文件操作
 * @author 石维(shiwei)
 * @copyright 版权归Jframe所有
 * @version 1.0
 * @2011-01-14
 */
class File_Local {
    /*
     * 递归创建多级目录;
     */

    static public function mkdir_r($path, $mode = 0755) {
        return is_dir($path) || ( self::mkdir_r(dirname($path), $mode) && @mkdir($path, $mode) );
    }

}