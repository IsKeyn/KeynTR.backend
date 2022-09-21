<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateYoutubeChannelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('youtube_channels', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('channel_id')->unique();
            $table->dateTime('published_at');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('custom_url')->nullable();
            $table->json('thumbnails')->nullable();
            $table->integer('view_count');
            $table->integer('subscriber_count');
            $table->integer('video_count');
            $table->boolean('hidden_subscriber_count')->nullable();
            $table->text('keywords')->nullable();
            $table->string('unsubscribed_trailer')->nullable();
            $table->string('banner_external_url')->nullable();
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
        Schema::dropIfExists('youtube_channels');
    }
}
