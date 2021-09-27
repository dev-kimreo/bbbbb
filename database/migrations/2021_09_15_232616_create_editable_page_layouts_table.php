<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEditablePageLayoutsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('editable_page_layouts', function (Blueprint $table) {
            $table->collation = 'utf8mb4_general_ci';
            $table->id();
            $table->foreignId('editable_page_id')->constrained();
            $table->foreignId('header_component_group_id')->constrained('linked_component_groups');
            $table->foreignId('content_component_group_id')->constrained('linked_component_groups');
            $table->foreignId('footer_component_group_id')->constrained('linked_component_groups');
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
        Schema::dropIfExists('editable_page_layouts');
    }
}
