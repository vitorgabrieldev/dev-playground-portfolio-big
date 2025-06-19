<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogsTable extends Migration
{

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('logs', function (Blueprint $table) {
			$table->id();
			$table->uuid('uuid')->unique();
			$table->unsignedBigInteger('user_id');
			$table->nullableMorphs('log');
			$table->string('log_name')->nullable();
			$table->string('message');
			$table->string('action', 30);
			$table->text('old_data')->nullable();
			$table->text('new_data')->nullable();
			$table->ipAddress('ip')->nullable();
			$table->string('user_agent', 200)->nullable();
			$table->string('url')->nullable();
			$table->string('method', 20)->nullable();
			$table->timestamps();

			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('logs');
	}
}
