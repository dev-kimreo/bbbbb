<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTranslationTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->string('type', 16);
            $table->string('code', 64);
            $table->string('explanation', 512);
            $table->timestamps();
            $table->softDeletes();
            $table->index('code');
        });

        Schema::create('translation_contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('translation_id')->constrained();
            $table->char('lang', 2);
            $table->string('value', 512);
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
        Schema::dropIfExists('translation_contents');
        Schema::dropIfExists('translations');
    }
}
