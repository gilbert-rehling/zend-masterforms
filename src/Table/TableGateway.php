<?php

namespace Masterforms\Table;

use Masterforms\Stdlib;
use Masterforms\Entity\EntityInterface;
use Masterforms\EventManager\EventManagerAwareTrait;
use Masterforms\Table\AbstractTableGateway as ZendTableGateway;

use \Traversable;
use \PDO;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\Hydrator\HydratorInterface;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\Metadata\Metadata;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\ResultSet\HydratingResultSet;
use Zend\Db\ResultSet\ResultSetInterface;
use Zend\Db\ResultSet\AbstractResultSet;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\TableIdentifier;
use Zend\Db\TableGateway\Feature;
use Zend\Db\TableGateway\Exception;

class TableGateway extends ZendTableGateway
{
    /**
     * Table name
     *
     * @var string
     */
    protected $table;

    /**
     * Table primary key column name
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Instance of Zend\Db\Sql\Select
     *
     * @var \Zend\Db\Sql\Select
     */
    protected $select;

    /**
     * Collection of items to store in the database
     * @var array
     */
    protected $collection = array();

    /**
     * Get a single entity using primary key id
     *
     * @param string|int $id
     * @return \Savve\Entity\EntityInterface
     */
    public function getById ($id)
    {
        return $this->findById($id);
    }

    /**
     * Find the row data given the primary key
     *
     * @param string|int $id
     * @return \Savve\Entity\EntityInterface
     */
    public function findById ($id)
    {
        $select = $this->newSelect();
        $select->where->equalTo("{$this->getTable()}.{$this->getPrimaryKey()}", $id);
        $result = $this->selectWith($select);
        return count($result) ? $result->current() : null;
    }

    public function get ()
    {
        if ($this->select) {
            $select = $this->select;
        }
        else {
            $select = $this->getSql()
                ->select();
        }

        return $this->selectWith($select);
    }


    /**
     * Get current instance of \Zend\Db\Sql\Select
     *
     * @return \Zend\Db\Sql\Select
     */
    public function getSelect ()
    {
        if (null == $this->select)
            $this->select = $this->newSelect();
        return $this->select;
    }

    /**
     * Executes the Select query
     * @return ResultSet
     */
    public function executeSelect (Select $select)
    {
        /* @var $featureSet \Zend\Db\TableGateway\Feature\FeatureSet */
        $featureSet = $this->featureSet;

        $selectState = $select->getRawState();
        if ($selectState['table'] != $this->table) {
            throw new \RuntimeException('The table name of the provided select object must match that of the table');
        }

        if ($selectState['columns'] == array(Select::SQL_STAR)
                && $this->columns !== array()) {
            $select->columns($this->columns);
        }

        // apply preSelect features
        $featureSet->apply('preSelect', array($select));

        // prepare and execute
        $statement = $this->sql->prepareStatementForSqlObject($select);


        $result = $statement->execute();

        // build result set
        $resultSet = clone $this->resultSetPrototype;
        $resultSet->initialize($result);

        if ($resultSet instanceof AbstractResultSet) {
            $resultSet->buffer();
            $resultSet->rewind();
        }

        // apply postSelect features
        $featureSet->apply('postSelect', array($statement, $result, $resultSet));

        return $resultSet;
    }

    /**
     * Set the table name
     *
     * @param string $table
     * @return \Savve\Table\TableGateway
     */
    public function setTable ($table)
    {
        $this->table = $table;
        return $this;
    }

    /**
     * Setter for the database adapter
     *
     * @param AdapterInterface $adapter
     * @return \Savve\Table\TableGateway
     */
    public function setAdapter (AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
        return $this;
    }

    /**
     * Set the feature set
     *
     * @param array|Feature\AbstractFeature|Feature\FeatureSet $features
     * @return \Savve\Table\TableGateway
     */
    public function setFeatureSet ($features)
    {
        if ($features !== null) {
            if ($features instanceof Feature\AbstractFeature) {
                $features = array(
                    $features
                );
            }
            if (is_array($features)) {
                $this->featureSet = new Feature\FeatureSet($features);
            }
            elseif ($features instanceof Feature\FeatureSet) {
                $this->featureSet = $features;
            }
            else {
                throw new Exception\InvalidArgumentException('TableGateway expects $feature to be an instance of an AbstractFeature or a FeatureSet, or an array of AbstractFeatures');
            }
        }
        else {
            $this->featureSet = new Feature\FeatureSet();
        }

        return $this;
    }

    /**
     * Set the select result prototype
     *
     * @param ResultSetInterface $resultSetPrototype
     * @return \Savve\Table\TableGateway
     */
    public function setResultSetPrototype (ResultSetInterface $resultSetPrototype)
    {
        $this->resultSetPrototype = ($resultSetPrototype) ?  : new ResultSet();
        return $this;
    }

    /**
     * Insert/Update
     * @param array|EntityInterface
     * @return array|EntityInterface
     */
    public function save ($set)
    {
        // if primary key exists, update
        if ($this->getPrimaryKey() && (isset($set[$this->getPrimaryKey()]) && $set[$this->getPrimaryKey()]) && ($found = $this->findById($set[$this->getPrimaryKey()]))) {
            $this->update($set, array($this->getPrimaryKey() => $set[$this->getPrimaryKey()]));
        }

        // if not, then insert as new
        else{
            // remove the primary key from the entity
            if (isset($set[$this->getPrimaryKey()]))
            	unset($set[$this->getPrimaryKey()]);

            $this->insert($set);
            if ($id = $this->getLastInsertValue()) {
                $set[$this->getPrimaryKey()] = $id;
            }
        }
        return $set;
    }

    /**
     * Persist entity/entities in the object
     * @param EntityInterface|array|\Traversable
     * @return \Savve\Table\TableGateway
     */
    public function persist ($set)
    {
        if ($set instanceof \Iterator) {
            foreach ($set as $item) {
                $this->collection[] = $item;
            }
            return $this;
        }

        $this->collection[] = $set;
        return $this;
    }

    /**
     * Insert/Update entities from the local storage to the database table
     * @return \Savve\Table\TableGateway
     */
    public function flush ()
    {
        try {
        	$this->beginTransaction();
        	foreach ($this->collection as $key => $entity) {
        	    $this->save($entity);

        	    // remove from the local storage
        	    unset($this->collection[$key]);
        	}
        	$this->commit();
        	return $this;
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
        return $this;
    }

    /**
     * Returns the table name
     */
    public function __toString ()
    {
        return $this->table;
    }

    /**
     * Get the primary key column name
     *
     * @return string
     */
    public function getPrimaryKey ()
    {
        return $this->primaryKey;
    }

    /**
     * Sets the primary key column name
     *
     * @return \Savve\Table\TableGateway
     */
    public function setPrimaryKey ($primaryKey)
    {
        $this->primaryKey = $primaryKey;
        return $this;
    }

    /**
     * Get the adapter platform
     * @return \Zend\Db\Adapter\Platform\Mysql
     */
    public function getPlatform ()
    {
        return $this->platform();
    }
}