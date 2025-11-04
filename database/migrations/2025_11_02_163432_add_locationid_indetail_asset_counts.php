<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLocationidIndetailAssetCounts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('detail_asset_counts', function (Blueprint $table) {
            $table->unsignedBigInteger('DetailLokasiID')->nullable()->after('id');

        $table->foreign('DetailLokasiID')
            ->references('id')
            ->on('lokasi_assets')
            ->onDelete('set null')
            ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
