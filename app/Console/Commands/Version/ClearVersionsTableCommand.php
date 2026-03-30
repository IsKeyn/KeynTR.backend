<?php

namespace App\Console\Commands\Version;

use App\Models\Version;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class ClearVersionsTableCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'version:clear-versions {days}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Комманда очищает версии, созданые больше определенного периода';

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
        $this->line('НАЧАЛО работы комманды очистки версий');

        $days = $this->argument('days');

        $this->line('Будут очищены записи старше ' . $days . ' дней');

        $versions = Version::query()
            ->whereDate('created_at', '<', Carbon::now()->subDays($days))
            ->get();

        foreach ($versions as $version) {
            $this->line('Удаление записи с ID ' . $version->id . ' от ' . $version->created_at);
            $version->delete();
        }

        $this->line('КОНЕЦ работы комманды очистки версий');
        return 0;
    }
}
