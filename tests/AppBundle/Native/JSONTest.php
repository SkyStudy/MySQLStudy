<?php

namespace Tests\AppBundle\Native;

class JSONTest extends SingleResultTestCase
{
    public function dataProvider()
    {
        $sql = "SELECT JSON_OBJECT('id', 1)";

        yield [
            $sql,
            '{"id": 1}'
        ];

        $sql = "SELECT JSON_OBJECT('id', 1, 'name', 'Reen')";

        yield [
            $sql,
            '{"id": 1, "name": "Reen"}'
        ];

        $sql = "SELECT JSON_ARRAY('id', 1, 'name', 'Reen')";

        yield [
            $sql,
            '["id", 1, "name", "Reen"]'
        ];

        foreach ($this->lengthDataProvider() as list($json, $length)) {
            yield ["SELECT JSON_LENGTH('$json')", (string)$length];
        }

        $sql = <<<'SQL'
          SELECT JSON_VALID('"some text"');
SQL;

        yield [
            $sql,
            '1'
        ];

        $sql = "SELECT JSON_VALID('some text')";

        yield [
            $sql,
            '0'
        ];

        $sql = "SELECT JSON_VALID('{}')";

        yield [
            $sql,
            '1'
        ];

        $sql = "SELECT JSON_VALID('{}')";

        yield [
            $sql,
            '1'
        ];

        $sql = "SELECT JSON_VALID('{1}')";

        yield [
            $sql,
            '0'
        ];

        $sql = "SELECT JSON_VALID('')";

        yield [
            $sql,
            '0'
        ];

        foreach ($this->typeDataProvider() as list($json, $type)) {
            yield ["SELECT JSON_TYPE('$json')", $type];
        }
    }

    private function lengthDataProvider()
    {
        yield [
            '[]',
            0
        ];

        yield [
            '{}',
            0
        ];

        yield [
            '[1, 2, 3]',
            3
        ];

        yield [
            '{"id": 1}',
            1
        ];
    }

    private function typeDataProvider()
    {
        yield [
            '1',
            'INTEGER'
        ];

        yield [
            '"text"',
            'STRING'
        ];
    }
}
