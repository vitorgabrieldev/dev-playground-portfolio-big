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
        Schema::create('grupo_aiz', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->text('text')->nullable();
			$table->string('file')->nullable();
            $table->string('type',10)->nullable();
			$table->timestamps();
			$table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('grupo_aiz');
    }
};
