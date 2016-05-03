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

    public function testRename()
    {
        $connection = $this->getConnection();

        $statement = $connection->executeQuery('
           SHOW TABLES;
        ');

        $this->assertEmpty($statement->rowCount());

        $connection = $this->getConnection();

        $connection->exec('
            CREATE TABLE `s_before`(
              `id` INT
            );
        ');

        $statement = $connection->executeQuery('
           SHOW TABLES;
        ');

        $this->assertSame(['s_before'], $statement->fetchAll(\PDO::FETCH_COLUMN));

        $connection->exec('
            RENAME TABLE `s_before` TO `s_after`;
        ');

        $statement = $connection->executeQuery('
           SHOW TABLES;
        ');

        $this->assertSame(['s_after'], $statement->fetchAll(\PDO::FETCH_COLUMN));

        $this->clear(['s_after']);
    }

    public function testOperation()
    {
        $connection = $this->getConnection();

        $statement = $connection->executeQuery('
           SELECT 1;
        ');

        $result = $statement->fetch(\PDO::FETCH_COLUMN);

        $this->assertSame($result, '1');

        $statement = $connection->executeQuery('
           SELECT 1 + 1;
        ');

        $result = $statement->fetch(\PDO::FETCH_COLUMN);

        $this->assertSame($result, '2');

        $statement = $connection->executeQuery('
           SELECT 10 - 3;
        ');

        $result = $statement->fetch(\PDO::FETCH_COLUMN);

        $this->assertSame($result, '7');

        $statement = $connection->executeQuery('
           SELECT 7 * 8;
        ');

        $result = $statement->fetch(\PDO::FETCH_COLUMN);

        $this->assertSame($result, '56');

        $statement = $connection->executeQuery('
           SELECT 10 / 2;
        ');

        $result = $statement->fetch(\PDO::FETCH_COLUMN);

        $this->assertSame($result, '5.0000');

        $statement = $connection->executeQuery('
           SELECT 10 / 0;
        ');

        $result = $statement->fetch(\PDO::FETCH_COLUMN);

        $this->assertSame($result, null);

        $statement = $connection->executeQuery('
           SELECT 5 DIV 2;
        ');

        $result = $statement->fetch(\PDO::FETCH_COLUMN);

        $this->assertSame($result, '2');

        $statement = $connection->executeQuery('
           SELECT 95 MOD 17 ;
        ');

        $result = $statement->fetch(\PDO::FETCH_COLUMN);

        $this->assertSame($result, '10');

        $statement = $connection->executeQuery('
           SELECT 5 % 2;
        ');

        $result = $statement->fetch(\PDO::FETCH_COLUMN);

        $this->assertSame($result, '1');

        $statement = $connection->executeQuery('
           SELECT (1 + 2 + 3) * 1 * 2 * 3;
        ');

        $result = $statement->fetch(\PDO::FETCH_COLUMN);

        $this->assertSame($result, '36');
    }

    public function testFunction()
    {
        $connection = $this->getConnection();

        $statement = $connection->executeQuery("
            SELECT 'function';
        ");

        $result = $statement->fetch(\PDO::FETCH_COLUMN);

        $this->assertSame($result, 'function');

        $statement = $connection->executeQuery("
            SELECT LENGTH('function');
        ");

        $result = $statement->fetch(\PDO::FETCH_COLUMN);

        $this->assertSame($result, '8');

        $statement = $connection->executeQuery("
            SELECT GREATEST(3, 5, 1);
        ");

        $result = $statement->fetch(\PDO::FETCH_COLUMN);

        $this->assertSame($result, '5');

        $statement = $connection->executeQuery("
            SELECT LEAST(3, 5, 1);
        ");

        $result = $statement->fetch(\PDO::FETCH_COLUMN);

        $this->assertSame($result, '1');
    }

    public function testLogicOperation()
    {
        $connection = $this->getConnection();

        $statement = $connection->executeQuery('
           SELECT 2 = 2;
        ');

        $result = $statement->fetch(\PDO::FETCH_COLUMN);

        $this->assertSame($result, '1');

        $statement = $connection->executeQuery('
           SELECT 2 > 2;
        ');

        $result = $statement->fetch(\PDO::FETCH_COLUMN);

        $this->assertSame($result, '0');

        $statement = $connection->executeQuery('
           SELECT 1 < 2 AND 2 > 1;
        ');

        $result = $statement->fetch(\PDO::FETCH_COLUMN);

        $this->assertSame($result, '1');
    }

    public function testOperationAction()
    {
        $connection = $this->getConnection();

        try {
            $connection->exec('
                CREATE TABLE `s_product`(
                  `code` INT,
                  `name` VARCHAR(255),
                  `price` INT,
                  `quantity` INT
                );
            ');

            $connection->insert('s_product', [
                'code' => 1,
                'name' => 'socks',
                'price' => 30,
                'quantity' => 100,
            ]);

            $statement = $connection->executeQuery('
               SELECT `price` * `quantity`
               FROM `s_product`;
            ');

            $result = $statement->fetch(\PDO::FETCH_COLUMN);

            $this->assertSame($result, '3000');

            $result = $connection->fetchAll('
                SELECT `name`, `price` * `quantity`
                FROM `s_product`;
            ');

            $this->assertSame($result, [
                [
                    'name' => 'socks',
                    '`price` * `quantity`' => '3000',
                ]
            ]);

            $result = $connection->fetchAll('
                SELECT `name`, `price` * `quantity` AS `total`
                FROM `s_product`;
            ');

            $this->assertSame($result, [
                [
                    'name' => 'socks',
                    'total' => '3000',
                ]
            ]);
        } finally {
            $this->clear(['s_product']);
        }
    }

    public function testInsert()
    {
        $connection = $this->getConnection();

        try {
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

            $result = $connection->fetchAll("
                SELECT COUNT(*) AS `count`
                FROM `s_user`;
            ");

            $this->assertSame($result, [[
                'count' => '3'
            ]]);

            $result = $connection->fetchAll("
                SELECT `name`
                FROM `s_user`;
            ");

            $this->assertSame($result, [
                ['name' => 'Alex'],
                ['name' => 'Anna'],
                ['name' => 'Anna']
            ]);

            $result = $connection->fetchAll("
                SELECT DISTINCT `name`
                FROM `s_user`;
            ");

            $this->assertSame($result, [
                ['name' => 'Alex'],
                ['name' => 'Anna'],
            ]);

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

            $result = $connection->fetchAll("
                SELECT `id`, COUNT(*) AS `count`
                FROM `s_user`
                GROUP BY `id`
                ORDER BY `count` ASC
                LIMIT 1;
            ");

            $this->assertSame($result, [
                [
                    'id' => '5',
                    'count' => '1'
                ],
            ]);

            $result = $connection->fetchAll("
                SELECT `id`, COUNT(*) AS `count`
                FROM `s_user`
                GROUP BY `id`
                HAVING `count` > 1;
            ");

            $this->assertSame($result, [
                [
                    'id' => '1',
                    'count' => '2'
                ],
            ]);

            $result = $connection->fetchAll("
                SELECT `name`, COUNT(*) AS `count`
                FROM `s_user`
                GROUP BY `name`
                HAVING `count` > 1;
            ");

            $this->assertSame($result, [
                [
                    'name' => 'Anna',
                    'count' => '2'
                ],
            ]);

            $connection->exec("
                INSERT INTO `s_user`(`id`, `name`)
                SELECT `id`, `name`
                FROM `s_user`
                WHERE `name` = 'Anna' AND `id` = 5
                LIMIT 1;
            ");

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
                    'count' => '2'
                ],
            ]);

            $result = $connection->fetchAll("
                SELECT `name`, COUNT(*) AS `count`
                FROM `s_user`
                GROUP BY `name`;
            ");

            $this->assertSame($result, [
                [
                    'name' => 'Alex',
                    'count' => '1'
                ],
                [
                    'name' => 'Anna',
                    'count' => '3'
                ],
            ]);

            $connection->exec("
                UPDATE `s_user`
                SET `id` = 3
                WHERE `id` = 1 AND `name` = 'Alex'
            ");

            $result = $connection->fetchAll("
                SELECT `id`, COUNT(*) AS `count`
                FROM `s_user`
                GROUP BY `id`;
            ");

            $this->assertSame($result, [
                [
                    'id' => '1',
                    'count' => '1'
                ],
                [
                    'id' => '3',
                    'count' => '1'
                ],
                [
                    'id' => '5',
                    'count' => '2'
                ],
            ]);

            $connection->exec("
                INSERT INTO `s_user` (`id`, `name`)
                VALUES (15, 'Mister');
            ");

            $result = $connection->fetchAll('
                SELECT * FROM `s_user` WHERE `id` = 15;
            ');

            $this->assertSame($result, [[
                'id' => '15',
                'name' => 'Mister',
            ]]);

            $result = $connection->fetchAll('
                SELECT * FROM `s_user` WHERE `id` IN (1, 15);
            ');

            $this->assertSame($result, [
                [
                    'id' => '1',
                    'name' => 'Anna',
                ],
                [
                    'id' => '15',
                    'name' => 'Mister',
                ],
            ]);

            $connection->exec('
                DELETE FROM `s_user`
                WHERE `id` > 5
            ');

            $result = $connection->fetchAll('
                SELECT * FROM `s_user` WHERE `id` = 15;
            ');

            $this->assertSame($result, []);
        } finally {
            $this->clear(['s_user']);
        }
    }

    public function testJoin()
    {
        $connection = $this->getConnection();

        try {
            $connection->exec('
                CREATE TABLE `s_product`(
                  `code` INT,
                  `name` VARCHAR(255)
                );
            ');

            $connection->exec('
                CREATE TABLE `s_warehouse`(
                  `product_code` INT,
                  `price` INT,
                  `quantity` INT
                );
            ');

            $connection->exec("
                INSERT INTO `s_product`(`code`, `name`)
                VALUES
                  (1, 'Green Shirt'),
                  (2, 'White Shirt'),
                  (3, 'Red Shirt');
            ");

            $connection->exec("
                INSERT INTO `s_warehouse`(`product_code`, `price`, `quantity`)
                VALUES
                  (1, 120, 8),
                  (2, 125, 5),
                  (3, 200, 0);
            ");

            $result = $connection->fetchAll('
                SELECT * FROM `s_product`
                JOIN `s_warehouse` ON (`product_code` = `code`)
            ');

            $expect = [
                [
                    'code' => '1',
                    'name' => 'Green Shirt',
                    'product_code' => '1',
                    'price' => '120',
                    'quantity' => '8',
                ],
                [
                    'code' => '2',
                    'name' => 'White Shirt',
                    'product_code' => '2',
                    'price' => '125',
                    'quantity' => '5',
                ],
                [
                    'code' => '3',
                    'name' => 'Red Shirt',
                    'product_code' => '3',
                    'price' => '200',
                    'quantity' => '0',
                ]
            ];

            $this->assertSame($result, $expect);

            $result = $connection->fetchAll('
                SELECT * FROM `s_product`
                JOIN `s_warehouse` ON (`code` = `product_code`)
            ');

            $this->assertSame($result, $expect);

            $result = $connection->fetchAll('
                SELECT `name`, `quantity` FROM `s_product`
                JOIN `s_warehouse` ON (`code` = `product_code`)
            ');

            $expect = [
                [
                    'name' => 'Green Shirt',
                    'quantity' => '8',
                ],
                [
                    'name' => 'White Shirt',
                    'quantity' => '5',
                ],
                [
                    'name' => 'Red Shirt',
                    'quantity' => '0',
                ]
            ];

            $this->assertSame($result, $expect);

            $result = $connection->fetchAll('
                SELECT `s_product`.`name`, `s_warehouse`.`quantity`
                FROM `s_product`
                JOIN `s_warehouse` ON (`s_product`.`code` = `s_warehouse`.`product_code`)
            ');

            $this->assertSame($result, $expect);

            $result = $connection->fetchAll('
                SELECT `p`.`name`, `w`.`quantity`
                FROM `s_product` `p`
                JOIN `s_warehouse` `w` ON (`p`.`code` = `w`.`product_code`)
            ');

            $this->assertSame($result, $expect);

            $result = $connection->fetchAll('
                SELECT `p`.`name`, `w`.`quantity`
                FROM `s_product` AS `p`
                JOIN `s_warehouse` AS `w` ON (`p`.`code` = `w`.`product_code`)
            ');

            $this->assertSame($result, $expect);

            $result = $connection->fetchAll('
                SELECT `p`.`name` AS `n`, `w`.`quantity` AS `q`
                FROM `s_product` AS `p`
                JOIN `s_warehouse` AS `w` ON (`p`.`code` = `w`.`product_code`)
            ');

            $expect = [
                [
                    'n' => 'Green Shirt',
                    'q' => '8',
                ],
                [
                    'n' => 'White Shirt',
                    'q' => '5',
                ],
                [
                    'n' => 'Red Shirt',
                    'q' => '0',
                ]
            ];

            $this->assertSame($result, $expect);
        } finally {
            $this->clear(['s_product', 's_warehouse']);
        }
    }

    public function testRow()
    {
        $connection = $this->getConnection();

        $connection->exec('
            CREATE TABLE `users`(
              `id` INT,
              `name` CHAR(20)
            );
        ');

        $connection->exec("
            INSERT INTO `users`(`id`, `name`)
            VALUES
              (1, 'Alex'),
              (17, 'Reen');
        ");

        $result = $connection->fetchAll("
            SELECT * FROM `users` WHERE (`id`, `name`) IN (ROW(1, 'Alex'), ROW(17, 'Reen'));
        ");

        $this->assertSame($result, [
            [
                'id' => '1',
                'name' => 'Alex',
            ],
            [
                'id' => '17',
                'name' => 'Reen',
            ],
        ]);

        $this->clear(['users']);
    }

    public function testStudents()
    {
        /**
         * TODO:
         * Використовуючи з'єднання - знайти всіх студентів які вчились тільки в одному університеті
         * Using connections - to find all students who studied in only one university
         * Маємо три таблиці - студенти, університети, і з’єднувальна таблиця - "багато до багато"
         * We have three tables - students, universities and fittings table - "many to many"
         */

        $connection = $this->getConnection();

        try {
            $connection->exec('
                CREATE TABLE `s_student`(
                  `code` INT,
                  `name` VARCHAR(255)
                );

                CREATE TABLE `s_university`(
                  `code` INT,
                  `name` VARCHAR(255)
                );

                CREATE TABLE `s_student_to_university`(
                  `student_code` INT,
                  `university_code` INT
                );
            ');
        } finally {
            $this->clear(['s_student', 's_university', 's_student_to_university']);
        }
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
