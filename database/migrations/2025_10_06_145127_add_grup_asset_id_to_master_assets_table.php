<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('master_assets', function (Blueprint $table) {
            $table->unsignedBigInteger('GrupAssetID')->nullable()->after('id');
            $table->foreign('GrupAssetID')->references('id')->on('grup_assets')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('master_assets', function (Blueprint $table) {
            $table->dropForeign(['GrupAssetID']);
            $table->dropColumn('GrupAssetID');
        });
    }
};
