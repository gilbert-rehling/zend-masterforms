<?php

namespace Masterforms\Table;

use \ArrayObject;
use Masterforms\Stdlib;

use Zend\Hydrator\Aggregate\AggregateHydrator;
use Zend\Hydrator\HydratorInterface;
use Zend\EventManager\EventManager;
use Zend\Db\Metadata\Metadata;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\ResultSet\HydratingResultSet;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\ResultSet\ResultSetInterface;
use Zend\Db\Adapter\Platform\Mysql;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\Adapter\Driver\Pdo\Result as PDOResult;
use Zend\Db\TableGateway\Feature;
use Zend\Db\TableGateway\Exception;
use Zend\Db\TableGateway\AbstractTableGateway as ZendTableGateway;

abstract class AbstractTableGateway extends ZendTableGateway
{

    /**
     * Hydrator to use with the HydratingResultSet
     *
     * @var HydratorInterface
     */
    protected $hydrator;

    /**
     * Entity object to use with the HydratingResultSet
     *
     * @var \ArrayObject
     */
    protected $entity;

    /**
     * Constructor
     *
     * @param string $table
     * @param AdapterInterface $adapter
     * @param array $features
     * @param ResultSetInterface $resultSetPrototype
     * @param Sql $sql
     */
    public function __construct ($table = null, AdapterInterface $adapter = null, $features = null, ResultSetInterface $resultSetPrototype = null, Sql $sql = null)
    {
        // set table name
        if (null !== $table) {
            $this->table = $table;
        }

        // set adapter
        if (null !== $adapter) {
            $this->adapter = $adapter;
        }
        if (!$this->adapter) {
            $this->adapter = Feature\GlobalAdapterFeature::getStaticAdapter();
        }

        // process features
        if (empty($features) || null !== $features) {
            $features = [
                new Feature\GlobalAdapterFeature(),
                new Feature\EventFeature()
            ];
        }
        if (null !== $features) {
            if ($features instanceof Feature\AbstractFeature) {
                $features = [
                    $features
                ];
            }

            if (is_array($features)) {
                $this->featureSet = new Feature\FeatureSet($features);
            }
            elseif ($features instanceof Feature\FeatureSet) {
                $this->featureSet = $features;
            }
            else {
                throw new Exception\InvalidArgumentException('TableGateway expects $features to be an instance of an AbstractFeature or a FeatureSet, or an array of AbstractFeatures');
            }
        }
        else {
            // use default features
            $this->featureSet = new Feature\FeatureSet([
                new Feature\GlobalAdapterFeature(),
                new Feature\EventFeature()
            ]);
        }

        // make sure the EventFeature is included
        if (!$this->featureSet->getFeatureByClassName('Zend\Db\TableGateway\Feature\EventFeature')) {
            $this->featureSet->addFeature(new Feature\EventFeature(StaticEventManager::getInstance()));
        }

        $identifiers = array_values(class_parents($this));
        $identifiers[] = get_class($this);
        $identifiers[] = substr(get_class($this), 0, strrpos(get_class($this), '\\'));
        $eventFeature = $this->featureSet->getFeatureByClassName('Zend\Db\TableGateway\Feature\EventFeature');
        $eventFeature->getEventManager()
            ->addIdentifiers($identifiers);

        // result prototype
        $this->resultSetPrototype = ($resultSetPrototype) ?  : new ResultSet();

        // Sql object (factory for select, insert, update, delete)
        $this->sql = ($sql) ?  : new Sql($this->adapter, $this->table);

        // initialise
        $this->initialize();
    }

    /**
     * Fetch a single value
     *
     * @param Select $select
     */
    public function fetchOne (Select $select, array $params = [])
    {
        $this->featureSet->apply('preSelect', array($select));

        $adapter = $this->getAdapter();
        $sql = new Sql($adapter);
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute($params);

        $resultSet = new ResultSet();
        $resultSet->initialize($result);

        /* @var $pdoStatement \PDOStatement */
        $pdoStatement = $resultSet->getDataSource()->getResource();
        $return = $pdoStatement->fetchColumn();
        $pdoStatement->closeCursor();

        $this->featureSet->apply('postSelect', array($statement, $result, $resultSet));

        return $return;
    }

