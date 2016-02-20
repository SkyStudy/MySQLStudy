<?php

namespace Tests\AppBundle\Native;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class AbstractTest extends KernelTestCase
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    protected function setUp()
    {
        static::bootKernel();

        $this->container =  static::$kernel->getContainer();
    }
}
