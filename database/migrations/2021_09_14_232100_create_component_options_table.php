<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateComponentOptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('component_options', function (Blueprint $table) {
            $table->collation = 'utf8mb4_general_ci';
            $table->id();
            $table->foreignId('component_version_id')->constrained();
            $table->foreignId('component_type_id')->constrained();
            $table->string('name', 64);
            $table->string('key', 64);
            $table->boolean('display_on_pc')->default(false);
            $table->boolean('display_on_mobile')->default(false);
            $table->boolean('hideable');
            $table->text('attributes')->nullable();
            $table->text('help')->nullable();
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
        Schema::dropIfExists('component_options');
    }
}
