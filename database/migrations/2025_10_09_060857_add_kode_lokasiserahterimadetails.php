<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddKodeLokasiserahterimadetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('serah_terima_details', function (Blueprint $table) {
            $table->string('KodeLokasi')->nullable()->after('QtyDiterima');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('serah_terima_details', function (Blueprint $table) {
            $table->dropColumn('KodeLokasi');
        });
    }
}
