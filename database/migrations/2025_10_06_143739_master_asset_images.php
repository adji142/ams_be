<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('master_asset_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('master_asset_id');
            $table->string('file_path'); // path atau URL gambar
            $table->timestamps();

            $table->foreign('master_asset_id')
                ->references('id')
                ->on('master_assets')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('master_asset_images');
    }
};
