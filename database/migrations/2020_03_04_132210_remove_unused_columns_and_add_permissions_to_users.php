<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveUnusedColumnsAndAddPermissionsToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('email_verified_at');
        });

        if (env('DB_CONNECTION') === 'sqlite') {
            // SQLite does not support adding a column to an existing table
            // with a NOT NULL constraint without a default value
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedTinyInteger('permission')->default(0);
            });
        } else {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedTinyInteger('permission');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
}
