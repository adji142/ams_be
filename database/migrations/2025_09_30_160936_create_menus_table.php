<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('name');        // Nama menu
            $table->string('url')->nullable();   // URL route
            $table->string('icon')->nullable();  // Icon (opsional)
            $table->unsignedBigInteger('permission_id')->nullable(); // permission yg dibutuhkan
            $table->foreign('permission_id')->references('id')->on('permissions')->nullOnDelete();
            $table->unsignedBigInteger('parent_id')->nullable();     // untuk nested menu
            $table->foreign('parent_id')->references('id')->on('menus')->nullOnDelete();
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('menus');
    }
};
