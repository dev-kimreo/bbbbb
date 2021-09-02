<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWidgetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('widgets', function (Blueprint $table) {
            $table->collation = 'utf8mb4_general_ci';
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->string('name', 64);
            $table->text('description');
            $table->boolean('enable')->default(0);
            $table->boolean('only_for_manager')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('widget_usages', function (Blueprint $table) {
            $table->collation = 'utf8mb4_general_ci';
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('widget_id')->constrained();
            $table->unsignedTinyInteger('sort')->default(0);
            $table->timestamp('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('widget_usages');
        Schema::dropIfExists('widgets');
    }
}
