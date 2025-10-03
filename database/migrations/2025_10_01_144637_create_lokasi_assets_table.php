<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('lokasi_assets', function (Blueprint $table) {
            $table->id();
            $table->string('kode_lokasi')->unique();
            $table->string('nama_lokasi');
            $table->text('keterangan')->nullable();
            $table->foreignId('pic_id')->constrained('employees')->cascadeOnDelete();
            $table->unsignedInteger('asset_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('lokasi_assets');
    }
};
