<?php

namespace Tests\AppBundle\Native;

class StringTest extends SingleResultTestCase
{
    public function dataProvider()
    {
        yield [
            "SELECT 'text'",
            'text'
        ];

        yield [
            "SELECT CONCAT('text')",
            'text'
        ];

        yield [
            "SELECT CONCAT('Green', ' ', 'Forest')",
            'Green Forest'
        ];
    }
}
