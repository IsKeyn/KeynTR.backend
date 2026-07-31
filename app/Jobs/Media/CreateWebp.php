<?php

namespace App\Jobs\Media;

use App\Services\MediaService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateWebp implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $media;

    // Конструктор принимает данные (модели автоматически сериализуются)
    public function __construct($media)
    {
        $this->media = $media;
    }

    // Метод handle выполняется, когда воркер берет задачу из очереди
    public function handle()
    {
        // Здесь ваш тяжелый код
        $mediaService = app(MediaService::class);
        $mediaService->getWebp($this->media);
    }

    // Количество попыток выполнения при ошибках
    public $tries = 3;

    // Задержка между попытками в секундах
    public $backoff = [10, 60, 300];
}
