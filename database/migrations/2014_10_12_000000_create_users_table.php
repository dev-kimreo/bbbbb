<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->collation = 'utf8mb4_general_ci';
            $table->id();
            $table->string('name', 100)->index();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->unsignedTinyInteger('grade')->default('0');
            $table->string('mall_type', 16);
            $table->string('mall_name', 32);
            $table->string('mall_url', 256);
            $table->char('language', 2)->default('ko');
            $table->string('memo_for_managers', 256);
            $table->timestamp('registered_at')->nullable()->index();
            $table->timestamp('inactivated_at')->nullable();
            $table->timestamp('last_authorized_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['created_at']);
        });

        Schema::create('user_linked_solutions', function (Blueprint $table) {
            $table->collation = 'utf8mb4_general_ci';
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->string('name', 16);
            $table->string('apikey', 512)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('user_advertising_agrees', function (Blueprint $table) {
            $table->collation = 'utf8mb4_general_ci';
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->boolean('agree');
            $table->timestamp('created_at')->index();
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
        Schema::dropIfExists('user_linked_solutions');
        Schema::dropIfExists('user_advertising_agrees');
        Schema::dropIfExists('users');
    }
}
