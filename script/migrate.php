<?php
//  Set the current directory.
chdir(__DIR__);

// Errorhandling
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL ^E_NOTICE);

// Composer autoload
require_once '../vendor/autoload.php';

use RobinTheHood\Migration\Migrate;

// Configuration
$dbConfig = [
    'host' => 'localhost',
    'user' => 'root',
    'password' => 'root',
    'database' => 'migrationDb'
];

// Start migration
$migrate = new Migrate($dbConfig);
$migrate->action($argv);
