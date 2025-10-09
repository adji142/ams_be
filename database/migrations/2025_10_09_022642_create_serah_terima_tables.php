<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('serah_terima_headers', function (Blueprint $table) {
            $table->id();
            $table->string('NoSerahTerima')->unique();
            $table->date('TglSerahTerima');
            $table->string('NomorPermintaan');
            $table->unsignedBigInteger('PenerimaID')->nullable();
            $table->string('Keterangan')->nullable();
            $table->tinyInteger('DocStatus')->default(1);
            $table->timestamps();
            $table->softDeletes(); // 🧠 <—— Tambahkan ini

            $table->foreign('NomorPermintaan')->references('NoTransaksi')->on('permintaan_asset_headers')->onDelete('cascade');
            $table->foreign('PenerimaID')->references('id')->on('employees')->onDelete('set null');
        });

        Schema::create('serah_terima_details', function (Blueprint $table) {
            $table->id();
            $table->string('NoSerahTerima');
            $table->string('NomorPermintaan');
            $table->integer('NoUrutPermintaan');
            $table->string('KodeAsset');
            $table->string('NamaAsset');
            $table->double('QtyDiterima')->default(0);
            $table->double('EstimasiHarga')->default(0);
            $table->string('Keterangan')->nullable();
            $table->timestamps();
            $table->softDeletes(); // 🧠 <—— Tambahkan ini juga

            $table->foreign('NoSerahTerima')->references('NoSerahTerima')->on('serah_terima_headers')->onDelete('cascade');
            $table->foreign('NomorPermintaan')->references('NoTransaksi')->on('permintaan_asset_headers')->onDelete('cascade');
        });

    }

    public function down()
    {
        Schema::dropIfExists('serah_terima_details');
        Schema::dropIfExists('serah_terima_headers');
    }
};
