 <?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateComponentOptionPropertiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('component_option_properties', function (Blueprint $table) {
            $table->collation = 'utf8mb4_general_ci';
            $table->id();
            $table->unsignedBigInteger('component_option_id');
            $table->foreign('component_option_id', 'cosbp_co_foreign')->references('id')->on('component_options');
            $table->unsignedBigInteger('component_type_property_id');
            $table->foreign('component_type_property_id', 'cosbp_ctpi_foreign')->references('id')->on('component_type_properties');
            $table->string('key');
            $table->string('name');
            $table->text('initial_value')->nullable();
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
        Schema::dropIfExists('component_option_properties');
    }
}
