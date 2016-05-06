<?php

namespace Tests\AppBundle\Native;

class JSONTest extends ConnectionTestCase
{
    public function testObject()
    {
        $connection = $this->getConnection();

        $result = $connection->fetchColumn(<<<'SQL'
          SELECT JSON_OBJECT('id', 1);
SQL
        );

        $this->assertSame('{"id": 1}', $result);
    }
}
