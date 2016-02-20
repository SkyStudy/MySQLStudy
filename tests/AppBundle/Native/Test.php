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

        $user = [
            'id' => 1,
            'name' => 'Alex',
        ];

        $connection->insert('s_user', $user);

        $expectUser = [
            'id' => '1',
            'name' => 'Alex',
        ];
        $expectUserList = [$expectUser];

        $users = $connection->fetchAssoc('
            SELECT * FROM `s_user`;
        ');

        $this->assertSame($users, $expectUser);

        $users = $connection->fetchAll('
            SELECT * FROM `s_user`;
        ');

        $this->assertSame($users, $expectUserList);

        $users = $connection->fetchAll('
            SELECT * FROM `s_user` WHERE `id` = 1;
        ');

        $this->assertSame($users, $expectUserList);

        $users = $connection->fetchAll('
            SELECT * FROM `s_user` WHERE `id` = 2;
        ');

        $this->assertSame($users, []);

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
