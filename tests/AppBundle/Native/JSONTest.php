<?php

namespace Tests\AppBundle\Native;

class JSONTest extends ConnectionTestCase
{
    /**
     * @dataProvider dataProvider
     * @param $sql
     * @param $expected
     */
    public function testO($sql, $expected)
    {
        $connection = $this->getConnection();

        $this->assertSame($expected, $connection->fetchColumn($sql));
    }

    public function dataProvider()
    {
        $sql = <<<'SQL'
            SELECT JSON_OBJECT('id', 1);
SQL;

        yield [
            $sql,
            '{"id": 1}'
        ];

        $sql = <<<'SQL'
            SELECT JSON_OBJECT('id', 1, 'name', 'Reen');
SQL;

        yield [
            $sql,
            '{"id": 1, "name": "Reen"}'
        ];

        $sql = <<<'SQL'
            SELECT JSON_ARRAY('id', 1, 'name', 'Reen');
SQL;

        yield [
            $sql,
            '["id", 1, "name", "Reen"]'
        ];
    }
}
