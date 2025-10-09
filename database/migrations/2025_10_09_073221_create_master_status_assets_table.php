<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_status_assets', function (Blueprint $table) {
            $table->id();
            $table->string('NamaStatusAsset');
            $table->boolean('isDefault')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_status_assets');
    }
};
