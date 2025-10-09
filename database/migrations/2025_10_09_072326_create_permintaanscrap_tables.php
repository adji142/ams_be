<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 🔹 Header Table
        Schema::create('permintaan_scrap_headers', function (Blueprint $table) {
            $table->id();
            $table->string('NoTransaksi')->unique();
            $table->date('TglTransaksi');
            $table->unsignedBigInteger('Requester');
            $table->bigInteger('DocStatus')->default(1); // 0=Close, 1=Open, 99=Batal
            $table->string('Keterangan')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Relasi ke employee
            $table->foreign('Requester')->references('id')->on('employees');
        });

        // 🔹 Detail Table
        Schema::create('permintaan_scrap_details', function (Blueprint $table) {
            $table->id();
            $table->string('NoTransaksi');
            $table->integer('NoUrut');
            $table->string('KodeAsset');
            $table->string('NamaAsset');
            $table->double('Qty')->default(0);
            $table->string('KodeLokasi');
            $table->timestamps();

            // Relasi ke header
            $table->foreign('NoTransaksi')->references('NoTransaksi')->on('permintaan_scrap_headers')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permintaan_scrap_details');
        Schema::dropIfExists('permintaan_scrap_headers');
    }
};
