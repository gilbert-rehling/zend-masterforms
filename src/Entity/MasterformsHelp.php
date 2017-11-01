<?php

namespace Masterforms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="masterforms_help")
 */
class MasterformsHelp
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")
     */
    protected $id;

    /**
     * @ORM\Column(name="help_title")
     */
    protected $helpTitle;

    /**
     * @ORM\Column(name="help_category")
     */
    protected $helpCategory;

    /**
     * @ORM\Column(name="helpDescription")
     */
    protected $helpDescription;

    /**
     * @ORM\Column(name="helpData")
     */
    protected $helpData;

    /**
     * @ORM\Column(name="helpStatus")
     */
    protected $helpStatus;

    /**
     * @ORM\Column(name="created_at")
     */
    protected $createdAt;

    /**
     * @ORM\Column(name="updated_at")
     */
    protected $updatedAt;
}