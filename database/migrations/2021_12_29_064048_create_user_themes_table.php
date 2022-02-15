<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserThemesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_themes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('theme_id')->constrained();
            $table->string('name', 256);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('user_editable_pages', function (Blueprint $table) {
            $table->collation = 'utf8mb4_general_ci';
            $table->id();
            $table->foreignId('user_theme_id')->constrained();
            $table->foreignId('supported_editable_page_id')->constrained();
            $table->string('name', 64)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('user_component_groups', function (Blueprint $table) {
            $table->collation = 'utf8mb4_general_ci';
            $table->id();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('user_editable_page_layouts', function (Blueprint $table) {
            $table->collation = 'utf8mb4_general_ci';
            $table->id();
            $table->foreignId('user_editable_page_id')->constrained();
            $table->foreignId('header_component_group_id')->constrained('user_component_groups');
            $table->foreignId('content_component_group_id')->constrained('user_component_groups');
            $table->foreignId('footer_component_group_id')->constrained('user_component_groups');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('user_components', function (Blueprint $table) {
            $table->collation = 'utf8mb4_general_ci';
            $table->id();
            $table->foreignId('user_component_group_id')->constrained();
            $table->foreignId('component_id')->constrained();
            $table->string('name', 64);
            $table->text('etc')->nullable();
            $table->boolean('display_on_pc')->default(false);
            $table->boolean('display_on_mobile')->default(false);
            $table->integer('sort')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('user_component_options', function (Blueprint $table) {
            $table->collation = 'utf8mb4_general_ci';
            $table->id();
            $table->foreignId('component_option_id')->constrained();
            $table->foreignId('user_component_id')->constrained();
            $table->text('value')->nullable();
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
        Schema::dropIfExists('user_component_options');
        Schema::dropIfExists('user_components');
        Schema::dropIfExists('user_editable_page_layouts');
        Schema::dropIfExists('user_component_groups');
        Schema::dropIfExists('user_editable_pages');
        Schema::dropIfExists('user_themes');
    }
}
