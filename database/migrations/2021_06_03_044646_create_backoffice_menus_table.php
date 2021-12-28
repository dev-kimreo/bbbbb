<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBackofficeMenusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('backoffice_menus', function (Blueprint $table) {
            $table->id();
            $table->string('name', 32);
            $table->string('key', 64)->nullable();
            $table->tinyInteger('depth')->default(1);
            $table->unsignedBigInteger('parent')->default(0);
            $table->boolean('last')->default(1);
            $table->unsignedTinyInteger('sort')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });


        Schema::create('backoffice_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('authority_id')->constrained();
            $table->foreignId('backoffice_menu_id')->constrained();
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
        Schema::dropIfExists('backoffice_permissions');
        Schema::dropIfExists('backoffice_menus');
    }
}
