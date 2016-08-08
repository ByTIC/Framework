<?php

namespace Nip\Records\_Abstract;

use Nip\HelperBroker;
use Nip\Records\Relations\Relation;

/**
 * Class Row
 * @package Nip\Records\_Abstract
 *
 * @method \Nip_Helper_Url URL()
 */
abstract class Row extends \Nip_Object
{

    protected $_name = null;
    protected $_manager = null;
    protected $_managerName = null;

    protected $_dbData = array();
    protected $_helpers = array();


    /**
     * The loaded relationships for the model.
     * @var array
     */
    protected $relations = [];

    public function __construct()
    {
    }


    /**
     * Overloads Ucfirst() helper
     *
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {

        if (substr($name, 0, 3) == "get") {
            $relation = $this->getRelation(substr($name, 3));
            if ($relation) {
                return $relation->getResults();
            }
        }

        if ($name === ucfirst($name)) {
            return $this->getHelper($name);
        }

        trigger_error("Call to undefined method $name", E_USER_ERROR);
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        if ($this->_name == null) {
            $this->_name = inflector()->unclassify(get_class($this));
        }
        return $this->_name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->_name = $name;
    }

    /**
     * @param bool|array $data
     */
    public function writeDBData($data = false)
    {
        foreach ($data as $key => $value) {
            $this->_dbData[$key] = $value;
        }
    }

    public function getPrimaryKey()
    {
        $pk = $this->getManager()->getPrimaryKey();
        return $this->$pk;
    }

    public function insert()
    {
        $pk = $this->getManager()->getPrimaryKey();
        $this->$pk = $this->getManager()->insert($this);
        return $this->$pk > 0;
    }

    public function update()
    {
        $return = $this->getManager()->update($this);
        return $return;
    }

    public function save()
    {
        $this->getManager()->save($this);
    }

    public function saveRecord()
    {
        $this->getManager()->save($this);
    }

    public function saveRelations()
    {
        $relations = $this->getRelations();
        foreach ($relations as $relation) {
            /** @var Relation $relation */
            $relation->save();
        }
    }

    public function delete()
    {
        $this->getManager()->delete($this);
    }

    public function isInDB()
    {
        $pk = $this->getManager()->getPrimaryKey();
        return $this->$pk > 0;
    }

    public function exists()
    {
        return $this->getManager()->exists($this);
    }

    public function toArray()
    {
        $vars = get_object_vars($this);
        return $vars['_data'];
    }

    public function toJSON()
    {
        return json_encode($this->toArray());
    }

    public function toApiArray()
    {
        $data = $this->toArray();
        return $data;
    }

    /**
     * @param bool|array $data
     */
    public function writeData($data = false)
    {
        foreach ($data as $key => $value) {
            $this->__set($key, $value);
        }
    }

    /**
     * @return \Nip\Records\RecordManager
     */
    public function getManager()
    {
        if ($this->_manager == null) {
            $this->initManager();
        }

        return $this->_manager;
    }

    public function initManager()
    {
        $class = $this->getManagerName();
        $this->_manager = call_user_func(array($class, 'instance'));
    }

    public function setManager($manager)
    {
        $this->_manager = $manager;
    }

    public function getManagerName()
    {
        if ($this->_managerName == null) {
            $this->inflectManagerName();
        }
        return $this->_managerName;
    }

    public function initManagerName()
    {
        $this->_managerName = $this->inflectManagerName();
    }

    public function inflectManagerName()
    {
        return ucfirst(inflector()->pluralize(get_class($this)));
    }

    public function getHelper($name)
    {
        return HelperBroker::get($name);
    }

    /**
     * @return array
     */
    public function getRelations()
    {
        return $this->relations;
    }

    /**
     * @param $relationName
     * @return Relation|null
     */
    public function getRelation($relationName)
    {
        if (!$this->hasRelation($relationName)) {
            $this->initRelation($relationName);
        }
        return $this->relations[$relationName];
    }

    public function hasRelation($key)
    {
        return array_key_exists($key, $this->relations);
    }

    public function initRelation($relationName)
    {
        if (!$this->getManager()->hasRelation($relationName)) {
            return;
        }
        $this->relations[$relationName] = $this->newRelation($relationName);
    }

    public function newRelation($relationName)
    {
        $relation = clone $this->getManager()->getRelation($relationName);
        $relation->setItem($this);
        return $relation;
    }

    public function updateDataFromRecord($record)
    {
        $data = $record->toArray();
        $this->writeData($data);

        unset($this->{$this->getManager()->getPrimaryKey()}, $this->created);
    }

    public function getClone()
    {
        $clone = $this->getManager()->getNew();
        $clone->updateDataFromRecord($this);

        unset($clone->{$this->getManager()->getPrimaryKey()}, $clone->created);
        return $clone;
    }

    public function getCloneWithRelations()
    {
        $item = $this->getClone();
        $this->cloneRelations($item);

        return $item;
    }

    /**
     * @param Row $from
     * @return Row
     */
    public function cloneRelations($from)
    {
        return $this->getManager()->cloneRelations($from, $this);
    }
}