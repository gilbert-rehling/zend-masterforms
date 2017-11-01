<?php
namespace Masterforms\Service;

use Masterforms\StdLib\Exception;
use Masterforms\Entity;
use Zend\Session\Container as SessionContainer;
//use Masterforms\Stdlib;
// use Masterforms\Stdlib\Exception;
use Masterforms\Doctrine\Service\AbstractService;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\Collections\ArrayCollection;
use Zend\Session\Config\ConfigInterface;


class MasterformsService extends AbstractService
{

    /**
     * Learner options
     *
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var SessionContainer
     */
    protected $masterformsStorage;

    /**
     *  Constructor
     *
     * MasterformsService constructor.
     * @param \Doctrine\ORM\EntityManager $entityManager
     * @param ConfigInterface $config
     * @param $masterformsStorage
     */
    public function __construct (\Doctrine\ORM\EntityManager $entityManager, $config, $masterformsStorage)
    {
        $this->config = $config;
        $this->masterformsStorage = $masterformsStorage;

        parent::__construct($entityManager);
    }

    /**
     * Fetches the Masterforms configuration from this service
     *
     * @return bool|ConfigInterface
     */
    public function getConfig()
    {
        if (count($this->config)) {
            return $this->config;
        }
        return false;
    }

    /**
     * This method will try to get the Welcome content from Masterforms Help datatable
     *
     * @return \Masterforms\Doctrine\Entity\AbstractEntity|string
     * @throws \Exception
     */
    public function isSetupTest()
    {
        try {
            // load help repository
            $repository = $this->helpRepository();

            // execute query and return result
            return $repository->find(1);

        }
        catch (\Exception $e) {
            if (strpos($e->getMessage(), 'Access denied for user') !== false) {

                //   throw new Exception\InvalidQueryException('Database user has no access or no database exists');
                return 'Database user has no access or no database exists';

            } elseif (strpos($e->getMessage(), 'Base table or view not found') !== false) {

                throw new Exception\InvalidQueryException('Masterforms database has not been setup');

            } else {
                die(" you are here!!");
                throw $e;
            }
        }
    }

    /**
     * Get MasterformsHelp doctrine entity repository
     *
     * @return \Masterforms\Doctrine\Repository\AbstractRepository
     */
    public function helpRepository ()
    {
        $entityManager = $this->getEntityManager();
        return $entityManager->getRepository(Entity\MasterformsHelp::class);
    }
}