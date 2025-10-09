<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AssetMovingHistories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('asset_moving_histories', function (Blueprint $table) {
            $table->id();
            $table->string('KodeAsset');
            $table->string('KodeLokasi');
            $table->string('NoReff');
            $table->string('BaseReff');
            $table->double('Jumlah');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('asset_moving_histories');
    }
}
