<?php

use App\Models\Collection;
use App\Services\CollectionService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHierarchyColumnsToCollections extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('collections', function (Blueprint $table) {
            $table->unsignedInteger('root_collection_id')->nullable();
            $table->foreign('root_collection_id')->references('id')->on('collections');
            $table->unsignedSmallInteger('left')->default(0);
            $table->unsignedSmallInteger('right')->default(0);
            $table->unsignedTinyInteger('depth')->default(0);
            $table->boolean('is_being_shared')->default(false);
        });

        Collection::rebuildHierarchy();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
