<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('permintaan_asset_headers', function (Blueprint $table) {
            $table->id();
            $table->string('NoTransaksi')->unique();
            $table->date('TglTransaksi');
            $table->integer('Requester');
            $table->string('Keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('permintaan_asset_headers');
    }
};
