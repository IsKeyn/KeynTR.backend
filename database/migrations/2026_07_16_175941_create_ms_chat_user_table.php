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
        Schema::create('ms_chat_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_id')->constrained('ms_chats')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->enum('role', ['member', 'admin'])->default('member');

            // ID последнего сообщения, которое пользователь прочитал.
            // Непрочитанные = сообщения в чате, где message.id > user.last_read_message_id
            $table->unsignedBigInteger('last_read_message_id')->nullable();

            $table->integer('sort')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            // Уникальный составной индекс
            $table->unique(['chat_id', 'user_id']);

            // Индекс для быстрого поиска всех чатов конкретного пользователя
            $table->index(['user_id', 'chat_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ms_chat_user');
    }
};
