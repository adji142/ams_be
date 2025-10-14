<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompanySettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company_settings', function (Blueprint $table) {
            $table->id();
            $table->string('NamaPerusahaan')->nullable();
            $table->string('Alamat1')->nullable();
            $table->string('Alamat2')->nullable();
            $table->string('Email')->nullable();
            $table->string('NoTlp')->nullable();
            $table->text('Icon')->nullable();
            $table->decimal('LabelWidth', 8, 2)->nullable();
            $table->decimal('LabelHeight', 8, 2)->nullable();
            $table->decimal('H1Size', 8, 2)->nullable();
            $table->decimal('H2Size', 8, 2)->nullable();
            $table->decimal('PSize', 8, 2)->nullable();
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
        Schema::dropIfExists('company_settings');
    }
}
