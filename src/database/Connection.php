<?php

namespace Nip\Database;

use Nip\Database\Adapters\AbstractAdapter;
use Nip_DB_Query_Select as SelectQuery;
use Nip\Database\Query\_Abstract as Query;

class Connection
{

    protected $_adapter = null;

    protected $_connection;
    protected $_database;
    protected $metadata;
    protected $_query;
    protected $_queries = array();


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
            } catch (\Nip_DB_Exception $e) {
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

    public function setAdapter($adapter)
    {
        $this->_adapter = $adapter;
    }

    public function setAdapterName($name)
    {
        $this->setAdapter($this->newAdapter($name));
    }

    public function initAdapter()
    {
        $this->setAdapterName('MySQLi');
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

    public static function getAdapterClass($name)
    {
        return 'Nip_DB_Adapters_' . $name;
    }

    public function getDatabase()
    {
        return $this->_database;
    }

    /**
     * @param mixed $database
     */
    public function setDatabase($database)
    {
        $this->_database = $database;
    }

    /**
     * @return \Nip_Db_Metadata
     */
    public function getMetadata()
    {
        if (!$this->metadata) {
            $this->metadata = new \Nip_Db_Metadata();
            $this->metadata->setConnection($this);
        }
        return $this->metadata;
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
     * @return Query
     */
    public function newQuery($type = "select")
    {
        $className = '\Nip_DB_Query_' . inflector()->camelize($type);
        $query = new $className();
        /** @var Query $query */
        $query->setManager($this);

        return $query;
    }

    /**
     * Executes SQL query
     *
     * @param mixed $query
     * @return \Nip_DB_Result
     */
    public function execute($query)
    {
        $this->_queries[] = $query;

        $query = (string)$query;
        $query = $this->getAdapter()->execute($query);
        $result = new \Nip_DB_Result($query, $this->getAdapter());

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
            } catch (\Nip_DB_Exception $e) {
                $e->log();
            }
        }
    }

    public function describeTable($table)
    {
        return $this->getAdapter()->describeTable($this->protect($table));
    }

    public function getQueries()
    {
        return $this->_queries;
    }

}