<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('master_items', function (Blueprint $table) {
            $table->string('foto')->nullable();
        });
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('kode')->unique();
            $table->timestamps();
        });
        Schema::create('category_master_item', function (Blueprint $table) {
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('master_item_id')->constrained()->cascadeOnDelete();
            $table->primary(['category_id', 'master_item_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('category_master_item');
        Schema::dropIfExists('categories');
        Schema::table('master_items', function (Blueprint $table) {
            $table->dropColumn('foto');
        });
    }
};
