<?php

namespace Tests\AppBundle\Native;

use Doctrine\DBAL\Connection;

class Test extends AbstractTest
{
    public function testEmptyDatabase()
    {
        $connection = $this->getConnection();

        $statement = $connection->executeQuery('
           SHOW TABLES;
        ');

        $this->assertEmpty($statement->rowCount());
    }

    public function testCreateTable()
    {
        $connection = $this->getConnection();

        $connection->exec('
            CREATE TABLE `s_first`(
              `id` INT
            );
        ');

        $statement = $connection->executeQuery('
           SHOW TABLES;
        ');

        $this->assertSame('s_first', $statement->fetch(\PDO::FETCH_COLUMN));
        $this->assertSame(1, $statement->rowCount());

        $connection->exec('
            CREATE TABLE `s_second`(
              `id` INT
            );
        ');

        $statement = $connection->executeQuery('
           SHOW TABLES;
        ');

        $this->assertSame(['s_first', 's_second'], $statement->fetchAll(\PDO::FETCH_COLUMN));
        $this->assertSame(2, $statement->rowCount());

        $this->clear(['s_first', 's_second']);
    }

    public function testInsert()
    {

    }

    /**
     * @return Connection
     */
    private function getConnection()
    {
        return $this->container->get('doctrine')->getConnection();
    }

    private function clear(array $tables)
    {
        $connection = $this->getConnection();

        foreach ($tables as $table) {
            $connection->exec("
                DROP TABLE `$table`;
            ");
        }

    }
}
