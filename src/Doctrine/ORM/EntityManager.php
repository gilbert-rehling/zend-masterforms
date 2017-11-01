<?php
namespace Masterforms\Doctrine\ORM;

use Masterforms\Doctrine\ORM\QueryBuilder;
use Masterforms\Doctrine\Repository\EntityRepository;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager as DoctrineORMEntityManager;
use Doctrine\ORM\Decorator\EntityManagerDecorator;

/**
 * @method \Masterforms\Doctrine\Repository\EntityRepository getRepository($entityName)
 */
class EntityManager extends DoctrineORMEntityManager
{

    /**
     * Factory method to create EntityManager instances.
     *
     * @param mixed $conn An array with the connection parameters or an existing Connection instance.
     * @param Configuration $config The Configuration instance to use.
     * @param EventManager $eventManager The EventManager instance to use.
     *
     * @return EntityManager The created EntityManager.
     *
     * @throws \InvalidArgumentException
     * @throws ORMException
     */
    public static function create ($conn, Configuration $config, EventManager $eventManager = null)
    {
        if (!$config->getMetadataDriverImpl()) {
            throw ORMException::missingMappingDriverImpl();
        }

        switch (true) {
            case (is_array($conn)):
                $conn = \Doctrine\DBAL\DriverManager::getConnection($conn, $config, ($eventManager ?  : new EventManager()));
                break;

            case ($conn instanceof Connection):
                if ($eventManager !== null && $conn->getEventManager() !== $eventManager) {
                    throw ORMException::mismatchedEventManager();
                }
                break;

            default:
                throw new \InvalidArgumentException("Invalid argument: " . $conn);
        }

        return new EntityManager($conn, $config, $conn->getEventManager());
    }

    /**
     * Overrides the DoctrineORM entity manager createQuery($dql)
     *
     * @see \Doctrine\ORM\EntityManager::createQuery()
     */
    public function createQuery ($dql = '')
    {
        $query = parent::createQuery($dql);

        $lifetime = 3600;
    //    $parameters = $query->getParameters();
    //    $resultCacheId = md5(HOSTNAME . $query->getDQL() . serialize($parameters));
        $query->useResultCache(true, $lifetime)
            ->useQueryCache(true)
            ->setCacheable(true)
            ->setCacheMode(\Doctrine\ORM\Cache::MODE_NORMAL)
            ->setLifetime($lifetime);

        return $query;
    }

    /**
     * Create a QueryBuilder instance
     *
     * @return QueryBuilder
     */
    public function createQueryBuilder ()
    {
        return new QueryBuilder($this);
    }
}