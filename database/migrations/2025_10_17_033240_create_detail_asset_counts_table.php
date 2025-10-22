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
        Schema::create('detail_asset_counts', function (Blueprint $table) {
            $table->id();

            // Foreign key ke tabel header
            $table->foreignId('header_asset_count_id')->constrained('header_asset_counts')->onDelete('cascade');

            // Relasi ke tabel master_assets (sesuaikan nama tabel jika berbeda)
            $table->foreignId('AssetID')->constrained('master_assets');
            
            $table->integer('LineNumber');
            $table->integer('Jumlah');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_asset_counts');
    }
};