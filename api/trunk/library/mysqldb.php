<?php

class Mysqldb {

    public $linkid;

    public function connect($servername, $dbusername, $dbpassword, $dbname, $dbcharset = 'utf-8') {
        if (!is_resource($this->linkid)) {
            if (!$this->linkid = mysql_connect($servername, $dbusername, $dbpassword)) {
                die(mysql_error());
            }
            if (mysql_get_server_info($this->linkid) > '5.0.1') {
                mysql_query("SET sql_mode=''", $this->linkid);
            }

            if ($dbcharset && in_array(strtolower($dbcharset), array('gbk', 'big5', 'utf-8'))) {
                $dbcharset = str_replace('-', '', $dbcharset);
                mysql_query("SET names $dbcharset", $this->linkid);
            }

            if ($dbname) {
                mysql_select_db($dbname, $this->linkid);
            }
        }
    }

    public function query($sql, $type = '') {
        $func = $type == 'UNBUFFERED' && @function_exists('mysql_unbuffered_query') ?
                'mysql_unbuffered_query' : 'mysql_query';
        if (!($query = $func($sql, $this->linkid)) && $type != 'SILENT') {
            //die('MySQL Query Error:' . $sql);
            return false;
        }
        return $query;
    }

    public function getall($sql) {
        $res = $this->query($sql);
        if ($res !== false) {
            $arr = array();
            while ($row = mysql_fetch_assoc($res)) {
                $arr[] = $row;
            }
            return $arr;
        } else {
            return false;
        }
    }

    public function getrow($sql) {
        $res = $this->query($sql);
        if ($res !== false) {
            return mysql_fetch_assoc($res);
        } else {
            return false;
        }
    }

    public function getone($sql) {
        $res = $this->query($sql);
        if ($res !== false) {
            $row = mysql_fetch_row($res);
            return $row[0];
        } else {
            return false;
        }
    }

    public function insertId() {
        $id = mysql_insert_id($this->linkid);
        return $id;
    }

    public function close() {
        return mysql_close($this->linkid);
    }

    public function __destruct() {
        mysql_close($this->linkid);
    }

}

?>