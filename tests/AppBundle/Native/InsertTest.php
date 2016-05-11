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
                  `id` INT PRIMARY KEY AUTO_INCREMENT,
                  `name` VARCHAR(255),
                  UNIQUE KEY (`name`)
                );
            ');

            $connection->executeUpdate("
                INSERT INTO `users` (`name`)
                VALUE ('Alex');
            ");

            $this->assertSame('1', $connection->lastInsertId());

            $connection->executeUpdate("
                INSERT INTO `users` (`name`)
                VALUE ('Reen');
            ");

            $this->assertSame('2', $connection->lastInsertId());

        } finally {
            $this->clear(['users']);
        }
    }
}
