<?php

namespace Tests\AppBundle\Native;

use Doctrine\DBAL\Connection;

class Test extends AbstractTest
{
    public function test()
    {
        $this->getConnection()->exec('
            DROP TABLE IF EXISTS s_first ;
        ');
    }

    /**
     * @return Connection
     */
    private function getConnection()
    {
        return $this->container->get('doctrine')->getConnection();
    }
}
