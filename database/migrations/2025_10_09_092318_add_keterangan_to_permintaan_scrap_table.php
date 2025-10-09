<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddKeteranganToPermintaanScrapTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('permintaan_scrap_details', function (Blueprint $table) {
            $table->unsignedBigInteger('Keterangan')->nullable()->after('StatusID');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('permintaan_scrap_details', function (Blueprint $table) {
            $table->dropColumn('Keterangan');
        });
    }
}
