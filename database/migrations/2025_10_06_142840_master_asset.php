<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('master_assets', function (Blueprint $table) {
            $table->id();
            $table->string('KodeAsset')->unique();
            $table->string('NamaAsset');
            $table->date('TglBeli');
            $table->date('TglKapitalisasi')->nullable();
            $table->integer('UmurPakai')->nullable();
            $table->integer('Keterangan')->nullable();
            $table->double('Jumlah')->default(0);

            // relasi ke employees
            $table->unsignedBigInteger('PIC')->nullable();
            $table->foreign('PIC')->references('id')->on('employees')->onDelete('set null');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('master_assets');
    }
};
