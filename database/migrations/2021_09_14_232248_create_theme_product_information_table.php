<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateThemeProductInformationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('theme_product_information', function (Blueprint $table) {
            $table->collation = 'utf8mb4_general_ci';
            $table->id();
            $table->foreignId('theme_product_id')->constrained();
//            $table->string('business', 32);
//            $table->string('layout', 32);
//            $table->string('numberOfItemsSold', 32);
//            $table->string('color', 32);
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('theme_product_information');
    }
}
