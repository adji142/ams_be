<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('permintaanperbaikanheader', function (Blueprint $table) {
            $table->string('Approval', 1)->change();
        });
    }

    /**
     * Kembalikan migrasi (rollback).
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('permintaanperbaikanheader', function (Blueprint $table) {
            $table->integer('Approval')->change();
        });
    }
};
