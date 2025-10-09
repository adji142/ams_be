<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusidToPermintaanScrapTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('permintaan_scrap_details', function (Blueprint $table) {
            $table->unsignedBigInteger('StatusID')->nullable()->after('KodeLokasi');

            $table->foreign('StatusID')
                ->references('id')
                ->on('master_status_assets')
                ->nullOnDelete(); // jika status dihapus, set null
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
            $table->dropForeign(['StatusID']);
            $table->dropColumn('StatusID');
        });
    }
}
