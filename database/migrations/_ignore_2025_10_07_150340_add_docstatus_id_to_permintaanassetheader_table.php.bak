<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('permintaan_asset_headers', function (Blueprint $table) {
            $table->unsignedBigInteger('DocStatus')->nullable()->after('Requester');
        });
    }

    public function down()
    {
        Schema::table('permintaan_asset_headers', function (Blueprint $table) {
            $table->dropColumn('DocStatus');
        });
    }
};
