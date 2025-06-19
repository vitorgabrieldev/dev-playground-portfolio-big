<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomersTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('customers', function (Blueprint $table) {
			$table->id();
			$table->uuid('uuid')->unique();
			$table->string('name');
			$table->string('avatar')->nullable();
			$table->string('document',20)->nullable();
			$table->string('cnpj',20)->nullable();
			$table->string('email');
            $table->string('phone',18);
            $table->date('birth_date')->nullable();
            $table->unsignedBigInteger('perfil_acesso_id');
			$table->timestamp('email_verified_at')->nullable();
			$table->timestamp('account_verified_at')->nullable();
            $table->timestamp('accepted_term_of_users_at')->nullable();
            $table->timestamp('accepted_policy_privacy_at')->nullable();
			$table->boolean('accept_newsletter')->default(0);
			$table->string('password')->nullable();
			$table->boolean('notify_general')->default(1);
			$table->boolean('is_active')->default(1);
			$table->rememberToken();
			$table->timestamps();
			$table->softDeletes();
			
            $table->foreign('perfil_acesso_id')->references('id')->on('perfis_acesso');
		});

		/**
		 * Activities
		 */
		Schema::create('customers_activities', function (Blueprint $table) {
			$table->id();
			$table->uuid('uuid')->unique();
			$table->unsignedBigInteger('customer_id');
			$table->nullableMorphs('activity');
			$table->string('activity_name');
			$table->string('message');
			$table->string('action', 30);
			$table->text('old_data')->nullable();
			$table->text('new_data')->nullable();
			$table->ipAddress('ip')->nullable();
			$table->string('user_agent', 200)->nullable();
			$table->string('url')->nullable();
			$table->string('method', 20)->nullable();
			$table->timestamps();

			$table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
		});

		/**
		 * relacionamento com preferencias
		 */
		Schema::create('customers_has_preferencias', function (Blueprint $table) {
			$table->unsignedBigInteger('preferencia_id');
			$table->unsignedBigInteger('customer_id');

			$table->foreign('preferencia_id')->references('id')->on('preferencias')->onDelete('cascade');
			$table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');

			$table->primary(['preferencia_id', 'customer_id']);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('customers_activities');
		Schema::dropIfExists('customers_has_preferencias');
		Schema::dropIfExists('customers');
	}
}
