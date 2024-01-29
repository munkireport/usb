<?php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Capsule\Manager as Capsule;

class UsbAddTimestamp extends Migration
{
    private $tableName = 'usb';

    public function up()
    {
        $capsule = new Capsule();
        $capsule::schema()->table($this->tableName, function (Blueprint $table) {
            $table->bigInteger('timestamp')->nullable();
            $table->boolean('connected')->nullable();
        });

        # Force reload USB data
        $capsule::unprepared("UPDATE hash SET hash = 'x' WHERE name = '$this->tableName'");
    }

    public function down()
    {
        $capsule = new Capsule();
        $capsule::schema()->table($this->tableName, function (Blueprint $table) {
            $table->dropColumn('timestamp');
            $table->dropColumn('connected');
        });
    }
}
