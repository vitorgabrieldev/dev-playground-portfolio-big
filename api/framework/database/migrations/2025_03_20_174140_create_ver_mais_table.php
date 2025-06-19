<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ver_mais', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('icone');
            $table->string('video')->nullable();
            $table->string('capa')->nullable();
            $table->string('name');
            $table->text('text');
			$table->integer('order')->default(0);
			$table->boolean('is_active')->default(1);
			$table->timestamps();
			$table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ver_mais');
    }
};
