<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('permintaanperbaikanheader', function (Blueprint $table) {
            $table->id();
            $table->string('NoTransaksi')->unique();
            $table->date('TglTransaksi');
            $table->bigInteger('DocStatus')->default(1);
            $table->text('Keterangan')->nullable();
            $table->tinyInteger('Approval')->default(0)->comment('0: Pending, 1: Approve, 2: Selesai, 9: Reject');
            $table->string('KeteranganApproval')->nullable();
            $table->datetime('ApproveDate')->nullable();
            $table->string('ApproveBy')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permintaanperbaikanheader');
    }
};
