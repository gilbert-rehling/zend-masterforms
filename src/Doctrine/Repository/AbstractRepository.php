<?php

namespace Masterforms\Doctrine\Repository;

//use Savve\Stdlib;
//use Savve\Stdlib\Exception;
//use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
//use Doctrine\Common\Persistence\ObjectRepository;
//use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\QueryBuilder;

abstract class AbstractRepository extends EntityRepository implements
    RepositoryInterface
{

    /**
     * Instance of the QueryBuilder
     *
     * @var QueryBuilder
     */
    protected $query;

    /**
     * Create a query using QueryBuilder
     *
     * @return QueryBuilder
     */
    public function getQuery ()
    {
        trigger_error(sprintf('Class %s must implement method %s', get_class($this), __METHOD__), E_USER_WARNING);
    }

    /**
     * Set the query builder instance
     *
     * @param QueryBuilder $query
     * @return AbstractRepository
     */
    public function setQuery (QueryBuilder $query)
    {
        $this->query = $query;
        return $this;
    }

    /**
     * Fetches the result as an ArrayCollection
     *
     * @param \Doctrine\ORM\Query $query
     * @param boolean $debug
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function fetchCollection ($query, $debug = false)
    {
        if ($query instanceof QueryBuilder) {
            $query = $query->getQuery();
        }
        if (is_string($query)) {
            $args = func_get_args();
            $parameters = isset($args[1]) && is_array($args[1]) ? $args[1] : [];
            $debug = isset($args[2]) && is_bool($args[2]) ? $args[2] : false;
            $entityManager = $this->getEntityManager();
            $query = $entityManager->createQuery($query)
                ->setParameters($parameters);
        }
        elseif (is_array($query)) {
            $query = implode(' ', $query);
            $args = func_get_args();
            $parameters = isset($args[1]) && is_array($args[1]) ? $args[1] : [];
            $debug = isset($args[2]) && is_bool($args[2]) ? $args[2] : false;
            $entityManager = $this->getEntityManager();
            $query = $entityManager->createQuery($query)
                ->setParameters($parameters);
        }
        $parameters = $query->getParameters();

        // when debugging
        $this->showQuery($query, $debug);

        // cache the query
        $this->cacheQuery($query, $parameters);

        // execute the query
        $results = $query->getResult();
        $collection = new ArrayCollection($results);

        return $collection;
    }

    /**
     * Fetches the result as an array
     *
     * @return array
     */
    public function fetchAll ($query, $debug = false)
    {
        if ($query instanceof QueryBuilder) {
            $query = $query->getQuery();
        }
        $parameters = $query->getParameters();

        // cache the query
        $this->cacheQuery($query, $parameters);

        // execute the query
        $results = $query->getResult();

        // when debugging
        if ($debug === true) {
            \Zend\Debug\Debug::dump(__METHOD__ . ' ' . __LINE__);
            \Zend\Debug\Debug::dump($query->getDQL());
            \Zend\Debug\Debug::dump($query->getSQL());
            \Zend\Debug\Debug::dump($query->getParameters());
        }

        return $results;
    }

    /**
     * Fetches the result as an array
     *
     * @return array
     */
    public function fetchArray ($query, $debug = false)
    {
        if ($query instanceof QueryBuilder) {
            $query = $query->getQuery();
        }
        if (is_string($query)) {
            $args = func_get_args();
            $parameters = isset($args[1]) && is_array($args[1]) ? $args[1] : [];
            $debug = isset($args[2]) && is_bool($args[2]) ? $args[2] : false;
            $entityManager = $this->getEntityManager();
            $query = $entityManager->createQuery($query)
                ->setParameters($parameters);
        }
        elseif (is_array($query)) {
            $query = implode(' ', $query);
            $args = func_get_args();
            $parameters = isset($args[1]) && is_array($args[1]) ? $args[1] : [];
            $debug = isset($args[2]) && is_bool($args[2]) ? $args[2] : false;
            $entityManager = $this->getEntityManager();
            $query = $entityManager->createQuery($query)
                ->setParameters($parameters);
        }
        $parameters = $query->getParameters();

        // when debugging
        $this->showQuery($query, $debug);

        // cache the query
        $this->cacheQuery($query, $parameters);

        // execute the query
        $results = $query->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

        return $results;
    }

    /**
     * Fetches the result as a Scalar
     *
     * @param \Doctrine\ORM\Query $query
     * @param boolean $debug
     * @return array
     */
    public function fetchScalar ($query, $debug = false)
    {
        if ($query instanceof QueryBuilder) {
            $query = $query->getQuery();
        }
        elseif (is_string($query)) {
            $args = func_get_args();
            $parameters = isset($args[1]) && is_array($args[1]) ? $args[1] : [];
            $debug = isset($args[2]) && is_bool($args[2]) ? $args[2] : false;
            $entityManager = $this->getEntityManager();
            $query = $entityManager->createQuery($query)
                ->setParameters($parameters);
        }
        elseif (is_array($query)) {
            $query = implode(' ', $query);
            $args = func_get_args();
            $parameters = isset($args[1]) && is_array($args[1]) ? $args[1] : [];
            $debug = isset($args[2]) && is_bool($args[2]) ? $args[2] : false;
            $entityManager = $this->getEntityManager();
            $query = $entityManager->createQuery($query)
                ->setParameters($parameters);
        }
        $parameters = $query->getParameters();

        // cache the query
        $this->cacheQuery($query, $parameters);

        // execute the query
        $results = $query->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_SCALAR);

        // when debugging
        $this->showQuery($query, $debug);

        return $results;
    }

    /**
     * Fetch a single entity from the repository
     *
     * @param \Doctrine\ORM\Query $query
     * @param boolean $debug
     * @return \Masterforms\Doctrine\Entity\AbstractEntity null
     */
    public function fetchOne ($query, $debug = false)
    {
        if ($query instanceof QueryBuilder) {
            $query = $query->getQuery();
        }
        if (is_string($query)) {
            $args = func_get_args();
            $parameters = isset($args[1]) && is_array($args[1]) ? $args[1] : [];
            $debug = isset($args[2]) && is_bool($args[2]) ? $args[2] : false;
            $entityManager = $this->getEntityManager();
            $query = $entityManager->createQuery($query)
                ->setParameters($parameters);
        }
        elseif (is_array($query)) {
            $query = implode(' ', $query);
            $args = func_get_args();
            $parameters = isset($args[1]) && is_array($args[1]) ? $args[1] : [];
            $debug = isset($args[2]) && is_bool($args[2]) ? $args[2] : false;
            $entityManager = $this->getEntityManager();
            $query = $entityManager->createQuery($query)
                ->setParameters($parameters);
        }
        $parameters = $query->getParameters();

        // when debugging
        $this->showQuery($query, $debug);

        // cache the query
        $this->cacheQuery($query, $parameters);

        /* @var $results \Doctrine\Common\Collections\ArrayCollection */
        $results = $query->getResult();
        if (count($results) >= 1) {
            $results = current($results);
        }

        return $results;
    }

    /**
     * Fetch ONE entity value from the repository
     *
     * @return \Masterforms\Doctrine\Entity\AbstractEntity null
     */
    public function fetchValue ($query, $debug = false)
    {
        if ($query instanceof QueryBuilder) {
            $query = $query->getQuery();
        }
        if (is_string($query)) {
            $args = func_get_args();
            $parameters = isset($args[1]) && is_array($args[1]) ? $args[1] : [];
            $debug = isset($args[2]) && is_bool($args[2]) ? $args[2] : false;
            $entityManager = $this->getEntityManager();
            $query = $entityManager->createQuery($query)
                ->setParameters($parameters);
        }
        elseif (is_array($query)) {
            $query = implode(' ', $query);
            $args = func_get_args();
            $parameters = isset($args[1]) && is_array($args[1]) ? $args[1] : [];
            $debug = isset($args[2]) && is_bool($args[2]) ? $args[2] : false;
            $entityManager = $this->getEntityManager();
            $query = $entityManager->createQuery($query)
                ->setParameters($parameters);
        }
        $parameters = $query->getParameters();

        // when debugging
        $this->showQuery($query, $debug);

        // cache the query
        $this->cacheQuery($query, $parameters);

        // fetch
        $results = $query->getOneOrNullResult();

        return $results;
    }

    /**
     * Cache doctrine query
     *
     * @param \Doctrine\ORM\Query $query
     * @return \Doctrine\ORM\Query
     */
    public function cacheQuery (Query $query, $parameters = [], $lifetime = 3600)
    {
        if (!$parameters) {
            $parameters = $query->getParameters();
        }
        $resultCacheId = md5(HOSTNAME . $query->getDQL() . serialize($parameters));

        // cache
        $query->useResultCache(true, $lifetime, $resultCacheId)
            ->useQueryCache(true)
            ->setCacheable(true)
            ->setCacheMode(\Doctrine\ORM\Cache::MODE_NORMAL)
            ->setLifetime($lifetime);

        return $query;
    }

    /**
     * Display the query
     *
     * @param Query $query
     * @return Query
     */
    public function showQuery (Query $query, $show = false, $trace = false)
    {
        if ($show === true) {
            \Zend\Debug\Debug::dump(__METHOD__ . ' ' . __LINE__);
            \Zend\Debug\Debug::dump($query->getDQL());
            \Zend\Debug\Debug::dump($query->getSQL());
            \Zend\Debug\Debug::dump($query->getParameters());

            if ($trace === true) {
                echo '<pre>' . Stdlib\Debug::stackTrace() . '</pre>';
            }
        }

        return $query;
    }

    /**
     * Creates a create query
     *
     * @param $entity
     * @return mixed
     * @throws \Exception
     */
    public function create ($entity)
    {
        try {
            $entityManager = $this->getEntityManager();
            $entityManager->persist($entity);
            $entityManager->flush($entity);
            $entityManager->clear();

            return $entity;
        }
        catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Creates an update query
     *
     * @param $entity
     * @return mixed
     * @throws \Exception
     */
    public function update ($entity)
    {
        try {
            $entityManager = $this->getEntityManager();
            $entityManager->persist($entity);
            $entityManager->flush($entity);
            $entityManager->clear();

            return $entity;
        }
        catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Throws an error for delete entity quries
     *
     * @param $entity
     */
    public function delete ($entity)
    {
        trigger_error(sprintf('Class %s must implement method %s', get_class($this), __METHOD__), E_USER_WARNING);
    }

    /**
     * Creates an activate query
     *
     * @param $entity
     * @return mixed
     * @throws \Exception
     */
    public function activate ($entity)
    {
        try {
            // set the status to active
            $entity->setStatus('active');

            // save
            $entityManager = $this->getEntityManager();
            $entityManager->persist($entity);
            $entityManager->flush($entity);
            $entityManager->clear();

            return $entity;
        }
        catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Creates a deactivate query
     *
     * @param $entity
     * @return mixed
     * @throws \Exception
     */
    public function deactivate ($entity)
    {
        try {
            // set the status to inactive
            $entity->setStatus('inactive');

            // save
            $entityManager = $this->getEntityManager();
            $entityManager->persist($entity);
            $entityManager->flush($entity);
            $entityManager->clear();

            return $entity;
        }
        catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Get the entity manager
     *
     * @return \Doctrine\ORM\EntityManager
     */
    public function entityManager ()
    {
        return $this->getEntityManager();
    }
}