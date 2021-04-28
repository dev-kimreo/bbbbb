<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttachFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attach_files', function (Blueprint $table) {
            $table->collation = 'utf8mb4_general_ci';
            $table->id();
            $table->string('server', 32);
            $table->string('type', 32);
            $table->unsignedBigInteger('type_id');
            $table->unsignedBigInteger('user_id');
            $table->string('url');
            $table->string('path');
            $table->string('name', 128);
            $table->string('org_name', 128);
            $table->json('etc');
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
        Schema::dropIfExists('attach_files');
    }
}
