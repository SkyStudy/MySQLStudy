<?php

namespace Tests\AppBundle\Native;

abstract class SingleResultTestCase extends ConnectionTestCase
{
    /**
     * @dataProvider dataProvider
     * @param $sql
     * @param $expected
     */
    public function test($sql, $expected)
    {
        $connection = $this->getConnection();

        $this->assertSame($expected, $connection->fetchColumn($sql));
    }

    abstract public function dataProvider();
}
