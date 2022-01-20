<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmailTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('email_templates', function (Blueprint $table) {
            $table->collation = 'utf8mb4_general_ci';
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->string('category', 16);
            $table->string('code', 64);
            $table->string('name', 256);
            $table->string('title', 256);
            $table->text('contents');
            $table->text('sending_time');
            $table->boolean('enable');
            $table->boolean('ignore_agree')->default(1);
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
        Schema::dropIfExists('email_templates');
    }
}
