<?php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Capsule\Manager as Capsule;

class UsbReupload extends Migration
{
    private $tableName = 'usb';

    public function up()
    {
        $capsule = new Capsule();

        # Force reload USB data
        $capsule::unprepared("UPDATE hash SET hash = 'x' WHERE name = '$this->tableName'");
    }

    public function down()
    {
       // No going down
    }
}
