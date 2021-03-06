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
            $table->nullableMorphs('linkable');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('translation_contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('translation_id')->constrained();
            $table->char('lang', 2);
            $table->text('value');
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
