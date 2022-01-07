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
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->unsignedTinyInteger('grade')->default('0');
            $table->char('language', 2)->default('ko');
            $table->string('memo_for_managers', 256);
            $table->timestamp('registered_at')->nullable()->index();
            $table->timestamp('inactivated_at')->nullable();
            $table->timestamp('last_authorized_at')->nullable();
            $table->timestamp('last_password_changed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['created_at']);
        });

        foreach (['active', 'inactive', 'deleted'] as $v) {
            Schema::create('user_privacy_' . $v, function (Blueprint $table) {
                $table->collation = 'utf8mb4_general_ci';
                $table->id();
                $table->foreignId('user_id')->constrained();
                $table->string('name', 100)->index();
                $table->string('email')->unique();
            });
        }

        Schema::create('user_solutions', function (Blueprint $table) {
            $table->collation = 'utf8mb4_general_ci';
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('solution_id')->constrained();
            $table->string('solution_user_id', 128)->nullable();
            $table->string('apikey', 512)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('user_advertising_agrees', function (Blueprint $table) {
            $table->collation = 'utf8mb4_general_ci';
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->boolean('agree');
            $table->timestamp('created_at')->useCurrent()->index();
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
        Schema::dropIfExists('user_login_logs');
        Schema::dropIfExists('user_solutions');
        Schema::dropIfExists('user_sites');  // TODO: 구 버전 마이그레이션 환경 대응 임시 추가
        Schema::dropIfExists('user_advertising_agrees');
        Schema::dropIfExists('user_privacy_active');
        Schema::dropIfExists('user_privacy_inactive');
        Schema::dropIfExists('user_privacy_deleted');
        Schema::dropIfExists('users');
    }
}
