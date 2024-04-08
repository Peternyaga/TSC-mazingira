<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class AddCalculatedColumnToTable extends Migration
{
    public function up()
    {
        DB::unprepared('CREATE TRIGGER calculate_deviation_trigger
BEFORE INSERT ON `planting_exercises`
FOR EACH ROW
BEGIN
    SET NEW.deviation = NEW.`planted seedlings` - NEW.`target seedlings`;
END;');
    }

    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS calculate_column_value');
    }
}

