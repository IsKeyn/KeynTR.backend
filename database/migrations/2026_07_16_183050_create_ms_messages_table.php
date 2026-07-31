<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ms_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_id')->constrained('ms_chats');
            $table->foreignId('user_id')->constrained(); // Отправитель

            $table->unsignedBigInteger('reply_to_id')->nullable(); // Для ответа на сообщение
            $table->foreign('reply_to_id')->references('id')->on('ms_messages')->nullOnDelete();

            $table->enum('type', ['text', 'image', 'file', 'system'])->default('text');
            $table->text('body'); // Текст или JSON с данными для file/image
            $table->integer('sort')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            // Для быстрой загрузки истории чата (пагинация по ID)
            $table->index(['chat_id', 'id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ms_messages');
    }
};
