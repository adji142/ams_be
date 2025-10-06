<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up()
    {
        DB::statement('ALTER TABLE master_assets MODIFY Keterangan VARCHAR(255) NULL;');
    }

    public function down()
    {
        DB::statement('ALTER TABLE master_assets MODIFY Keterangan INT NULL;');
    }
};
