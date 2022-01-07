<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActionLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('action_logs', function (Blueprint $table) {
            $table->bigInteger('id');
            $table->string('conn_id', 32)->nullable();
            $table->tinyInteger('client_id')->nullable();
            $table->foreignId('user_id')->nullable(); // 로그성 테이블의 유연성을 위해 제약조건 미설정
            $table->tinyInteger('user_grade')->nullable();
            $table->char('ip', 15);
            $table->string('loggable_type');
            $table->foreignId('loggable_id');
            $table->string('request_location', 255)->nullable();
            $table->string('request_path', 255)->nullable();
            $table->char('crud', 1);
            $table->string('path', 128);
            $table->string('title', 16)->nullable();
            $table->string('memo', 512)->nullable();
            $table->json('properties')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->primary(['id', 'created_at']);
            $table->index(['created_at', 'loggable_type', 'loggable_id']);
        });

        DB::statement("ALTER TABLE `action_logs` MODIFY COLUMN `id` BIGINT(20) UNSIGNED NOT NULL auto_increment");

        DB::statement("
            ALTER TABLE `action_logs` PARTITION BY RANGE (UNIX_TIMESTAMP(created_at)) (
                PARTITION p2021 VALUES LESS THAN (UNIX_TIMESTAMP('2021-12-31 23:59:59')) ENGINE = InnoDB,
                PARTITION p2022 VALUES LESS THAN (UNIX_TIMESTAMP('2022-12-31 23:59:59')) ENGINE = InnoDB
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
        Schema::dropIfExists('backoffice_logs');
        Schema::dropIfExists('action_logs');
    }
}
