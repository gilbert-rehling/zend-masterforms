<?php
namespace Masterforms\Doctrine\ORM;

use Masterforms\Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface as EntityManagerInterface;

trait EntityManagerAwareTrait
{

    /**
     * Doctrine EntityManager
     *
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * Get the Doctrine EntityManager
     *
     * @return EntityManager $entityManager
     */
    public function getEntityManager ()
    {
        return $this->entityManager;
    }

    /**
     * Set the Doctrine EntityManager
     *
     * @param EntityManagerInterface $entityManager
     * @return $this
     */
    public function setEntityManager (EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        return $this;
    }
}