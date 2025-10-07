<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('permintaan_asset_details', function (Blueprint $table) {
            $table->id();
            $table->string('NoTransaksi');
            $table->integer('NoUrut')->default(1);
            $table->string('KodeAsset');
            $table->string('NamaAsset');
            $table->double('Qty')->default(0);
            $table->double('EstimasiHarga')->default(0);
            $table->double('QtySerahTerima')->default(0);
            $table->timestamps();

            $table->foreign('NoTransaksi')
                ->references('NoTransaksi')
                ->on('permintaan_asset_headers')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('permintaan_asset_details');
    }
};
