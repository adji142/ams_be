<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPicToMutasiAssetHeadersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mutasi_asset_headers', function (Blueprint $table) {
            $table->string('PIC_Lama')->nullable()->after('Keterangan');
            $table->string('PIC_Baru')->nullable()->after('PIC_Lama');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mutasi_asset_headers', function (Blueprint $table) {
            $table->dropColumn(['PIC_Lama', 'PIC_Baru']);
        });
    }
}
