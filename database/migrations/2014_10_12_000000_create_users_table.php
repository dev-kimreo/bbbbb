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
            $table->char('language', 2)->default('ko');
            $table->string('memo_for_managers', 256);
            $table->timestamp('registered_at')->nullable()->index();
            $table->timestamp('inactivated_at')->nullable();
            $table->timestamp('last_authorized_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['created_at']);
        });

        Schema::create('user_sites', function (Blueprint $table) {
            $table->collation = 'utf8mb4_general_ci';
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->string('type', 16)->nullable();
            $table->string('name', 32)->nullable();
            $table->string('url', 256)->nullable();
            $table->string('solution', 16)->nullable();
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

        Schema::create('user_login_logs', function (Blueprint $table) {
            $table->collation = 'utf8mb4_general_ci';
            $table->bigInteger('id');
            $table->foreignId('user_id'); // 로그성 테이블의 유연성을 위해 제약조건 미설정
            $table->foreignId('manager_id')->nullable(); // 로그성 테이블의 유연성을 위해 제약조건 미설정
            $table->foreignId('client_id');
            $table->char('ip', 15);
            $table->timestamp('created_at')->index();
            $table->primary(['id', 'created_at']);
        });

        DB::statement("ALTER TABLE `user_login_logs` MODIFY COLUMN `id` BIGINT(20) UNSIGNED NOT NULL auto_increment");

        DB::statement("
            ALTER TABLE `user_login_logs` PARTITION BY RANGE (UNIX_TIMESTAMP(created_at)) (
                PARTITION p2021 VALUES LESS THAN (UNIX_TIMESTAMP('2021-12-31 23:59:59')) ENGINE = InnoDB
            )
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_login_logs');
        Schema::dropIfExists('user_sites');
        Schema::dropIfExists('user_advertising_agrees');
        Schema::dropIfExists('users');
    }
}
