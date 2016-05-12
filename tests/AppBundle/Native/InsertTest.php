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

            $response = $connection->executeUpdate("
                INSERT INTO `users` (`name`)
                VALUE ('Alex');
            ");
            $this->assertSame(1, $response);

            $this->assertSame('1', $connection->lastInsertId());

            $response = $connection->executeUpdate("
                INSERT INTO `users` (`name`)
                VALUE ('Reen');
            ");
            $this->assertSame(1, $response);

            $this->assertSame('2', $connection->lastInsertId());

            $response = $connection->executeUpdate("
                INSERT IGNORE INTO `users` (`name`)
                VALUE ('Sky');
            ");
            $this->assertSame(1, $response);

            $this->assertSame('3', $connection->lastInsertId());

            $response = $connection->executeUpdate("
                INSERT IGNORE INTO `users` (`name`)
                VALUE ('Alex');
            ");

            $this->assertSame(0, $response);
            $this->assertSame('0', $connection->lastInsertId());

        } finally {
            $this->clear(['users']);
        }
    }
}
