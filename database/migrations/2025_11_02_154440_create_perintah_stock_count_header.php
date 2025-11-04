<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('perintah_stock_count_headers', function (Blueprint $table) {
            $table->id();
            $table->string('NoTransaksi', 50)->unique();
            $table->date('TglPerintah');
            $table->unsignedBigInteger('PIC');
            $table->string('Keterangan')->nullable();
            $table->timestamps();
            $table->softDeletes(); // untuk soft delete
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('perintah_stock_count_headers');
    }
};
