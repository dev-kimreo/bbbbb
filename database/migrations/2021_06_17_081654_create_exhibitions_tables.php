<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExhibitionsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('popups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->string('title', 128);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('popup_device_contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('popup_id')->constrained();
            $table->enum('device', ['pc', 'mobile']);
            $table->text('contents');
            // No timestamps
            $table->softDeletes();
            $table->unique(['popup_id', 'device']);
        });

        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->string('title', 128);
            $table->string('url', 256);
            $table->string('ga_code', 128);
            $table->string('memo', 256);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('banner_device_contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('banner_id')->constrained();
            $table->enum('device', ['pc', 'mobile']);
            // No timestamps
            $table->softDeletes();
            $table->unique(['banner_id', 'device']);
        });

        Schema::create('exhibitions', function (Blueprint $table) {
            $table->id();
            $table->morphs('exhibitable');
            $table->foreignId('exhibition_category_id');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->string('target_opt');
            $table->json('target_grade')->nullable();
            $table->unsignedSmallInteger('sort')->default(999);
            $table->boolean('visible')->default('1');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('exhibition_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 64);
            $table->string('url', 256);
            $table->enum('division', ['popup', 'banner']);
            $table->string('site', 32);
            $table->unsignedTinyInteger('max')->default(1);
            $table->boolean('enable')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('exhibition_target_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exhibition_id')->constrained();
            $table->foreignId('user_id')->constrained();
            // No timestamps
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
        Schema::dropIfExists('banner_device_contents');
        Schema::dropIfExists('banners');
        Schema::dropIfExists('popup_device_contents');
        Schema::dropIfExists('popups');
        Schema::dropIfExists('exhibition_categories');
        Schema::dropIfExists('exhibition_target_users');
        Schema::dropIfExists('exhibitions');
    }
}
