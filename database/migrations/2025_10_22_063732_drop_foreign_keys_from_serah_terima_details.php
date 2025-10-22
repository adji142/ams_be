<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migration.
     */
    public function up(): void
    {
        Schema::table('serah_terima_details', function (Blueprint $table) {
            $table->dropForeign('serah_terima_details_nomorpermintaan_foreign');
            $table->dropForeign('serah_terima_details_noserahterima_foreign');
        });
    }

    /**
     * Reverse migration.
     */
    public function down(): void
    {
        Schema::table('serah_terima_details', function (Blueprint $table) {
            // Pastikan menyesuaikan kolom dan tabel referensi sesuai relasi aslinya
            $table->foreign('nomorpermintaan')
                  ->references('NoTransaksi')
                  ->on('permintaan_assets')
                  ->cascadeOnDelete();

            $table->foreign('noserahterima')
                  ->references('NoTransaksi')
                  ->on('serah_terima_headers')
                  ->cascadeOnDelete();
        });
    }
};
