<?php

namespace Masterforms\BlockManager;

use Masterforms\BlockManager\AbstractBlock;

class Block extends AbstractBlock
{

    protected $container;

    public function __constructor($container)
    {
        $this->container = $container;
    }
}