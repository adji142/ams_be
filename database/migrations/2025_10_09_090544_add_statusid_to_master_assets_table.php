<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('master_assets', function (Blueprint $table) {
            $table->unsignedBigInteger('StatusID')->nullable()->after('PIC');

            $table->foreign('StatusID')
                ->references('id')
                ->on('master_status_assets')
                ->nullOnDelete(); // jika status dihapus, set null
        });
    }

    public function down(): void
    {
        Schema::table('master_assets', function (Blueprint $table) {
            $table->dropForeign(['StatusID']);
            $table->dropColumn('StatusID');
        });
    }
};
