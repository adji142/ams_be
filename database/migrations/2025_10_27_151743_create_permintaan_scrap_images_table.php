<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePermintaanScrapImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('permintaan_scrap_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permintaan_scrap_header_id')->constrained('permintaan_scrap_headers')->onDelete('cascade');
            $table->longText('image_base64');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('permintaan_scrap_images');
    }
}
