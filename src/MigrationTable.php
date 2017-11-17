<?php
namespace Migration;

use Terminal\Terminal;
use Debug\Debug;
use NamingConvention\NamingConvention;
use Database\Database;

class MigrationTable
{
    private $tableName = 'migration_status';

    public function __construct($tableName = '')
    {
        if ($tableName) {
            $this->tableName = $tableName;
        }
    }

    public function create()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `{$this->tableName}`
                    (
                        `id` int(11) unsigned NOT NULL auto_increment,
                        `namespace` VARCHAR(255) NOT NULL,
                        `status` VARCHAR(255) NOT NULL,
                        `executed` DATETIME NOT NULL,
                        PRIMARY KEY  (`id`)
                    )";

        $this->execute($sql);
    }

    public function getStatus($namespace = 'global')
    {
        $sql = "SELECT * FROM `{$this->tableName}` WHERE namespace = '$namespace'";

        $result = $this->query($sql);
        return $result[0];
    }

    public function setStatus($status, $namespace = 'global')
    {
        $lastStatusArray = $this->getStatus($namespace);

        if ($lastStatusArray) {
            $this->updateStatus($status, $namespace);
        } else {
            $this->addStatus($status, $namespace);
        }
    }

    private function addStatus($status, $namespace)
    {
        $sql = "INSERT INTO `{$this->tableName}` (`namespace`, `status`, `executed`)
                VALUES ('$namespace', '$status', NOW())";
        $result = $this->execute($sql);
    }

    private function updateStatus($status, $namespace)
    {
        $sql = "UPDATE `{$this->tableName}`
                SET `status` = '$status', `executed` = NOW()
                WHERE `namespace` = '$namespace'";
        $result = $this->execute($sql);
    }

    private function execute($sql, $verbose = false)
    {
        if ($verbose === true) {
            Terminal::outln($sql, Terminal::DARK_GRAY);
        }

        $pdo = Database::getConnection();
        $result = $pdo->exec($sql);

        if ($result === false) {
            Debug::error('SQL-ERROR: ' . $pdo->errorInfo()[2]);
            die();
        }
    }

    private function query($sql, $verbose = false)
    {
        if ($verbose === true) {
            Terminal::outln($sql, Terminal::DARK_GRAY);
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute();

        if($stmt->errorCode() == 0) {
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } else {
            Debug::error('SQL-ERROR: ' . $stmt->errorInfo()[2]);
            die();
        }

        return $result;
    }
}
