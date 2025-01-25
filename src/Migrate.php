<?php

namespace RobinTheHood\Migration;

use RobinTheHood\Migration\Migration;
use RobinTheHood\Terminal\Terminal;

class Migrate
{
    private $migration;

    public function __construct($config)
    {
        $this->migration = new Migration($config);
    }

    public function action($argv)
    {
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
