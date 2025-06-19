<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotifications extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
			$table->string('name');
            $table->string('descricao')->nullable();
            $table->string('video')->nullable();
            $table->string('capa')->nullable();
            $table->text('text')->nullable();
            $table->string('titulo_button')->nullable();
            $table->string('link_button')->nullable();
            $table->dateTime('data_envio');
            $table->boolean('send_push')->default(0);
            $table->boolean('is_active')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('customer_has_notifications', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('notification_id');
            $table->boolean('read')->default(0);
            $table->boolean('deleted')->default(0);
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('notification_id')->references('id')->on('notifications')->onDelete('cascade');

            $table->primary(['customer_id', 'notification_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customer_has_notifications');
        Schema::dropIfExists('notifications');
    }
}
