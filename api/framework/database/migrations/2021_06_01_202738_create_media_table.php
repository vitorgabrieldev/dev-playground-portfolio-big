<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMediaTable extends Migration
{

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('media', function (Blueprint $table) {
			$table->id();
			$table->uuid('uuid')->unique();
			$table->morphs('media');
			$table->string('type', 30);
			$table->string('file');
			$table->string('name', 100)->nullable();
			$table->string('description', 200)->nullable();
			$table->string('mime', 100)->nullable();
			$table->unsignedDecimal('size')->nullable();
			$table->mediumInteger('width', false, true)->nullable();
			$table->mediumInteger('height', false, true)->nullable();
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
		Schema::dropIfExists('media');
	}
}
