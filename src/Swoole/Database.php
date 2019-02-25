<?php
namespace Swoole;
/**
 * 数据库基类
 * @package SwooleSystem
 * @subpackage database
 */

/**
 * Database Driver接口
 * 数据库驱动类的接口
 *
 */
interface IDatabase
{
	function query($sql);
	function connect();
	function close();
	function lastInsertId();
    function getAffectedRows();
    function errno();
    function quote($str);
}
/**
 * Database Driver接口
 * 数据库结果集的接口，提供2种接口
 * fetch 获取单条数据
 * fetch 获取全部数据到数组
 */
interface IDbRecord
{
	function fetch();
	function fetchall();
}

/**
 * Database类，处理数据库连接和基本的SQL组合
 * 提供4种接口，query  insert update delete
 * @method connect
 * @method close
 * @method quote $str
 * @method errno
 */
class Database
{
	public $debug = false;
	public $read_times = 0;
	public $write_times = 0;

    /**
     * @var IDatabase
     */
	public $_db = null;
    protected $lastSql = '';

	const TYPE_MYSQL   = 1;
	const TYPE_MYSQLi  = 2;
	const TYPE_PDO     = 3;
	const TYPE_COMYSQL = 4;
	const TYPE_COHOOKMYSQL = 5;

    function __construct($db_config)
    {
        switch ($db_config['type'])
        {
            case self::TYPE_MYSQL:
                $this->_db = new Database\MySQL($db_config);
                break;
            case self::TYPE_MYSQLi:
                $this->_db = new Database\MySQLi($db_config);
                break;
            case self::TYPE_COMYSQL:
                $this->_db = new Coroutine\Component\MySQL($db_config);
                break;
            case self::TYPE_COHOOKMYSQL:
                $this->_db = new Coroutine\Component\Hook\MySQL($db_config);
                break;
            default:
                $this->_db = new Database\PdoDB($db_config);
                break;
        }
    }

    /**
     * 初始化参数
     */
    function __init()
    {
        $this->check_status();
        $this->read_times = 0;
        $this->write_times = 0;
    }

    /**
     * 检查连接状态，如果连接断开，则重新连接
     */
    function check_status()
    {
        if (!$this->_db->ping())
        {
            $this->_db->close();
            $this->_db->connect();
        }
    }

    /**
     * 启动事务处理
     * @return bool
     */
    function start()
    {
        if ($this->query('set autocommit = 0') === false)
        {
            return false;
        }
        return $this->query('START TRANSACTION');
    }

	/**
	 * 提交事务处理
	 * @return bool
	 */
	function commit()
	{
        if ($this->query('COMMIT') === false)
        {
            return false;
        }
        $this->query('set autocommit = 1');
        return true;
	}

	/**
	 * 事务回滚
	 * @return bool
	 */
	function rollback()
	{
        if ($this->query('ROLLBACK') === false)
        {
            return false;
        }
        $this->query('set autocommit = 1');
		return true;
	}

    /**
     * 执行一条SQL语句
     * @param $sql
     * @return \Swoole\Database\MySQLiRecord
     */
    public function query($sql)
    {
        if ($this->debug)
        {
            echo "$sql<br />\n<hr />";
        }
        $this->read_times += 1;
        $this->lastSql = $sql;
        return $this->_db->query($sql);
    }

    /**
     * 获取最近一次执行的SQL语句
     * @return string
     */
    function getSql()
    {
        return $this->lastSql;
    }

    /**
     * 调用$driver的自带方法
     * @param $method
     * @param array $args
     * @return mixed
     */
    function __call($method, $args = array())
    {
        return $this->_db->{$method}(...$args);
    }
}
