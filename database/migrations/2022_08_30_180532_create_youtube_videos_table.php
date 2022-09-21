<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateYoutubeVideosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('youtube_videos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('video_id')->unique();
            $table->dateTime('published_at');
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('thumbnails')->nullable();
            $table->string('playlist_id')->nullable();
            $table->integer('position_in_playlist')->nullable();
            $table->text('player')->nullable();
            $table->string('status');
            $table->string('channel_id');
            $table->softDeletes();
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
        Schema::dropIfExists('youtube_videos');
    }
}
