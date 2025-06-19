<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSystemLogsTable extends Migration
{

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('system_logs', function (Blueprint $table) {
			$table->id();
			$table->uuid('uuid')->unique();
			$table->nullableMorphs('user');
			$table->text('message')->nullable();
			$table->string('level', 20)->default('INFO');
			$table->text('context')->nullable();
			$table->ipAddress('ip')->nullable();
			$table->string('user_agent', 200)->nullable();
			$table->string('url')->nullable();
			$table->string('method', 20)->nullable();
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('system_logs');
	}
}
