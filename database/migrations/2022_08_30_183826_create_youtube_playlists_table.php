<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateYoutubePlaylistsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('youtube_playlists', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('playlist_id')->unique();
            $table->dateTime('published_at');
            $table->string('title');
            $table->text('description');
            $table->json('thumbnails');
            $table->integer('video_count');
            $table->text('player');
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
        Schema::dropIfExists('youtube_playlists');
    }
}
