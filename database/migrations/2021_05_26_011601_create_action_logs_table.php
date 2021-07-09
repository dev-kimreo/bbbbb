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
            $table->tinyInteger('client_id');
            $table->foreignId('user_id'); // 로그성 테이블의 유연성을 위해 제약조건 미설정
            $table->morphs('loggable');
            $table->char('crud', 1);
            $table->string('action_type', 16)->nullable();
            $table->string('path', 128);
            $table->string('memo', 512);
            $table->json('columns')->nullable();
            $table->timestamp('created_at')->index();
            $table->primary(['id', 'created_at']);
        });

        DB::statement("ALTER TABLE `action_logs` MODIFY COLUMN `id` BIGINT(20) UNSIGNED NOT NULL auto_increment");

        DB::statement("
            ALTER TABLE `action_logs` PARTITION BY RANGE (UNIX_TIMESTAMP(created_at)) (
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
        Schema::dropIfExists('backoffice_logs');
        Schema::dropIfExists('action_logs');
    }
}
