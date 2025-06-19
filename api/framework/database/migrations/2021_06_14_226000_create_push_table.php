<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePushTable extends Migration
{

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('push_general', function (Blueprint $table) {
			$table->id();
			$table->uuid('uuid')->unique();
			$table->string('title', 50);
			$table->string('body', 100);
			$table->string('url')->nullable();
			$table->integer('total_users')->nullable();
			$table->text('api_response')->nullable();
			$table->dateTime('scheduled_at')->nullable();
			$table->dateTime('send_at')->nullable();
			$table->timestamps();
			$table->softDeletes();
		});

		Schema::create('push_user', function (Blueprint $table) {
			$table->id();
			$table->uuid('uuid')->unique();
            $table->morphs('user');
			$table->string('title', 50);
			$table->string('body', 100);
			$table->string('url')->nullable();
            // alert
            $table->boolean('is_alert')->default(0);
            $table->string('text')->nullable();
            $table->decimal('max_value', 10, 2)->nullable();
            $table->decimal('min_value', 10, 2)->nullable();

            $table->boolean('read')->default(0);
			$table->text('api_response')->nullable();
			$table->dateTime('scheduled_at')->nullable();
			$table->dateTime('send_at')->nullable();
			$table->timestamps();
			$table->softDeletes();
		});

		Schema::create('push_state', function (Blueprint $table) {
			$table->id();
			$table->uuid('uuid')->unique();
			$table->unsignedBigInteger('state_id');
			$table->string('title', 50);
			$table->string('body', 100);
			$table->string('url')->nullable();
			$table->integer('total_users')->nullable();
			$table->text('api_response')->nullable();
			$table->dateTime('scheduled_at')->nullable();
			$table->dateTime('send_at')->nullable();
			$table->timestamps();
			$table->softDeletes();

			$table->foreign('state_id')->references('id')->on('states')->onDelete('cascade');
		});

		Schema::create('push_city', function (Blueprint $table) {
			$table->id();
			$table->uuid('uuid')->unique();
			$table->unsignedBigInteger('city_id');
			$table->string('title', 50);
			$table->string('body', 100);
			$table->string('url')->nullable();
			$table->integer('total_users')->nullable();
			$table->text('api_response')->nullable();
			$table->dateTime('scheduled_at')->nullable();
			$table->dateTime('send_at')->nullable();
			$table->timestamps();
			$table->softDeletes();

			$table->foreign('city_id')->references('id')->on('cities')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('push_general');
		Schema::dropIfExists('push_user');
		Schema::dropIfExists('push_state');
		Schema::dropIfExists('push_city');
	}
}
