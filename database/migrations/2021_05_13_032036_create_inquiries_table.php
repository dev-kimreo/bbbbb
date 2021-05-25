<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInquiriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inquiries', function (Blueprint $table) {
            $table->collation = 'utf8mb4_general_ci';
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('assignee_id')->nullable()->references('id')->on('users');
            $table->string('title', 100);
            $table->mediumText('question');
            $table->string('status', 16)->default('waiting');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('inquiry_answers', function (Blueprint $table) {
            $table->collation = 'utf8mb4_general_ci';
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('inquiry_id')->constrained();
            $table->mediumText('answer');
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
        Schema::dropIfExists('inquiry_answers');
        Schema::dropIfExists('inquiries');
    }
}
