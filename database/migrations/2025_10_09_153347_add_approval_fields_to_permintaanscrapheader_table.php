<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permintaanscrapheader', function (Blueprint $table) {
            $table->tinyInteger('Approval')->default(0)->comment('0: Pending, 1: Approve, 9: Reject');
            $table->string('KeteranganApproval')->nullable();
            $table->dateTime('ApproveDate')->nullable();
            $table->unsignedBigInteger('ApproveBy')->nullable();

            // Optional: foreign key ke employee jika mau
            $table->foreign('ApproveBy')->references('id')->on('employees')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('permintaanscrapheader', function (Blueprint $table) {
            $table->dropForeign(['ApproveBy']);
            $table->dropColumn(['Approval', 'KeteranganApproval', 'ApproveDate', 'ApproveBy']);
        });
    }
};
