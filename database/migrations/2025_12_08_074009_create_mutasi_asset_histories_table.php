<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('mutasi_asset_histories', function (Blueprint $table) {
            $table->id();
            $table->string('NoTransaksi');
            $table->date('TglTransaksi');

            // Relasi ke Employee (opsional)
            $table->unsignedBigInteger('PIC_Lama')->nullable();
            $table->unsignedBigInteger('PIC_Baru')->nullable();

            $table->text('Keterangan')->nullable();

            $table->timestamps();

            // Jika ingin FK (optional, boleh diaktifkan jika tabel employees ada)
            // $table->foreign('PIC_Lama')->references('id')->on('employees');
            // $table->foreign('PIC_Baru')->references('id')->on('employees');
        });
    }

    public function down()
    {
        Schema::dropIfExists('mutasi_asset_histories');
    }
};
