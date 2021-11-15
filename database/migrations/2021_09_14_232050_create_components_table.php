<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateComponentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('components', function (Blueprint $table) {
            $table->collation = 'utf8mb4_general_ci';
            $table->id();
            $table->foreignId('user_partner_id')->constrained();
            $table->foreignId('solution_id')->constrained();
            $table->string('name', 64);
            $table->boolean('use_other_than_maker');            // 제작자 외 회원 사용가능 여부
            $table->string('first_category');                   // 컴포넌트 카테고리1
            $table->string('second_category')->nullable();      // 컴포넌트 카테고리2
            $table->boolean('use_blank');                       // 여백 옵션
            $table->boolean('use_all_page');                    // 사용 페이지 타입 (전체 = true, 선택 = false)
            $table->boolean('display');                         // 노출 여부
            $table->string('status')->default('registering');   // 상태 구분 (등록중, 등록완료)
            $table->text('manager_memo');                       // 관리자 메모
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
        Schema::dropIfExists('components');
    }
}
