<?php
namespace Masterforms\Doctrine\ORM;

use Masterforms\Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;

interface EntityManagerAwareInterface
{

    /**
     * Get the Doctrine EntityManager
     *
     * @return EntityManager $entityManager
     */
    public function getEntityManager ();

    /**
     * Set the Doctrine EntityManager
     *
     * @param EntityManager $entityManager
     */
    public function setEntityManager (EntityManagerInterface $entityManager);
}