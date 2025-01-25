<?php

namespace RobinTheHood\Migration;

use RobinTheHood\Database\Database;
use RobinTheHood\Terminal\Terminal;
use RobinTheHood\Debug\Debug;

class ActiveRecord
{
    public $scriptTimeStart;
    public $scriptTimeEnd;

    public function createTable($tableName, $attributes)
    {
        Terminal::outln('-- create_table(:' . $tableName . ')');
        $sql = $this->getSqlCreateTable($tableName, $attributes);
        $this->execute($sql);
    }

    public function dropTable($tableName)
    {
        Terminal::outln('-- drop_table(:' . $tableName . ')');
        $sql = $this->getSqlDropTable($tableName);
        $this->execute($sql);
    }

    public function renameTable($tableNameOld, $tableNameNew)
    {
        Terminal::outln('-- rename_table(:' . $tableNameOld . ', :' . $tableNameNew . ')');
        $sql = $this->getSqlRenameTable($tableNameOld, $tableNameNew);
        $this->execute($sql);
    }

    public function addColumn($tableName, $columnName, $type)
    {
        Terminal::outln('-- add_column(:' . $tableName . ', :' . $columnName . ', :' . $type . ')');
        $sql = $this->getSqlAddColumn($tableName, $columnName, $type);
        $this->execute($sql);
    }

    public function removeColumn($tableName, $columnName)
    {
        Terminal::outln('-- revome_column(:' . $tableName . ', :' . $columnName . ')');
        $sql = $this->getSqlRemoveColumn($tableName, $columnName);
        $this->execute($sql);
    }

    public function renameColumn($tableName, $columnNameOld, $columnNameNew, $type)
    {
        Terminal::outln('-- rename_column(:' . $tableName . ', :' . $columnNameOld . ', :' . $columnNameNew . ')');
        $sql = $this->getSqlRenameColumn($tableName, $columnNameOld, $columnNameNew, $type);
        $this->execute($sql);
    }


    private function getSqlCreateTable($tableName, $attributes)
    {
        // Create Columns
        $columns = '';
        foreach ($attributes as $values) {
            $columnName = $values[0];
            $columnType = $values[1];
            $columnPrimary = $values[2];

            $column = '`' . $columnName . '`' . ' ' . $columnType;

            if ($columnPrimary) {
                $column .= ', PRIMARY KEY (' . $columnName . ')';
            }

            // Add , to the string as long as it is not the last interation.
            if (end($attributes) != $values) {
                $column .= ', ';
            }

            $columns .= $column;
        }

        //Create Table
        $sql = 'CREATE TABLE `' . $tableName . '` (' . $columns . ');';

        return $sql;
    }

    private function getSqlDropTable($tableName)
    {
        $sql = 'DROP table `' . $tableName . '`;';
        return $sql;
    }

    private function getSqlRenameTable($tableNameOld, $tableNameNew)
    {
        $sql = 'ALTER TABLE `' . $tableNameOld . '` RENAME TO `' . $tableNameNew . '`;';
        return $sql;
    }

    private function getSqlAddColumn($tableName, $columnName, $type)
    {
        $sql = 'ALTER TABLE `' . $tableName . '` ADD `' . $columnName . '` ' . $type . ';';
        return $sql;
    }

    private function getSqlRemoveColumn($tableName, $columnName)
    {
        $sql = 'ALTER TABLE `' . $tableName . '` DROP `' . $columnName . '`;';
        return $sql;
    }

    private function getSqlRenameColumn($tableName, $columnNameOld, $columnNameNew, $type)
    {
        $sql = 'ALTER TABLE `'
            . $tableName . '` CHANGE `' . $columnNameOld . '` `' . $columnNameNew . '` ' . $type . ';';
        return $sql;
    }

    private function execute($sql)
    {
        $this->startTimer();

        Terminal::outln($sql, Terminal::DARK_GRAY);
        $pdo = Database::getConnection();
        $result = $pdo->exec($sql);

        $this->printTimer();

        if ($result === false) {
            Debug::error('SQL-ERROR: ' . $pdo->errorInfo()[2]);
            die();
        }
    }


    /*
     ****************
     *    HELPER    *
     ****************
     */

    private function startTimer()
    {
        $this->scriptTimeStart = microtime(true);
    }

    private function stopTimer()
    {
        $this->scriptTimeEnd = microtime(true);
        return microtime(true) - $this->scriptTimeStart;
    }

    private function printTimer()
    {
        Terminal::outln('   -> ' . round($this->stopTimer(), 3) . ' ns', Terminal::YELLOW);
    }
}
