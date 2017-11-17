<?php
namespace Migration;

use Terminal\Terminal;
use Debug\Debug;
use NamingConvention\NamingConvention;
use Database\Database;

class Migration
{
    private $migrationRootPath;
    private $statusFile;

    public function __construct($config)
    {
        if ($config['migrationPath']) {
            $this->migrationRootPath = $config['migrationPath'];
        } else {
            $this->migrationRootPath = __DIR__ . '/../migration/';
        }

        $dbConfig = [
            'host' => $config['host'],
            'user' => $config['user'],
            'password' => $config['password'],
            'database' => $config['database']
        ];

        Database::getConnection($dbConfig);

        $migrationTable = new MigrationTable();
        $migrationTable->create();
    }

    public function up()
    {
        $activeRecordFileNames = $this->getMigrationsToDoFileNames();
        if (!count($activeRecordFileNames)) {
            Terminal::outln($this->addPostfix('== nothing to do ', '=', 70), Terminal::YELLOW);
            return;
        } else {
            foreach($activeRecordFileNames as $activeRecordFileName) {
                $obj = $this->getActivRecordObj($activeRecordFileName);
                $this->_up($obj, $activeRecordFileName);
                $this->saveStatus($activeRecordFileName);
            }
        }
    }

    public function rollback()
    {
        $activeRecordFileName = $this->loadStatus();
        Terminal::outln($this->addPostfix('== rollback ', '=', 70));

        if (!$activeRecordFileName) {
            Terminal::outln($this->addPostfix('== nothing to do ', '=', 70), Terminal::YELLOW);
            return;
        }

        $obj = $this->getActivRecordObj($activeRecordFileName);
        $this->down($obj, $activeRecordFileName);
        $this->saveStatus($this->getPreviousActiveRecordFileName($activeRecordFileName));
    }

    public function printStatus($rows = 0)
    {
        $migrationFileNames = $this->getMigrationFileNames('DESC');
        $currentMigrationFileName = $this->loadStatus();

        Terminal::outln('Current-File: ' . $currentMigrationFileName);

        $headings = $this->addPostfix('Status   Migration ID      Migration Name', ' ', 70);
        $line = $this->addPostfix('', '-', 70);
        Terminal::outln($headings);
        Terminal::outln($line);

        $count = 0;
        foreach($migrationFileNames as $migrationFileName) {
            $id = $this->getActiveRecordId($migrationFileName);
            $id = $this->addPostfix($id, ' ', 18);

            if ($currentMigrationFileName == $migrationFileName) {
                $status = ' up';
                $statusColor = Terminal::GREEN;
            } else {
                $status = ' down';
                $statusColor = Terminal::RED;
            }

            $status =  $this->addPostfix($status, ' ', 9);
            $name = $this->getActiveRecordClassName($migrationFileName);
            Terminal::out($status, $statusColor);
            Terminal::outln($id . $name);

            if (++$count == $rows) {
                break;
            }
        }
    }

    public function saveStatus($status)
    {
        $migrationTable = new MigrationTable();
        $migrationTable->setStatus($status);

        // Use this when save status as file.
        //file_put_contents($this->migrationStatusPath, $status);
    }

    public function loadStatus()
    {
        $migrationTable = new MigrationTable();
        $statusArray = $migrationTable->getStatus();
        return $statusArray['status'];

        // Use this when load status out of file
        // if (file_exists($this->migrationStatusPath)) {
        //     return trim(file_get_contents($this->migrationStatusPath));
        // } else {
        //     Debug::error("Migration directory not exsist.\n" . $this->migrationRootPath);
        //     die();
        // }
    }

    public function getMigrationFileNames($sort = 'ASC')
    {
        if (!\file_exists($this->migrationRootPath)) {
            Debug::error("Migration directory not exsist.\n" . $this->migrationRootPath);
            die();
        }

        $files = scandir($this->migrationRootPath);

        if ($sort == 'DESC') {
            rsort($files);
        } else {
            sort($files);
        }

        $migrationFileNames = array();
        foreach ($files as $file) {
            if ($this->isMigrationFileName($file)) {
                $migrationFileNames[] = $file;
            }
        }

        return $migrationFileNames;
    }

