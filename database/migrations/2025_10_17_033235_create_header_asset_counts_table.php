<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('header_asset_counts', function (Blueprint $table) {
            $table->id();
            $table->string('NoTransaksi')->unique();
            $table->date('TglTransaksi');
            
            // Relasi ke tabel employees (sesuaikan nama tabel jika berbeda)
            $table->foreignId('PICID')->constrained('employees')->onUpdate('cascade')->onDelete('restrict');
            
            // Relasi ke tabel lokasi_assets (sesuaikan nama tabel jika berbeda)
            $table->foreignId('LokasiID')->constrained('lokasi_assets')->onUpdate('cascade')->onDelete('restrict');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('header_asset_counts');
    }
};