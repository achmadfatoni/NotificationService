<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotificationRequestsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('notification_requests', function(Blueprint $table) {
            $table->integer('from_user_id')->unsigned();
            $table->foreign('from_user_id')->references('id')->on('users');

            $table->integer('to_user_id')->unsigned();
            $table->foreign('to_user_id')->references('id')->on('users');

            $table->increments('id');
            $table->timestamps();

            $table->integer('target_id')->unsigned()->nullable();

            $table->string('route');

            $table->enum('channel', ['Sms', 'Email']);

            $table->boolean('sent')->default(false);

            $table->string('response_text')->nullable();
            $table->integer('response_code')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::drop('notification_requests');
    }

}
