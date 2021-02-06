<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToItems extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('items', function (Blueprint $table) {
            $table->unsignedBigInteger('buyer_id')->nullable()->change();
            $table->string('name');
            $table->text('description');
            $table->unsignedInteger('price');
            $table->string('state');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('items', function (Blueprint $table) {
            $table->unsignedBigInteger('buyer_id')->nullable(false)->change();
            $table->dropColumn('name');
            $table->dropColumn('description');
            $table->dropColumn('price');
            $table->dropColumn('state');
        });
    }
}
