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
        $connection = $this->getConnection();

        $connection->exec('
            CREATE TABLE `s_user`(
              `id` INT,
              `name` CHAR(20)
            );
        ');

        $userAlex = [
            'id' => 1,
            'name' => 'Alex',
        ];

        $connection->insert('s_user', $userAlex);

        $expectUserAlex = [
            'id' => '1',
            'name' => 'Alex',
        ];

        $result = $connection->fetchAssoc('
            SELECT * FROM `s_user`;
        ');

        $this->assertSame($result, $expectUserAlex);

        $result = $connection->fetchAll('
            SELECT * FROM `s_user`;
        ');

        $this->assertSame($result, [$expectUserAlex]);

        $result = $connection->fetchAll('
            SELECT * FROM `s_user` WHERE `id` = 1;
        ');

        $this->assertSame($result, [$expectUserAlex]);

        $result = $connection->fetchAll('
            SELECT * FROM `s_user` WHERE `id` = 2;
        ');

        $this->assertSame($result, []);

        $userAnna = [
            'id' => 1,
            'name' => 'Anna',
        ];

        $connection->insert('s_user', $userAnna);

        $expectUserAnna = [
            'id' => '1',
            'name' => 'Anna',
        ];

        $result = $connection->fetchAll('
            SELECT * FROM `s_user` WHERE `id` = 1;
        ');

        $this->assertSame($result, [$expectUserAlex, $expectUserAnna]);

        $result = $connection->fetchAll('
            SELECT * FROM `s_user`
            WHERE `id` = 1
            LIMIT 1;
        ');

        $this->assertSame($result, [$expectUserAlex]);

        $result = $connection->fetchAll('
            SELECT * FROM `s_user`
            WHERE `id` = 1
            LIMIT 1
            OFFSET 1;
        ');

        $this->assertSame($result, [$expectUserAnna]);

        $result = $connection->fetchAll("
            SELECT * FROM `s_user` WHERE `name` = 'Anna';
        ");

        $this->assertSame($result, [$expectUserAnna]);

        $this->clear(['s_user']);
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
        $list = [];

        foreach ($tables as $table) {
            $list[] = "DROP TABLE `$table`;";
        }

        $connection = $this->getConnection();
        $connection->exec(join('', $list));
    }
}
