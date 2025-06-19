<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Treinamento extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('treinamento', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('categoria_treinamento_id');
			$table->string('name');
            $table->string('capa');
            $table->string('video',350);
            $table->string('duracao')->nullable();
            $table->text('descricao')->nullable();
			$table->integer('order')->default(0);
            $table->boolean('destaque')->default(0);
            $table->boolean('is_active')->default(1);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('categoria_treinamento_id')->references('id')->on('categorias_treinamento')->onDelete('cascade');
        });

        Schema::create('customer_has_treinamento', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('treinamento_id');
            $table->boolean('read')->default(0);
            $table->boolean('favorited')->default(0);
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('treinamento_id')->references('id')->on('treinamento')->onDelete('cascade');

            $table->primary(['customer_id', 'treinamento_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customer_has_treinamento');
        Schema::dropIfExists('treinamento');
    }
}
