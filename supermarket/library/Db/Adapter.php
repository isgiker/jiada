<?php

/**
 * @abstract 此类库是把所有的MYSQL链接方式整合在一起，通过API统一调用其它类库。
 * @author Vic Shiwei <isgiker@gmail.com>
 * @copyright The copyright of this framework belongs to the author. Please keep reproduced.
 */
class Db_Adapter {

    /**
     * The connector resource     *
     * @var resource
     */
    protected $_resource = '';

    /**
     * The last query cursor
     * 最后一个查询游标
     * @var resource
     */
    protected $_cursor = null;

    /**
     * The limit for the query     *
     * @var int
     */
    protected $_limit = 0;

    /**
     * The for offset for the limit
     * 对于偏移量限制
     * @var int
     */
    protected $_offset = 0;

    /**
     * The number of queries performed by the object instance
     * 数的查询对象的实例执行
     * @var int
     */
    protected $_ticker = 0;

    /**
     * A log of queries
     * 日志的查询
     * @var array
     */
    protected $_log = null;

    /**
     * The query sql string     *
     * @var string
     * */
    protected $_sql = '';

    /**
     * The prefix used on all database tables     *
     * @var string
     */
    protected $_table_prefix = 'jf_';

    /**
     * The database error number     *
     * @var int
     * */
    protected $_errorNum = 0;

    /**
     * The database error message     *
     * @var string
     */
    protected $_errorMsg = '';

    /**
     * Debug option     *
     * @var boolean
     */
    protected $_debug = 0;

    /**
     *  The null/zero date string     *
     * @var string
     */
    protected $_nullDate = '0000-00-00 00:00:00';

    /**
     * The fields that are to be quote     *
     * @var array
     * @since	1.5
     */
    protected $_quoted = null;

    /**
     *  Legacy compatibility
     * @var bool
     * @since	1.5
     */
    protected $_hasQuoted = null;
    protected $charset = 'utf8';

    function __construct($options) {

        $this->charset = $options['charset'];

        $prefix = array_key_exists('dbprefix', $options) ? $options['dbprefix'] : 'jf_';
        $this->_table_prefix = $prefix;
        $this->_ticker = 0;
        $this->_errorNum = 0;
        $this->_log = array();
        $this->_quoted = array();
        $this->_hasQuoted = false;
        $this->setSupport();
        // Register faked "destructor" in PHP4 to close all connections we might have made
        if (version_compare(PHP_VERSION, '5') == -1) {
            register_shutdown_function(array(&$this, '__destruct'));
        }
    }

    /**
     * 实例化：返回数据库对象的引用，如果不存在就创建     *
     * The 'driver' 根据数据库驱动类型创建数据库对象     *
     * @默认为PDO
     */
    public static function getInstance($options = array()) {
        static $instances;
        if (!isset($instances)) {
            $instances = array();
        }
        $signature = serialize($options);
        if (empty($instances[$signature])) {
            if (!$options['driver']) {
                $options['driver'] = 'Pdo_Ext';
            } elseif ($options['driver'] == 'pdo') {
                //pdo不能作为类名,与系统类文件冲突;
                $options['driver'] = 'Pdo_Ext';
            }
            $driver = $options['driver'];

            $path = dirname(__FILE__) . DS . 'Driver' . DS . str_replace('_', DS, $options['driver']) . '.php';

            if (file_exists($path)) {
                require_once($path);
            } else {
                die('Error:404 Not find! Path:' . $path);
            }

            $adapter = 'Db_Driver_'.$driver;
            $instance = new $adapter($options);
            $instances[$signature] = & $instance;
        }

        return $instances[$signature];
    }
    
    

    /**
     * Adapter object destructor     *
     * @abstract
     * @access private
     * @return boolean
     * @since 1.5
     */
    function __destruct() {
        return true;
    }

    /**
     * Sets the SQL query string for later execution.
     *
     * This function replaces a string identifier <var>$prefix</var> with the
     * string held is the <var>_table_prefix</var> class variable.
     *
     * @access public
     * @param string The SQL query
     * @param string The offset to start selection
     * @param string The number of results to return
     * @param string The common table prefix
     */
    function setQuery($sql, $offset = 0, $limit = 0) {
        $this->_sql = $sql;
        $this->_limit = (int) $limit;
        $this->_offset = (int) $offset;
    }

    /* ------------------------------------------------------查看基本信息-------------------------------------------------------- */

    /**
     * Get the active query
     *
     * @access public
     * @return string The current value of the internal SQL vairable
     */
    function getQuery() {
        return $this->_sql;
    }

    /**
     * Get the database null date
     *
     * @access public
     * @return string Quoted null/zero date string
     */
    function getNullDate() {
        return $this->_nullDate;
    }

    /**
     * Get the database table prefix
     *
     * @access public
     * @return string The database prefix
     */
    function getPrefix() {
        return $this->_table_prefix;
    }

    /**
     * Get a database error log
     *
     * @access public
     * @return array
     */
    function getLog() {
        return $this->_log;
    }

    /**
     * Get the total number of queries made
     *
     * @access public
     * @return array
     */
    function getTicker() {
        return $this->_ticker;
    }

    /* ------------------------------------------------------MYSQL错误处理-------------------------------------------------------- */

    /**
     * Get the error message
     *
     * @access public
     * @return string The error message for the most recent query
     */
    function getErrorMsg($escaped = false) {
        if ($escaped) {
            return addslashes($this->_errorMsg);
        } else {
            return $this->_errorMsg;
        }
    }

    /**
     * Get the error number
     *
     * @access public
     * @return int The error number for the most recent query
     */
    function getErrorNum() {
        return $this->_errorNum;
    }

    /**
     * ADODB compatability function
     *
     * @since 1.5
     */
    function ErrorMsg() {
        if ($this->_debug) {
            return $this->getErrorMsg();
        }
    }

    /**
     * ADODB compatability function
     *
     * @since 1.5
     */
    function ErrorNo() {
        if ($this->_debug) {
            return $this->getErrorNum();
        }
    }

    /**
     * Print out an error statement
     * 打印错误生命
     * @param boolean If TRUE, displays the last SQL statement sent to the database
     * @return string A standised error message
     */
    function stderr($showSQL = false) {
        if ($this->_errorNum != 0) {
            return "DB function failed with error number $this->_errorNum"
                    . "<br /><font color=\"red\">$this->_errorMsg</font>"
                    . ($showSQL ? "<br />SQL = <pre>$this->_sql</pre>" : '');
        } else {
            return "DB function reports no errors";
        }
    }

}