    /**
     * Fetch a column
     *
     * @param Select $select
     */
    public function fetchColumn (Select $select, array $params = [])
    {
        $this->featureSet->apply('preSelect', array($select));

        $adapter = $this->getAdapter();
        $sql = new Sql($adapter);
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute($params);

        $resultSet = new ResultSet();
        $resultSet->initialize($result);

        /* @var $pdoStatement \PDOStatement */
        $pdoStatement = $resultSet->getDataSource()->getResource();
        $return = $pdoStatement->fetchAll(\PDO::FETCH_COLUMN);
        $pdoStatement->closeCursor();

        $this->featureSet->apply('postSelect', array($statement, $result, $resultSet));

        return $return;
    }

    /**
     * Fetch a single row
     *
     * @param Select $select
     */
    public function fetchRow (Select $select, array $params = [])
    {
        $this->featureSet->apply('preSelect', array($select));

        $adapter = $this->getAdapter();
        $sql = new Sql($adapter);
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute($params);

        $resultSet = new ResultSet();
        $resultSet->initialize($result);

        /* @var $pdoStatement \PDOStatement */
        $pdoStatement = $resultSet->getDataSource()->getResource();
        $return = $pdoStatement->fetch(\PDO::FETCH_ASSOC) ? : [];
        $pdoStatement->closeCursor();

        $this->featureSet->apply('postSelect', array($statement, $result, $resultSet));

        return $return;
    }

    /**
     * Fetch all results
     *
     * @param Select $select
     */
    public function fetchAll (Select $select, array $params = [])
    {
        $this->featureSet->apply('preSelect', array($select));

        $adapter = $this->getAdapter();
        $sql = new Sql($adapter);
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute($params);

        $resultSet = new ResultSet();
        $resultSet->initialize($result);

        /* @var $pdoStatement \PDOStatement */
        $pdoStatement = $resultSet->getDataSource()->getResource();
        $return = $pdoStatement->fetchAll(\PDO::FETCH_ASSOC);
        $pdoStatement->closeCursor();

        $this->featureSet->apply('postSelect', array($statement, $result, $resultSet));

        return $return;
    }

    public function fetchPairs (Select $select, array $params = [])
    {
        $this->featureSet->apply('preSelect', array($select));

        $adapter = $this->getAdapter();
        $sql = new Sql($adapter);
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute($params);

        $resultSet = new ResultSet();
        $resultSet->initialize($result);

        /* @var $pdoStatement \PDOStatement */
        $pdoStatement = $resultSet->getDataSource()->getResource();
        $return = $pdoStatement->fetchAll(\PDO::FETCH_KEY_PAIR);
        $pdoStatement->closeCursor();

        $this->featureSet->apply('postSelect', array($statement, $result, $resultSet));

        return $return;
    }

    /**
     * Fetch all rows
     *
     * @return \Zend\Db\Adapter\Driver\Pdo\Result
     */
    public function fetch ($query, $parameters = [])
    {
        if ($query instanceof ResultSet) {
            $resultSet = $query;
        }
        else {
            $resultSet = new ResultSet();
            $resultSet->initialize($this->execute($query, $parameters));
        }

        /* @var $dataSource \Zend\Db\Adapter\Driver\Pdo\Result */
        /* @var $resource \PDOStatement */

        $dataSource = $resultSet->getDataSource();
        $resource = $dataSource->getResource();
        $result = $resource->fetchAll(\PDO::FETCH_ASSOC);
        $resource->closeCursor();

        return $result;
    }

    /**
     * Execute query
     *
     * @param mixed $query (\Zend\Db\Sql\*|string) Query
     * @param mixed $parameters Parameters
     *
     * @return \Zend\Db\Adapter\Driver\Pdo\Result
     */
    public function execute ($query, $parameters = null)
    {
        $adapter = $this->getAdapter();
        if (is_string($query)) {
            $statement = $adapter->createStatement($query);
        }
        else {
            $statement = $adapter->createStatement();
            $query->prepareStatement($this->getAdapter(), $statement);
        }

        return $statement->execute($parameters);
    }

    /**
     * Execute a query string
     *
     * @param string $query
     * @param array $parameters
     * @return \Zend\Db\Adapter\Driver\Pdo\Result
     */
    public function query ($query, array $parameters = [])
    {
        /* @var $adapter \Zend\Db\Adapter\Adapter */
        /* @var $statement \Zend\Db\Adapter\Driver\Pdo\Statement */
        /* @var $result \Zend\Db\Adapter\Driver\Pdo\Result */

        $adapter = $this->getAdapter();
        if (is_string($query)) {
            $statement = $adapter->createStatement($query);
        }
        else {
            $statement = $adapter->createStatement();
            $query->prepareStatement($this->getAdapter(), $statement);
        }

        return $statement->execute($parameters);

//         $adapter = $this->getAdapter();
//         $statement = $adapter->createStatement($query, $parameters);
//         return $statement->execute();
    }

