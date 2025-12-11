<?php
namespace App\Http\Controllers\Admin\Games;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\GamingPlatform;
use App\Models\Genre;
use App\Models\Group;
use App\Services\CompanyService;
use App\Services\ErrorService;
use App\Services\GameService;
use App\Services\GamingPlatformService;
use App\Services\GenreService;
use App\Services\MediaService;
use App\Services\Speedruncom\SpeedrunApiService;
use App\Traits\HasDateParsing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class GamesApiController extends Controller {

    use HasDateParsing;

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
                            'date' => $result["release-date"],
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

                // Заполняем дополнительные поля
                $gameFields['additional_fields'] = [];

                if ($result["data"]["names"]["japanese"]) {
                    $gameFields['additional_fields'][] = [
                        'name' => 'Название на Японском',
                        'slug' => 'name_on_japanese',
                        'sort' => 50,
                        'value' => $result["data"]["names"]["japanese"]
                    ];
                }

                if ($result["data"]["names"]["twitch"]) {
                    $gameFields['additional_fields'][] = [
                        'name' => 'Название на twitch',
                        'slug' => 'name_on_twitch',
                        'sort' => 51,
                        'value' => $result["data"]["names"]["twitch"]
                    ];
                }

                // Заполняем платформу в связке с датой выхода
                $arGamingPlatformsIds = [];

                if ($result["data"]["platforms"] && $result["data"]["release-date"]) {
                    foreach ($result["data"]["platforms"] as $platform) {
                        $platformData = $speedrunApiService->getPlatform($platform);
                        $platformId = GamingPlatform::where('name', $platformData['data']['name'])->orWhere('spc_id', $platformData['data']['id'])->value('id');

                        if ($platformId) {
                            $arGamingPlatformsIds[] = $platformId;
                        } else {
                            $platformFields = [
                                'name' => $platformData['data']['name'],
                                'slug' => Str::slug($platformData['data']['name'], '-', 'ru'),
                                'spc_id' => $platformData['data']['id'],
                            ];

                            if ($platformData['data']['released'] ?? null) {
                                $platformFields['release_dates'][] = [
                                    'date' => $data['birth_date'] = $this->parseDateString($platformData['data']['released'])?->format('Y-m-d'),
                            ];
                        }

                            $gamingPlatformService = new GamingPlatformService();

                            $newPlatform = $gamingPlatformService->add($platformFields);

                            $arGamingPlatformsIds[] = $newPlatform->id;
                        }
                    }

                    if ($arGamingPlatformsIds) {
                        foreach ($arGamingPlatformsIds as $platformId) {
                            $gameFields['release_dates'][] = [
                                'gaming_platform' => $platformId,
                                'date' => $this->parseDateString($result["data"]["release-date"])?->format('Y-m-d'),
                                'addInfo' => null,
                            ];
                        }
                    }
                }

                // Заполняем ссылки
                $gameFields['links'] = [];

                if ($result["data"]["weblink"]) {
                    $gameFields['links'][] = [
                        'name' => 'speedrun.com',
                        'url' => $result["data"]["weblink"]
                    ];
                }

                // Заполняем жанры
                $gameFields['genres'] = [];

                if ($result["data"]["genres"]) {

                    foreach ($result["data"]["genres"] as $genreSpcId) {
                        $genre = $speedrunApiService->getGenres($genreSpcId);

                        $genreId = Genre::where('name', $genre['data']['name'])->orWhere('spc_id', $genre['data']['id'])->value('id');

                        if ($genreId) {
                            $gameFields['genres'][] = [
                                'genre' => $genreId,
                            ];
                        } else {
                            $genreFields = [
                                'name' => $genre['data']['name'],
                                'slug' => Str::slug($genre['data']['name'], '-', 'ru'),
                                'spc_id' => $genre['data']['id'],
                            ];

                            $genreService = new GenreService();

                            $newGenre = $genreService->add($genreFields);

                            $gameFields['genres'][] = [
                                'genre' => $newGenre->id,
                            ];
                        }
                    }
                }

                // Заполняем издателя и разработчика
                $gameFields['companies'] = [];

                if ($result["data"]["developers"]) {
                    foreach ($result["data"]["developers"] as $developerSpcId) {
                        $developer = $speedrunApiService->getData($developerSpcId, 'developers');

                        $companyId = Company::where('name', $developer['data']['name'])->orWhere('spc_id', $developer['data']['id'])->value('id');

                        if ($companyId) {
                            $gameFields['companies'][] = [
                                'additional_info' => '',
                                'company' => $companyId,
                                'company_role' => Group::findBySlug('razrabotchik')->value('id'),
                            ];
                        } else {
                            $companyFields = [
                                'name' => $developer['data']['name'],
                                'slug' => Str::slug($developer['data']['name'], '-', 'ru'),
                                'spc_id' => $developer['data']['id'],
                            ];

                            $companyService = new CompanyService();

                            $newCompany = $companyService->add($companyFields);

                            $gameFields['companies'][] = [
                                'additional_info' => '',
                                'company' => $newCompany->id,
                                'company_role' => Group::findBySlug('razrabotchik')->value('id'),
                            ];
                        }
                    }
                }

                if ($result["data"]["publishers"]) {
                    foreach ($result["data"]["publishers"] as $publisherSpcId) {
                        $publisher = $speedrunApiService->getData($publisherSpcId, 'publishers');

                        $companyId = Company::where('name', $publisher['data']['name'])->orWhere('spc_id', $publisher['data']['id'])->value('id');

                        if ($companyId) {
                            $gameFields['companies'][] = [
                                'additional_info' => '',
                                'company' => $companyId,
                                'company_role' => Group::findBySlug('izdatel')->value('id'),
                            ];
                        } else {
                            $companyFields = [
                                'name' => $publisher['data']['name'],
                                'slug' => Str::slug($publisher['data']['name'], '-', 'ru'),
                                'spc_id' => $publisher['data']['id'],
                            ];

                            $companyService = new CompanyService();

                            $newCompany = $companyService->add($companyFields);

                            $gameFields['companies'][] = [
                                'additional_info' => '',
                                'company' => $newCompany->id,
                                'company_role' => Group::findBySlug('izdatel')->value('id'),
                            ];
                        }
                    }
                }

                // Движок игры
                if ($result["data"]["engines"]) {
                    foreach ($result["data"]["engines"] as $engineSpcId) {
                        $engine = $speedrunApiService->getData($engineSpcId, 'engines');

                        $gameFields['additional_fields'][] = [
                            'name' => 'Движок',
                            'slug' => 'engine',
                            'sort' => 40,
                            'value' => $engine["data"]["name"]
                        ];
                    }
                }

                // Регионы
                if ($result["data"]["regions"]) {
                    $regionList = [];

                    foreach ($result["data"]["regions"] as $regionSpcId) {
                        $region = $speedrunApiService->getData($regionSpcId, 'regions');

                        $regionList[] = $region["data"]["name"];
                    }

                    $gameFields['additional_fields'][] = [
                        'name' => 'Регионы',
                        'slug' => 'regions',
                        'sort' => 30,
                        'value' => implode(', ', $regionList),
                    ];
                }

                /*
                $url = $result["data"]["assets"]["cover-large"]["uri"];

                $cdn = trim(shell_exec('node resolveSpeedrunCdn.js "' . escapeshellarg($url) . '"'));

                $imageContent = file_get_contents($cdn);

                dd($imageContent);

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
                */

//            dd($gameFields);
                return $gameService->addGame($gameFields);
        }
    }
}
