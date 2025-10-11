<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('permintaanperbaikandetail', function (Blueprint $table) {
            $table->id();
            $table->string('NoTransaksi');
            $table->integer('NoUrut');
            $table->string('KodeAsset');
            $table->string('NamaAsset');
            $table->double('Qty')->default(0);
            $table->string('KodeLokasi');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('NoTransaksi')
                ->references('NoTransaksi')
                ->on('permintaanperbaikanheader')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permintaanperbaikandetail');
    }
};
