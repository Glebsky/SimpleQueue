<?php

namespace Glebsky\SimpleQueueTest;

use Glebsky\SimpleQueue\Transports\PDOTransport;
use PDO;

class MigrationTest extends \PHPUnit\Framework\TestCase
{
    private static $pdo;
    private static $transport;
    private static $testTable;

    public static function setUpBeforeClass()
    {
        self::$pdo = new PDO("mysql:host=localhost:3306;dbname=simple_queue", 'root', '');
        self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        self::$testTable = 'testJobTable';
        self::$transport = new PDOTransport('localhost:3306', 'simple_queue', 'root', '', self::$testTable);
    }

    public static function tearDownAfterClass()
    {
        $testTable = self::$testTable;
        self::$pdo->exec("DROP TABLE simple_queue.$testTable");
    }

    public function testMigrationsCreateTable()
    {
        $testTable = self::$testTable;
        $result = self::$pdo->query("SHOW TABLES LIKE '$testTable'");
        if ($result->rowCount() > 0) {
            self::$pdo->exec("DROP TABLE simple_queue.$testTable");
        }
        $result    = self::$transport->migrate();
        self::assertTrue($result);

        $result = self::$pdo->query("SHOW TABLES LIKE '$testTable'");
        if ($result->rowCount() == 0) {
            self::fail();
        }
    }

    public function testExistTable()
    {
        $testTable = self::$testTable;
        $result = self::$pdo->query("SHOW TABLES LIKE '$testTable'");
        if ($result->rowCount() == 0) {
            self::fail();
        }

        $result    = self::$transport->migrate();
        self::assertTrue($result);

        $result = self::$pdo->query("SHOW TABLES LIKE '$testTable'");
        if ($result->rowCount() == 0) {
            self::fail();
        }
    }

    public function testNonExistingField()
    {
        $testTable = self::$testTable;
        self::$pdo->exec("alter table simple_queue.$testTable drop column error");

        $this->expectException(\RuntimeException::class);
        self::$transport->migrate();
    }
}