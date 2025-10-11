<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mutasi_asset_headers', function (Blueprint $table) {
            $table->id();
            $table->string('NoTransaksi')->unique();
            $table->date('TglTransaksi');
            $table->bigInteger('DocStatus')->default(1); // 1=Open, 0=Close, 99=Batal
            $table->string('Keterangan')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('mutasi_asset_details', function (Blueprint $table) {
            $table->id();
            $table->string('NoTransaksi');
            $table->integer('NoUrut');
            $table->string('KodeAsset');
            $table->string('NamaAsset');
            $table->double('Qty')->default(0);
            $table->string('KodeLokasiAsal');
            $table->string('KodeLokasiTujuan');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('NoTransaksi')->references('NoTransaksi')->on('mutasi_asset_headers')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mutasi_asset_details');
        Schema::dropIfExists('mutasi_asset_headers');
    }
};
