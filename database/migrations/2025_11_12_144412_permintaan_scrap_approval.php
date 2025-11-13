<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PermintaanScrapApproval extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('permintaan_scrap_approval', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('NoTransaksi', 50);
            $table->integer('Level');
            $table->unsignedBigInteger('ApproverID');
            $table->tinyInteger('Status')->default(0)->comment('0: Pending, 1: Approved, 9: Rejected');
            $table->text('Keterangan')->nullable();
            $table->dateTime('ApprovedAt')->nullable();
            $table->timestamps();

            // Relasi ke tabel employees (pastikan tabelnya ada)
            $table->foreign('ApproverID')->references('id')->on('employees')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('permintaan_scrap_approval');
    }
}
