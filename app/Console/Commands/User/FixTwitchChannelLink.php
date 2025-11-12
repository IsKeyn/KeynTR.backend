<?php

namespace App\Console\Commands\User;

use App\Models\AdditionalField;
use Illuminate\Console\Command;

class FixTwitchChannelLink extends Command
{
    protected $signature = 'user:fix-twitch-channel-link';
    protected $description = 'Исправление ссылки на твич';

    public function handle()
    {
        $additionalFields = AdditionalField::query()->where('slug', 'twitch_channel')->get();

        foreach ($additionalFields as $additionalField) {
            $additionalField->value = str_replace('https://www.twitch.tv/', '', $additionalField->value);
            $additionalField->save();

            $this->line('Поле проверено, результат: ' . $additionalField->value);
        }
    }
}
