<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateValeRioDoce extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vale_rio_doce', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
			$table->string('name');
            $table->string('sinopse')->nullable();
            $table->text('text');
			$table->integer('order')->default(0);
            $table->boolean('is_active')->default(1);
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
        Schema::dropIfExists('vale_rio_doce');
    }
}