    /**
     * Create a new \Zend\Db\Sql\Select instance
     *
     * @param string $table
     * @return Select
     */
    public function newSelect ($table = null)
    {
        if (null === $table && $this->getTable()) {
            $table = $this->getTable();
        }

        // $this->select = new Select(($table) ? : $this->table);
        $this->select = new Select();
        $this->select->from(($table) ? $table : $this->table);

        return $this->select;
    }

    /**
     * Insert
     *
     * @param array $set
     * @return int
     */
    public function insert ($set)
    {
        $set = Stdlib\ObjectUtils::toArray($set);
        return parent::insert($set);
    }

    /**
     * Update
     *
     * @param array $set
     * @param string|array|closure $where
     * @return int
     */
    public function update ($set, $where = null)
    {
        $set = Stdlib\ObjectUtils::toArray($set);
        return parent::update($set, $where);
    }

    /**
     * Prepares data before insert/update
     *
     * @param array|\ArrayObject|\stdclass
     * @return \Savve\Table\TableGateway
     */
    public function prepareData ($data)
    {
        if ($data instanceof ArrayObject) {
            $resultSet = $this->getResultSetPrototype();
            $hydrator = $resultSet->getHydrator();
            if ($hydrator instanceof HydratorInterface){
                $data = $hydrator->extract($data);
            }
            // convert object to array
            $data = Stdlib\ObjectUtils::toArray($data);
        }

        // extract only data that matches the table columns
        $columns = $this->getColumns();
        $data = array_intersect_key($data, array_combine($columns, $columns));

        return $data;
    }

    /**
     * Get table columns
     *
     * @return array
     */
    public function getColumns ()
    {
        if (empty($this->columns)) {
            $metadata = new Metadata($this->getAdapter());
            $this->columns = $metadata->getColumnNames($this->getTable());
        }
        return $this->columns;
    }

    /**
     * Begin transaction
     *
     * @return void
     */
    public function beginTransaction ()
    {
        return $this->getAdapter()
            ->getDriver()
            ->getConnection()
            ->beginTransaction();
    }

    /**
     * Commit transaction
     *
     * @return void
     */
    public function commit ()
    {
        return $this->getAdapter()
            ->getDriver()
            ->getConnection()
            ->commit();
    }

    /**
     * Rollback transaction
     *
     * @return void
     */
    public function rollback ()
    {
        return $this->getAdapter()
            ->getDriver()
            ->getConnection()
            ->rollback();
    }

    /**
     * Quotes a table column name for use in query
     *
     * @param string $name
     * @return string
     */
    public function quoteIdentifier ($name)
    {
        $adapter = $this->getAdapter();
        $platform = $adapter->getPlatform();
        return $platform->quoteIdentier($name);
    }

    /**
     * Quotes a parameter name for use in query
     *
     * @param string $name
     * @return string
     */
    public function formatParameterName ($name)
    {
        $adapter = $this->getAdapter();
        $driver = $adapter->getDriver();
        return $driver->formatParameterName($name);
    }

    /**
     * User HydratingResultSet
     *
     * @param  Result $result
     * @return HydratingResultSet
     */
    public function hydratingResultSet ($result)
    {
        if ($this->getResultSetPrototype() instanceof HydratingResultSet) {
            $resultSet = $this->getResultSetPrototype();
        }
        else {
            $resultSet = new HydratingResultSet($this->getHydrator(), $this->getEntity());
        }
        $resultSet->initialize($result);
        $resultSet->buffer();
        $resultSet->rewind();
        return $resultSet;
    }

    /**
     * Get the hydrator instance
     *
     * @return AggregateHydrator
     */
    public function getHydrator ()
    {
        return $this->hydrator ?  : new AggregateHydrator();
    }

    /**
     * Set the hydrator instance
     *
     * @param HydratorInterface $hydrator
     */
    public function setHydrator (HydratorInterface $hydrator)
    {
        $this->hydrator = $hydrator;
    }

    /**
     * Get the entity object model
     *
     * @return \ArrayObject
     */
    public function getEntity ()
    {
        return $this->entity ?  : new ArrayObject();
    }

    /**
     * Set the entity object model
     *
     * @param \ArrayObject $entity
     */
    public function setEntity ($entity)
    {
        $this->entity = $entity;
    }

    /**
     * Get the table gateway platform
     *
     * @return \Platform\Mysql
     */
    public function platform ()
    {
        return $this->getAdapter()
            ->getPlatform();
    }
}