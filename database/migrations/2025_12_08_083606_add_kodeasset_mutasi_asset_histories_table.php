<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddKodeassetMutasiAssetHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mutasi_asset_histories', function (Blueprint $table) {
             $table->string('KodeAsset')->nullable()->after('Keterangan');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mutasi_asset_histories', function (Blueprint $table) {
            $table->dropColumn(['KodeAsset']);
        });
    }
}
