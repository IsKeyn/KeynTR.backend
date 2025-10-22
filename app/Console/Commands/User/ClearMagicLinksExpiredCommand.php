<?php

namespace App\Console\Commands\User;

use App\Models\User\MagicLink;
use App\Services\User\MagicLinkService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class ClearMagicLinksExpiredCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:clear-magic-links';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Команда удаляет истекшие записи для автологина, а также QR коды';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->line('НАЧАЛО удаления истекших ссылок');

        $magicLinks = MagicLink::query()->where('expires_at', '<', Carbon::now())->get();

        $count = 0;

        foreach ($magicLinks as $magicLink) {
            // Удаляем QR код
            MagicLinkService::deleteQrFile(basename($magicLink->qr_code));

            // Удаляем токен, чтобы нельзя было использовать повторно
            $magicLink->delete();

            $count++;
        }

        $this->line('КОНЕЦ удалено истекших ссылок ' . $count);
        return Command::SUCCESS;
    }
}