    public function isMigrationFileName($file)
    {
        $timestamp = substr($file, 0, 14);
        $underscore = substr($file, 14, 1);
        $extention = substr($file, -4, 4);

        if ($extention != '.php') {
            return false;
        }

        if ($underscore != '_') {
            return false;
        }

        if (!is_numeric($timestamp)) {
            return false;
        }

        return true;
    }

    public function getMigrationsToDoFileNames()
    {
        $migrationFileNames = $this->getMigrationFileNames();
        $statusMigraionFileName = $this->loadStatus();

        $migrationToDoFileNames = array();
        foreach ($migrationFileNames as $migrationFileName) {
            if ($collecting) {
                $migrationToDoFileNames[] = $migrationFileName;
            }
            if ($migrationFileName == $statusMigraionFileName) {
                $collecting = true;
            }
        }

        if ($collecting) {
            return $migrationToDoFileNames;
        } else {
            return $migrationFileNames;
        }
    }


    private function getPreviousActiveRecordFileName($currentActiveRecordFileName)
    {
        $activeRecordFileNames = $this->getMigrationFileNames('DESC');
        $previousActiveRecordFileName = '';

        $next = false;
        foreach($activeRecordFileNames as $activeRecordFileName) {
            if ($next == true) {
                $previousActiveRecordFileName = $activeRecordFileName;
                break;
            }
            if ($activeRecordFileName == $currentActiveRecordFileName) {
                $next = true;
            }
        }

        return $previousActiveRecordFileName;
    }


    private function _up($obj, $activeRecordFileName)
    {
        $className = $this->getActiveRecordClassName($activeRecordFileName);
        Terminal::outln($this->addPostfix('== ' . $className .': migrating ', '=', 70));
        $obj->up();
    }

    private function down($obj, $activeRecordFileName)
    {
        $className = $this->getActiveRecordClassName($activeRecordFileName);
        Terminal::outln($this->addPostfix('== ' . $className .': migrating rollback ', '=', 70));
        $obj->down();
    }

    public function getActivRecordObj($activeRecordFileName)
    {
        $namespace =  $this->getActiveRecordNameSpace($activeRecordFileName);
        $this->loadActiveRecordFileIntoNamespace($activeRecordFileName, $namespace);
        $classNameCamelCase .= $this->getActiveRecordClassName($activeRecordFileName);
        try {
            $rc = new \ReflectionClass('\\' . $namespace . '\\' . $classNameCamelCase);
            $obj = $rc->newInstance();
        } catch(\ReflectionException $e) {
            Debug::error("Migration class not exsits: " . $classNameCamelCase);
            die();
        }
        return $obj;
    }

    public function loadActiveRecordFileIntoNamespace($activeRecordFileName, $namespace) {
        $filePath = $this->migrationRootPath . '/' . $activeRecordFileName;
        if (!\file_exists($filePath)) {
            Debug::error("Migration file not exsits.\n" . $filePath);
            die();
        }
        eval('namespace ' . $namespace . '; ?>' . file_get_contents($filePath));
    }


    private function getActiveRecordId($activeRecordFileName)
    {
        return substr($activeRecordFileName, 0, 14);
    }

    private function getActiveRecordNameSpace($activeRecordFileName)
    {
        return 'active_record_' . substr($activeRecordFileName, 0, 14);
    }

    private function getActiveRecordClassName($activeRecordFileName)
    {
        $classNameSnakeCase = substr($activeRecordFileName, 15, strlen($activeRecordFileName) - (16+3));
        $classNameCamelCase = NamingConvention::snakeCaseToCamelCaseFirstUpper($classNameSnakeCase);
        return $classNameCamelCase . 'Migration';
    }

    private function addPostfix($str, $postfix, $totalLength)
    {
        $result = $str;
        $count = $totalLength - strlen($str);
        for($i=0; $i<$count;  $i++) {
            $result .= $postfix;
        }
        return $result;
    }
}
