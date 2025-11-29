<?php
namespace App\Http\Controllers\Admin\Games;

use App\Http\Controllers\Controller;
use App\Models\GamingPlatform;
use App\Services\ErrorService;
use App\Services\GameService;
use App\Services\MediaService;
use App\Services\Speedruncom\SpeedrunApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class GamesApiController extends Controller {
    public function search(Request $request)
    {
        // TODO переделать под Provider?

        switch ($request->apiName) {
            case 'speedrun.com':
                $speedrunApiService = new SpeedrunApiService();

                $arForReturn = [];

                $results = $speedrunApiService->search($request->search, $request->offset);

                if (isset($results['data']) && $results['data']) {
                    foreach ($results['data'] as $result) {

                        $arForReturn['list'][] = [
                            'id' => $result["id"],
                            'name' => $result["names"]["international"],
                        ];
                    }
                }

                $arForReturn['pagination'] = $results['pagination'];

                return $arForReturn;
        }
    }

    public function add(GameService $gameService, Request $request)
    {
        if (!$request->id) {
            return ErrorService::message('ID не получен');
        }

        // TODO переделать под Provider?

        switch ($request->apiName) {
            case 'speedrun.com':
                $speedrunApiService = new SpeedrunApiService();

                $result = $speedrunApiService->getGame($request->id);

                if (!$result) {
                    return ErrorService::message('API не вернул данных об игре');
                }

                $user = Auth::user();

                $gameFields = [
                    'name' => $result["data"]["names"]["international"],
                    'slug' => Str::slug($result["data"]["names"]["international"], '-', 'ru'),
                    'active' => true,
                    'show_in_list' => true,
                    'created_by' => $user->id,
                ];

                $platformData = [];

                foreach ($result["data"]["platforms"] as $platform) {
                    $platformData = $speedrunApiService->getPlatform($platform);
                    $platformId = GamingPlatform::where('name', $platformData['data']['name'])->value('id');

                    if ($platformId) {
                        $platformData[] = $platformId;
                    } else {
                        $platformFields = [
                            'name' => $platformData['data']['name'],
                            'slug' => Str::slug($platformData['data']['name'], '-', 'ru'),
                        ];

//                        $newPlatform = GamingPlatform::create($platformFields);
//                        $platformData[] = $newPlatform->id;
                    }
                }

                // Добавляем Cover
                if ($result["data"]["assets"]["cover-large"]["uri"] ?? null) {
                    $response = Http::get($result["data"]["assets"]["cover-large"]["uri"]);
                    $imageContent = $response->body();

                    // Создаем временный файл
                    $tempFile = tempnam(sys_get_temp_dir(), 'downloaded_image');
                    file_put_contents($tempFile, $imageContent);

                    // Создаем объект UploadedFile
                    $uploadedFile = new \Illuminate\Http\UploadedFile(
                        $tempFile,
                        $gameFields['slug'] . '_cover',
                        $response->header('Content-Type', 'image/jpeg'),
                        null,
                        true
                    );

                    $fileArray = [
                        'name' => $result["data"]["names"]["international"] . ' cover',
                        'src' => $uploadedFile,
                    ];

                    $mediaService = new MediaService();

                    if ($cover = $mediaService->addMedia($fileArray, $user)) {
                        $gameFields['covers'][] = $cover;
                    }
                }

//                dd($platformData);

                return $gameService->addGame($gameFields);

//                $arForReturn = [];
//
//                $results = $speedrunApiService->search($request->search, $request->offset);
//
//                if (isset($results['data']) && $results['data']) {
//                    foreach ($results['data'] as $result) {
//
//                        $arForReturn['list'][] = [
//                            'id' => $result["id"],
//                            'name' => $result["names"]["international"],
//                        ];
//                    }
//                }
//
//                $arForReturn['pagination'] = $results['pagination'];
//
//                return $arForReturn;
//                    return $results;
        }
    }
}
