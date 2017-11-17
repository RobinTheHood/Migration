<?php
namespace Migration;

use Migration\Migration;
use Terminal\Terminal;

class Migrate
{
    private $migration;

    public function __construct($dbConfig)
    {
        $this->migration = new Migration($dbConfig);
    }

    public function action($argv)
    {
        // $migrationTable = new MigrationTable();
        // $migrationTable->create();
        // $migrationTable->setStatus('test');
        // $migrationTable->getStatus();
        // die('test DONE');

        $command = $argv[1];
        if ($command == 'migrate' || $command == '-m') {
            $this->migration->up();

        } elseif ($command == 'rollback' || $command == '-r') {
            $this->migration->rollback();

        } elseif ($command == 'status' || $command == '-s') {
            $this->migration->printStatus();

        } elseif ($command === 'help' || $command == '-h') {
            Terminal::outln('Command: -h (help)');
            Terminal::outln('migrate or -m');
            Terminal::outln('rollback or -r');
            Terminal::outln('status or -s');

        } else {
            Terminal::outln('Command unknown. Try -h for help.');
        }
    }
}
