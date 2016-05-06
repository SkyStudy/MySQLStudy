<?php

namespace Tests\AppBundle\Native;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\DBAL\Connection;

abstract class ConnectionTestCase extends KernelTestCase
{
    /**
     * @var Connection
     */
    protected $connection;

    protected function setUp()
    {
        static::bootKernel();

        $container =  static::$kernel->getContainer();

        $this->connection = $container->get('doctrine')->getConnection();
    }

    /**
     * @return Connection
     */
    protected function getConnection()
    {
        return $this->connection;
    }
}
