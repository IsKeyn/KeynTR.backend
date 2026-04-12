<?php
namespace App\Http\Controllers\Admin\Games;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Game;
use App\Models\GamingPlatform;
use App\Models\Genre;
use App\Models\Group;
use App\Models\Series;
use App\Services\CompanyService;
use App\Services\ErrorService;
use App\Services\GameService;
use App\Services\GamingPlatformService;
use App\Services\GenreService;
use App\Services\MediaService;
use App\Services\SeriesService;
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
                            'japanese_name' => isset($result["names"]["japanese"]) ? $result["names"]["japanese"] : null,
                            'twitch_name' => isset($result["names"]["twitch"]) ? $result["names"]["twitch"] : null,
                            'date' => $result["release-date"],
                            'weblink' => $result["weblink"],
                            'romhack' => $result["romhack"],

                            'cover' => isset($result['assets']['cover-large']['uri']) ? $result['assets']['cover-large']['uri'] : null,
                            'background' => isset($result['assets']['background']['uri']) ? $result['assets']['background']['uri'] : null,
                            'foreground' => isset($result['assets']['foreground']['uri']) ? $result['assets']['foreground']['uri'] : null,

                            'gametypes' => isset($result['gametypes']) ? $result['gametypes'] : null,
                            'platforms' => isset($result['platforms']) ? $result['platforms'] : null,
                            'regions' => isset($result['regions']) ? $result['regions'] : null,
                            'genres' => isset($result['genres']) ? $result['genres'] : null,
                            'engines' => isset($result['engines']) ? $result['engines'] : null,
                            'developers' => isset($result['developers']) ? $result['developers'] : null,
                            'publishers' => isset($result['publishers']) ? $result['publishers'] : null,
                        ];
                    }
                }

                $arForReturn['pagination'] = $results['pagination'];

                return $arForReturn;
        }
    }

    public function check(Request $request)
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

                $slug = Str::slug($result["data"]["names"]["international"], '-', 'ru');

                $game = Game::where(function ($query) use ($slug, $request) {
                    $query
                        ->where('slug', $slug)
                        ->orWhere('spc_id', $request->id);
                })->first();

                return $game;
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
                    'active' => false,
                    'mod' => $result["data"]["romhack"],
                    'show_in_list' => true,
                    'spc_id' => $request->id,
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
                        $slug = Str::slug($platformData['data']['name'], '-', 'ru');

                        $platformId = GamingPlatform::where(function ($query) use ($platformData, $slug) {
                            $query
                                ->where('slug', $slug)
                                ->orWhere('spc_id', $platformData['data']['id']);
                        })->value('id');

                        if ($platformId) {
                            $arGamingPlatformsIds[] = $platformId;
                        } else {
                            $platformFields = [
                                'name' => $platformData['data']['name'],
                                'slug' => $slug,
                                'spc_id' => $platformData['data']['id'],
                            ];

                            if ($platformData['data']['released'] ?? null) {
                                $platformFields['release_dates'][] = [
                                    'date' => $this->parseDateString($platformData['data']['released'])?->format('Y-m-d'),
                                ];
                            }

                            $gamingPlatformService = new GamingPlatformService();

                            $newPlatform = $gamingPlatformService->add($platformFields);

                            if (is_array($newPlatform) && isset($newPlatform['error'])) {
                                return ErrorService::message($newPlatform['error']);
                            }

                            $arGamingPlatformsIds[] = $newPlatform->id;
                        }
                    }

                    if ($arGamingPlatformsIds) {
                        foreach ($arGamingPlatformsIds as $platformId) {
                            $date = null;
                            $hideDay = false;
                            $hideMonth = false;
                            $parsedData = $this->parseDateString($result["data"]["release-date"]);

                            if ($parsedData) {
                                $date = $parsedData?->format('Y-m-d');

                                if ($parsedData->day === 1 && $parsedData->month === 1) {
                                    $hideDay = true;
                                    $hideMonth = true;
                                }
                            }

                            $gameFields['release_dates'][] = [
                                'gaming_platform' => $platformId,
                                'date' => $date,
                                'addInfo' => null,
                                'hideDay' => $hideDay,
                                'hideMonth' => $hideMonth,
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
                        $slug = Str::slug($genre['data']['name'], '-', 'ru');

                        $genreId = Genre::where(function ($query) use ($genre, $slug) {
                            $query
                                ->where('slug', $slug)
                                ->orWhere('spc_id', $genre['data']['id']);
                        })->value('id');

                        if ($genreId) {
                            $gameFields['genres'][] = [
                                'genre' => $genreId,
                            ];
                        } else {
                            $genreFields = [
                                'name' => $genre['data']['name'],
                                'slug' => $slug,
                                'spc_id' => $genre['data']['id'],
                            ];

                            $genreService = new GenreService();

                            $newGenre = $genreService->add($genreFields);
                            if (is_array($newGenre) && isset($newGenre['error'])) {
                                return ErrorService::message($newGenre['error']);
                            }

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
                        $slug = Str::slug($developer['data']['name'], '-', 'ru');

                        $companyId = Company::where(function ($query) use ($developer, $slug) {
                            $query
                                ->where('slug', $slug)
                                ->orWhere('spc_id', $developer['data']['id']);
                        })->value('id');

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
                            if (is_array($newCompany) && isset($newCompany['error'])) {
                                return ErrorService::message($newCompany['error']);
                            }

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
                        $slug = Str::slug($publisher['data']['name'], '-', 'ru');

                        $companyId = Company::where(function ($query) use ($publisher, $slug) {
                            $query
                                ->where('slug', $slug)
                                ->orWhere('spc_id', $publisher['data']['id']);
                        })->value('id');

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
                            if (is_array($newCompany) && isset($newCompany['error'])) {
                                return ErrorService::message($newCompany['error']);
                            }

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

                // Серия
                if ($result["data"]["links"]) {
                    foreach ($result["data"]["links"] as $link) {
                        if ($link['rel'] === 'series') {

                            $options = [
                                'http' => [
                                    'method' => 'GET',
                                ],
                                'ssl' => [
                                    'verify_peer' => config('ssl.verify_peer'),
                                    'verify_peer_name' => config('ssl.verify_peer_name'),
                                ]
                            ];

                            $context = stream_context_create($options);
                            $response = file_get_contents($link['uri'], false, $context);

                            if ($response) {
                                $seriesData = json_decode($response, true);

                                $slug = Str::slug($seriesData['data']['names']['international'], '-', 'ru');

                                $seriesId = Series::where(function ($query) use ($seriesData, $slug) {
                                    $query
                                        ->where('slug', $slug)
                                        ->orWhere('spc_id', $seriesData['data']['id']);
                                })->value('id');

                                if ($seriesId) {
                                    $gameFields['series'][] = [
                                        'series' => $seriesId,
                                    ];
                                } else {
                                    $seriesFields = [
                                        'name' => $seriesData['data']['names']['international'],
                                        'slug' => $slug,
                                        'spc_id' => $seriesData['data']['id'],
                                    ];

                                    $seriesService = new SeriesService();

                                    $newSeries = $seriesService->add($seriesFields);
                                    if (is_array($newSeries) && isset($newSeries['error'])) {
                                        return ErrorService::message($newSeries['error']);
                                    }

                                    $gameFields['series'][] = [
                                        'series' => $newSeries->id,
                                    ];
                                }
                            }
                            break;
                        }
                    }
                }

                // Изображения
                // Обложка
                if ($originalUrl = $result["data"]["assets"]["cover-large"]["uri"]) {
                    if (!str_starts_with($originalUrl, 'http')) {
                        $originalUrl = 'https://www.speedrun.com' . $originalUrl;
                    }

                    // Прогоняем URL через бесплатный прокси-сервер картинок
                    // Формат: https://images.weserv.nl/?url=ОРИГИНАЛЬНЫЙ_URL
                    $proxyUrl = 'https://images.weserv.nl/?url=' . urlencode($originalUrl);

                    $imageResponse = Http::get($proxyUrl);

                    if ($imageResponse->successful() && str_contains($imageResponse->header('Content-Type'), 'image')) {
                        $tempFile = tempnam(sys_get_temp_dir(), 'sr_cover_');
                        file_put_contents($tempFile, $imageResponse->body());

                        $uploadedFile = new \Illuminate\Http\UploadedFile(
                            $tempFile,
                            $gameFields['slug'] . '_cover',
                            $imageResponse->header('Content-Type', 'image/jpeg'),
                            null,
                            true
                        );

                        $mediaService = new MediaService();

                        $fileArray = [
                            'name' => 'Обложка игры ' . $gameFields['name'],
                            'description' => 'Обложка игры ' . $gameFields['name'],
                            'src' => $uploadedFile,
                            'tags' => ['cover', 'коробка', 'обложка'],
                        ];

                        if ($cover = $mediaService->addMedia($fileArray, $user)) {
                            $gameFields['covers'] = [
                                [
                                    'id' => $cover->id,
                                ],
                            ];
                        }
                    }
                }

                // Титульное изображение
                if (isset($result["data"]["assets"]["background"]["uri"]) || isset($result["data"]["assets"]["foreground"]["uri"])) {
                    $originalUrl = null;

                    if (isset($result["data"]["assets"]["background"]["uri"]) && $result["data"]["assets"]["background"]["uri"]) {
                        $originalUrl = $result["data"]["assets"]["background"]["uri"];
                    } else {
                        if (isset($result["data"]["assets"]["foreground"]["uri"]) && $result["data"]["assets"]["foreground"]["uri"]) {
                            $originalUrl = $result["data"]["assets"]["foreground"]["uri"];
                        }
                    }

                    if ($originalUrl) {
                        if (!str_starts_with($originalUrl, 'http')) {
                            $originalUrl = 'https://www.speedrun.com' . $originalUrl;
                        }

                        // Прогоняем URL через бесплатный прокси-сервер картинок
                        // Формат: https://images.weserv.nl/?url=ОРИГИНАЛЬНЫЙ_URL
                        $proxyUrl = 'https://images.weserv.nl/?url=' . urlencode($originalUrl);

                        $imageResponse = Http::get($proxyUrl);

                        if ($imageResponse->successful() && str_contains($imageResponse->header('Content-Type'),
                                'image')) {
                            $tempFile = tempnam(sys_get_temp_dir(), 'sr_cover_');
                            file_put_contents($tempFile, $imageResponse->body());

                            $uploadedFile = new \Illuminate\Http\UploadedFile(
                                $tempFile,
                                $gameFields['slug'] . '_cover',
                                $imageResponse->header('Content-Type', 'image/jpeg'),
                                null,
                                true
                            );

                            $mediaService = new MediaService();

                            $fileArray = [
                                'name' => 'Титульное изображение игры ' . $gameFields['name'],
                                'description' => 'Титульное изображение игры ' . $gameFields['name'],
                                'src' => $uploadedFile,
                                'tags' => ['титульное изображение'],
                            ];

                            if ($media = $mediaService->addMedia($fileArray, $user)) {
                                $gameFields['title_image'] = $media->id;
                            }
                        }
                    }
                }

                return $gameService->addGame($gameFields);
        }
    }
}
