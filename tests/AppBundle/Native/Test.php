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

        $connection->insert('s_user', [
            'id' => 1,
            'name' => 'Alex',
        ]);

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

        $connection->insert('s_user', [
            'id' => 1,
            'name' => 'Anna',
        ]);

        $expectUserAnna = [
            'id' => '1',
            'name' => 'Anna',
        ];

        $result = $connection->fetchAll('
            SELECT * FROM `s_user`;
        ');

        $this->assertSame($result, [$expectUserAlex, $expectUserAnna]);

        $result = $connection->fetchAll('
            SELECT * FROM `s_user`
            ORDER BY `name`;
        ');

        $this->assertSame($result, [$expectUserAlex, $expectUserAnna]);

        $result = $connection->fetchAll('
            SELECT * FROM `s_user`
            ORDER BY `name` ASC;
        ');

        $this->assertSame($result, [$expectUserAlex, $expectUserAnna]);

        $result = $connection->fetchAll('
            SELECT * FROM `s_user`
            ORDER BY `name` DESC;
        ');

        $this->assertSame($result, [$expectUserAnna, $expectUserAlex]);

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

        $result = $connection->fetchAll("
            SELECT COUNT(*)
            FROM `s_user`;
        ");

        $this->assertSame($result, [[
            'COUNT(*)' => '2'
        ]]);

        $result = $connection->fetchAll("
            SELECT COUNT(*) AS `count`
            FROM `s_user`;
        ");

        $this->assertSame($result, [[
            'count' => '2'
        ]]);

        $result = $connection->fetchAll("
            SELECT COUNT(*) AS `count`
            FROM `s_user`
            GROUP BY `id`;
        ");

        $this->assertSame($result, [[
            'count' => '2'
        ]]);

        $result = $connection->fetchAll("
            SELECT `id`, COUNT(*) AS `count`
            FROM `s_user`
            GROUP BY `id`;
        ");

        $this->assertSame($result, [[
            'id' => '1',
            'count' => '2'
        ]]);

        $connection->insert('s_user', [
            'id' => 5,
            'name' => 'Anna',
        ]);

        $expectUserAnnaClone = [
            'id' => '5',
            'name' => 'Anna',
        ];

        $result = $connection->fetchAll("
            SELECT COUNT(*) AS `count`
            FROM `s_user`;
        ");

        $this->assertSame($result, [[
            'count' => '3'
        ]]);

        $result = $connection->fetchAll("
            SELECT `id`, COUNT(*) AS `count`
            FROM `s_user`
            GROUP BY `id`;
        ");

        $this->assertSame($result, [
            [
                'id' => '1',
                'count' => '2'
            ],
            [
                'id' => '5',
                'count' => '1'
            ],
        ]);

        $result = $connection->fetchAll("
            SELECT `id`, COUNT(*) AS `count`
            FROM `s_user`
            GROUP BY `id`
            ORDER BY `count` ASC;
        ");

        $this->assertSame($result, [
            [
                'id' => '5',
                'count' => '1'
            ],
            [
                'id' => '1',
                'count' => '2'
            ],
        ]);

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
