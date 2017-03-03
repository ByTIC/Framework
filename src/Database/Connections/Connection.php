<?php

namespace Nip\Database\Connections;

use Exception;
use Nip\Database\Adapters\AbstractAdapter;
use Nip\Database\Query\AbstractQuery as AbstractQuery;
use Nip\Database\Query\Select as SelectQuery;

/**
 * Class Connection
 * @package Nip\Database
 */
class Connection
{

    /**
     * The active PDO connection.
     *
     * @var PDO
     */
    protected $pdo;

    /**
     * The name of the connected database.
     *
     * @var string
     */
    protected $database;
    /**
     * The table prefix for the connection.
     *
     * @var string
     */
    protected $tablePrefix = '';
    /**
     * The database connection configuration options.
     *
     * @var array
     */
    protected $config = [];

    protected $_adapter = null;

    protected $_connection;
    protected $metadata;
    protected $_query;
    protected $_queries = [];

    /**
     * Create a new database connection instance.
     *
     * @param  \PDO|\Closure $pdo
     * @param  string $database
     * @param  string $tablePrefix
     * @param  array $config
     */
    public function __construct($pdo, $database = '', $tablePrefix = '', array $config = [])
    {
        $this->pdo = $pdo;

        // First we will setup the default properties. We keep track of the DB
        // name we are connected to since it is needed when some reflective
        // type commands are run such as checking whether a table exists.
        $this->database = $database;

        $this->tablePrefix = $tablePrefix;
        $this->config = $config;

        // We need to initialize a query grammar and the query post processors
        // which are both very important parts of the database abstractions
        // so we initialize these to their default values while starting.
//        $this->useDefaultQueryGrammar();
//        $this->useDefaultPostProcessor();
    }

    /**
     * Connects to SQL server
     *
     * @param string $host
     * @param string $user
     * @param string $password
     * @param string $database
     * @param bool $newLink
     * @return static
     */
    public function connect($host, $user, $password, $database, $newLink = false)
    {
        if (!$this->_connection) {
            try {
                $this->_connection = $this->getAdapter()->connect($host, $user, $password, $database, $newLink);
                $this->setDatabase($database);
            } catch (Exception $e) {
                $e->log();
            }
        }

        return $this;
    }

    /**
     * @return AbstractAdapter
     */
    public function getAdapter()
    {
        if ($this->_adapter == null) {
            $this->initAdapter();
        }

        return $this->_adapter;
    }

    /**
     * @param $adapter
     */
    public function setAdapter($adapter)
    {
        $this->_adapter = $adapter;
    }

    public function initAdapter()
    {
        $this->setAdapterName('MySQLi');
    }

    /**
     * @param $name
     */
    public function setAdapterName($name)
    {
        $this->setAdapter($this->newAdapter($name));
    }

    /**
     * @param $name
     * @return AbstractAdapter
     */
    public function newAdapter($name)
    {
        $class = static::getAdapterClass($name);

        return new $class();
    }

    /**
     * @param $name
     * @return string
     */
    public static function getAdapterClass($name)
    {
        return '\Nip\Database\Adapters\\' . $name;
    }

    /**
     * @return string
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * @param mixed $database
     */
    public function setDatabase($database)
    {
        $this->database = $database;
    }

    /**
     * @return Metadata\Manager
     */
    public function getMetadata()
    {
        if (!$this->metadata) {
            $this->metadata = new Metadata\Manager();
            $this->metadata->setConnection($this);
        }

        return $this->metadata;
    }

    /**
     * Prefixes table names
     *
     * @param string $table
     * @return string
     */
    public function tableName($table)
    {
        return $table;
    }

    /**
     * @param string $type optional
     * @return SelectQuery
     */
    public function newSelect()
    {
        return $this->newQuery('select');
    }

    /**
     * @param string $type optional
     * @return AbstractQuery
     */
    public function newQuery($type = "select")
    {
        $className = '\Nip\Database\Query\\' . inflector()->camelize($type);
        $query = new $className();
        /** @var AbstractQuery $query */
        $query->setManager($this);

        return $query;
    }

    /**
     * Executes SQL query
     *
     * @param mixed|AbstractQuery $query
     * @return Result
     */
    public function execute($query)
    {
        $this->_queries[] = $query;

        $sql = is_string($query) ? $query : $query->getString();

        $resultSQL = $this->getAdapter()->execute($sql);
        $result = new Result($resultSQL, $this->getAdapter());
        $result->setQuery($query);

        return $result;
    }

    /**
     * Gets the ID of the last inserted record
     * @return int
     */
    public function lastInsertID()
    {
        return $this->getAdapter()->lastInsertID();
    }

    /**
     * Gets the number of rows affected by the last operation
     * @return int
     */
    public function affectedRows()
    {
        return $this->getAdapter()->affectedRows();
    }

    /**
     * Disconnects from server
     */
    public function disconnect()
    {
        if ($this->_connection) {
            try {
                $this->getAdapter()->disconnect();
            } catch (Exception $e) {
                $e->log();
            }
        }
    }

    public function describeTable($table)
    {
        return $this->getAdapter()->describeTable($this->protect($table));
    }

    /**
     * Adds backticks to input
     *
     * @param string $input
     * @return string
     */
    public function protect($input)
    {
        return str_replace("`*`", "*", '`' . str_replace('.', '`.`', $input) . '`');
    }

    public function getQueries()
    {
        return $this->_queries;
    }
}