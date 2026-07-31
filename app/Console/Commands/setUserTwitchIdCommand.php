<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\AdditionalFieldsService;
use App\Services\TwitchService;
use Illuminate\Console\Command;

class setUserTwitchIdCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'setUserTwitchId';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Команда заполняет ';

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
        $users = User::query()->get();

        $clientId = config('twitch.client_id');
        $clientSecret = config('twitch.client_secret');

        $twitchService = new TwitchService();
        $token = $twitchService->getAccessToken($clientId, $clientSecret);

        foreach ($users as $user) {
            foreach ($user->additionalFields as $field) {
                if ($field->slug === 'twitch_channel') {

                    $fieldsArray = $user->additionalFields->toArray();

                    $result = array_filter($fieldsArray, function($item) {
                        return isset($item['slug']) && $item['slug'] === 'twitch_id';
                    });

                    if (!$result) {
                        $path = parse_url($field->value, PHP_URL_PATH);
                        $twitchName = basename($path);

                        $twitchUserData = $twitchService->getTwitchUserData($twitchName, $clientId, $token);

                        if (isset($twitchUserData['data'][0])) {
                            $fieldsArray[] = [
                                'name' => 'Twitch ID',
                                'slug' => 'twitch_id',
                                'value' => $twitchUserData['data'][0]['id'],
                                'sort' => 90,
                            ];

                            $additionalFieldsService = new AdditionalFieldsService();
                            $additionalFieldsService->sync($user, $fieldsArray);
                        }
                    }
                }
            }
        }
    }
}
