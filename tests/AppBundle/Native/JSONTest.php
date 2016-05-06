<?php

namespace Tests\AppBundle\Native;

class JSONTest extends ConnectionTestCase
{
    /**
     * @dataProvider objectDataProvider
     * @param $sql
     * @param $expected
     */
    public function testObject($sql, $expected)
    {
        $connection = $this->getConnection();

        $this->assertSame($expected, $connection->fetchColumn($sql));
    }

    public function objectDataProvider()
    {
        $sql = <<<'SQL'
          SELECT JSON_OBJECT('id', 1);
SQL;

        yield [
            $sql,
            '{"id": 1}'
        ];
    }
}
