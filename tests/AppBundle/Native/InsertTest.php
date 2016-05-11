<?php

namespace Tests\AppBundle\Native;

class InsertTest extends ConnectionTestCase
{
    public function testIgnore()
    {
        $connection = $this->getConnection();

        try {
            $connection->exec('
                CREATE TABLE `users` (
                  `id` INT PRIMARY KEY,
                  `name` VARCHAR(255)
                );
            ');

        } finally {
            $this->clear(['users']);
        }
    }
}
