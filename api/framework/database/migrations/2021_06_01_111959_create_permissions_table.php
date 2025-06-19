<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePermissionsTable extends Migration
{

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('roles', function (Blueprint $table) {
			$table->id();
			$table->uuid('uuid')->unique();
			$table->string('name');
			$table->string('description');
			$table->boolean('is_system')->default(0);
			$table->timestamps();
		});

		Schema::create('permissions', function (Blueprint $table) {
			$table->id();
			$table->uuid('uuid')->unique();
			$table->string('key')->unique();
			$table->string('name');
			$table->string('group');
			$table->integer('order')->default(0);
			$table->timestamps();
		});

		Schema::create('role_has_permissions', function (Blueprint $table) {
			$table->unsignedBigInteger('role_id');
			$table->unsignedBigInteger('permission_id');

			$table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
			$table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');

			$table->primary(['role_id', 'permission_id']);
		});

		Schema::create('user_has_roles', function (Blueprint $table) {
			$table->unsignedBigInteger('user_id');
			$table->unsignedBigInteger('role_id');

			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
			$table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');

			$table->primary(['user_id', 'role_id']);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('user_has_roles');
		Schema::dropIfExists('role_has_permissions');
		Schema::dropIfExists('roles');
		Schema::dropIfExists('permissions');
	}
}
