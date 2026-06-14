<?php

namespace App\Console\Commands\User;

use App\Models\User;
use Illuminate\Console\Command;

class FillPublicNameCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:fill-public-name-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::all();

        foreach ($users as $user) {
            if (blank($user->public_name)) {
                $user->public_name = $user->name;
                $user->saveQuietly();

                $this->line('Поле public_name заполнено, его значение: ' . $user->public_name);
            }
        }
    }
}
