<?php
use RobinTheHood\Database\DatabaseType;

class CreateUserMigration extends \RobinTheHood\Migration\ActiveRecord
{
    public function up()
    {
        $this->createTable('user',[
            ['id', DatabaseType::T_PRIMARY, true],
            ['created', DatabaseType::T_DATE_TIME],
            ['changed', DatabaseType::T_DATE_TIME],
            ['confirmed', DatabaseType::T_DATE_TIME],
            ['mail', DatabaseType::T_STRING],
            ['password', DatabaseType::T_STRING],
            ['verification', DatabaseType::T_STRING]
        ]);
    }


    public function down()
    {
        $this->dropTable('user');
    }
}
