<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('perintah_stock_count_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('HeaderID')
                  ->constrained('perintah_stock_count_headers')
                  ->cascadeOnDelete();
            $table->string('KodeAsset', 100);
            $table->integer('LineNumber');
            $table->string('KodeLokasi', 100);
            $table->double('Jumlah')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('perintah_stock_count_details');
    }
};
