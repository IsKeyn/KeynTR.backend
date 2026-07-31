<?php

namespace App\Http\Requests\Message;

use Illuminate\Foundation\Http\FormRequest;

class SendMessageRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check();
    }

    public function rules()
    {
        return [
            'message'           => 'required|string|max:4000',
            'chat_id'           => 'nullable|integer|exists:ms_chats,id',
            'recipient_user_id' => 'nullable|integer|exists:users,id',
            'reply_to_id'       => 'nullable|integer|exists:ms_messages,id',
            'type'              => 'in:text,image,file',
        ];
    }

    public function messages()
    {
        return [
            'message.required' => 'Поле "Сообщение" обязательно для заполнения',
        ];
    }
}
