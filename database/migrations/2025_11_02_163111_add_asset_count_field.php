<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAssetCountField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('header_asset_counts', function (Blueprint $table) {
            $table->unsignedBigInteger('perintah_id')->nullable()->after('id');
            $table->dateTime('JamMulai')->nullable()->after('perintah_id');
            $table->dateTime('JamSelesai')->nullable()->after('JamMulai');

            $table->foreign('perintah_id')
                ->references('id')
                ->on('perintah_stock_count_headers')
                ->onDelete('set null');
        });

        Schema::table('detail_asset_counts', function (Blueprint $table) {
            $table->integer('line_perintah')->nullable()->after('header_asset_count_id');
            $table->string('kode_asset_perintah', 100)->nullable()->after('line_perintah');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('header_asset_counts', function (Blueprint $table) {
            $table->dropForeign(['perintah_id']);
            $table->dropColumn(['perintah_id', 'JamMulai', 'JamSelesai']);
        });

        Schema::table('detail_asset_counts', function (Blueprint $table) {
            $table->dropColumn(['line_perintah', 'kode_asset_perintah']);
        });
    }
}
