 <?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLinkedComponentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('linked_components', function (Blueprint $table) {
            $table->collation = 'utf8mb4_general_ci';
            $table->id();
            $table->foreignId('linked_component_group_id')->constrained();
            $table->foreignId('component_id')->constrained();
            $table->string('name', 64);
            $table->text('etc')->nullable();
            $table->boolean('display_on_pc')->default(false);
            $table->boolean('display_on_mobile')->default(false);
            $table->integer('sort')->default(1);
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
        Schema::dropIfExists('linked_components');
    }
}
