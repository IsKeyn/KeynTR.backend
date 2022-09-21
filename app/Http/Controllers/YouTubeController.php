<?php

namespace App\Http\Controllers;

ini_set('max_execution_time', 180);

use App\Http\Resources\YouTubeResource;
use App\Models\YouTubeApi;
use App\Models\YoutubeChannel;
use App\Models\YoutubePlaylist;
use App\Models\YoutubeVideo;
use DateTime;
use Google_Client;
use Illuminate\Http\Request;

// TODO разница между this и self

class YouTubeController extends Controller
{
    public const APP_NAME = 'KeynTR';
    public const API_URL = 'https://youtube.googleapis.com/youtube/v3/';
    public const CHANNEL_ID = 'UCOGatAJNTBG0HIHDLsM7xGw';
    public const API_KEY = 'AIzaSyA0_9fFr2ho7faSbNqLt_nM4kMgx3KH9js';

    private const YOU_TUBE_DATE_FORMAT = 'Y-m-d?H:i:s?';
    private const DB_FORMAT = 'Y-m-d H:i:s';

    public $playList = [];
    public $videos = [];

    public function getDataFromYouTube() {
        if (!$this->getYouTubeChannelInfo())
            return redirect()->route('googleOAuth2');

        //$this->getPlayLists();
        //$this->getVideosFormPlayList();
    }

    /* Метод получает access_token от google и записывает его в БД, необходима авторизация со стороны пользователя */
    public function googleOAuth2() {
        $client = new Google_Client();
        $client->setAuthConfig(substr($_SERVER['DOCUMENT_ROOT'], 0, -6) . 'resources\json\google_client_secret.json');
        $client->setRedirectUri('http://' . $_SERVER['HTTP_HOST'] . '/get_google_access_token/');
        $client->setScopes([
            'https://www.googleapis.com/auth/youtube',
            'https://www.googleapis.com/auth/youtube.force-ssl',
            'https://www.googleapis.com/auth/youtube.readonly',
            'https://www.googleapis.com/auth/youtubepartner',
            'https://www.googleapis.com/auth/youtubepartner-channel-audit'
        ]);
        $client->setApprovalPrompt('force');
        $client->setAccessType("offline");

        // Проверяем получен ли код, редиректом от google
        if (!isset($_GET['code'])) {
            // Если нет, отправляем пользователя на авторизацию
            $authUrl = $client->createAuthUrl();
            return redirect(filter_var($authUrl, FILTER_SANITIZE_URL));

        } else {
            $accessToken = $client->fetchAccessTokenWithAuthCode($_GET['code']);

            if (isset($accessToken['access_token'])) {
                $token = YouTubeApi::query()->where('app_name', '=', self::APP_NAME)->first();

                $fields = [
                    'app_name' => self::APP_NAME,
                    'access_token' => json_encode($accessToken)
                ];

                $token ? YouTubeApi::where('app_name', '=', self::APP_NAME)->update($fields) : YouTubeApi::create($fields);

                return redirect()->route('getDataFromYouTube');
            } else {
                var_dump('access_token не получен');
            }
        }
    }

    private function refreshGoogleAccessToken($refreshToken) {
        $client = new Google_Client();
        $client->setAuthConfig(substr($_SERVER['DOCUMENT_ROOT'], 0, -6) . 'resources\json\google_client_secret.json');
        $client->setScopes([
            'https://www.googleapis.com/auth/youtube',
            'https://www.googleapis.com/auth/youtube.force-ssl',
            'https://www.googleapis.com/auth/youtube.readonly',
            'https://www.googleapis.com/auth/youtubepartner',
            'https://www.googleapis.com/auth/youtubepartner-channel-audit'
        ]);
        $client->setAccessType("offline");

        $client->refreshToken($refreshToken);
        $accessToken = $client->getAccessToken();

        $fields = [
            'app_name' => self::APP_NAME,
            'access_token' => json_encode($accessToken)
        ];

        YouTubeApi::where('app_name', '=', self::APP_NAME)->update($fields);

        return $accessToken;
    }

    private function sendRequestByUsingCurl($requestUrl, $token) {
        if ($curl = curl_init()) {
            curl_setopt($curl, CURLOPT_URL, $requestUrl);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl,CURLOPT_SSL_VERIFYHOST, false);

            curl_setopt(
                $curl,
                CURLOPT_HTTPHEADER,
                [
                    'Authorization: Bearer ' . $token,
                    'Accept: application/json'
                ]
            );

            $out = curl_exec($curl);

            curl_close($curl);

            return json_decode($out);
        }
    }

    private function requestUrlCreator($requestType, $params) {

        /*
            params example
            $params = array(
                'channelId' => self::CHANNEL_ID,
                'order' => 'date',
                'part' => 'snippet',
                'type' => 'playlist',
                'maxResults' => 10,
                'key' => self::API_KEY
            );
        */

        $requestUrl = self::API_URL . $requestType . '?';

        foreach ($params as $key => $param)
            $requestUrl .= $key . '=' . $param . '&';

        return $requestUrl;
    }

    public function getLastVideosFromApi($nextPageToken = null) {
        $requestUrl = $this->requestUrlCreator(
            'search',
            [
                'channelId' => self::CHANNEL_ID,
                'order' => 'date',
                'part' => 'snippet',
                'type' => 'video',
                'maxResults' => 10,
                'key' => self::API_KEY
            ]
        );

        if ($nextPageToken)
            $requestUrl .= 'pageToken=' . $nextPageToken . '&';

        $response = json_decode(file_get_contents($requestUrl));

        foreach ($response->items as $video) {
            $preparedArray[] = [
                'video_id' => $video->id->videoId,
                'published_at' => DateTime::createFromFormat(self::YOU_TUBE_DATE_FORMAT, $video->snippet->publishedAt)->format(self::DB_FORMAT),
                'title' => $video->snippet->title,
                'thumbnails' => json_encode($video->snippet->thumbnails),
                'status' => 'public',
                'channel_id' => self::CHANNEL_ID,
            ];
        }

        YoutubeVideo::query()->upsert(
            $preparedArray,
            'video_id',
            ['published_at', 'title', 'thumbnails', 'status', 'channel_id']
        );
    }

    public function getYouTubeChannelInfo() {
        $token = YouTubeApi::query()->where('app_name', '=', self::APP_NAME)->first();

        if ($token) {
            $tokenData = json_decode($token->access_token); // TODO в ларавеле есть авто перевекдение в JSON и обратно, сделать

            if (($tokenData->created - time()) <= -3600) {
                $tokenData = $this->refreshGoogleAccessToken($tokenData->refresh_token);
                $accessToken = $tokenData['access_token'];
            } else {
                $accessToken =  $tokenData->access_token;
            }

            $requestUrl = self::API_URL
                . 'channels?'
                . 'part=auditDetails,brandingSettings,contentDetails,contentOwnerDetails,id,localizations,snippet,statistics,status,topicDetails&'
                . 'key=' . self::API_KEY . '&'
                . 'id=' . self::CHANNEL_ID . '&';

            $result = $this->sendRequestByUsingCurl($requestUrl, $accessToken)->items[0];

            $preparedArray[] = [
                'channel_id' => $result->id,
                'published_at' => DateTime::createFromFormat(self::YOU_TUBE_DATE_FORMAT, $result->snippet->publishedAt)->format(self::DB_FORMAT),
                'title' => $result->snippet->title,
                'description' => $result->snippet->description,
                'custom_url' => $result->snippet->customUrl,
                'thumbnails' => json_encode($result->snippet->thumbnails),
                'view_count' => $result->statistics->viewCount,
                'subscriber_count' => $result->statistics->subscriberCount,
                'video_count' => $result->statistics->videoCount,
                'hidden_subscriber_count' => $result->statistics->hiddenSubscriberCount,
                'keywords' => $result->brandingSettings->channel->keywords,
                'unsubscribed_trailer' => $result->brandingSettings->channel->unsubscribedTrailer,
                'banner_external_url' => $result->brandingSettings->image->bannerExternalUrl
            ];

            YoutubeChannel::query()->upsert(
                $preparedArray,
                'channel_id',
                [
                    'published_at',
                    'title',
                    'description',
                    'custom_url',
                    'thumbnails',
                    'view_count',
                    'subscriber_count',
                    'video_count',
                    'hidden_subscriber_count',
                    'keywords',
                    'unsubscribed_trailer',
                    'banner_external_url'
                ]
            );

            return true;
        } else {
            return false;
        }
    }

    private function getPlayLists($nextPageToken = null) {
        $requestUrl = self::API_URL
        . 'playlists?'
        . 'part=contentDetails,snippet,player&'
        . 'maxResults=50&'
        . 'key=' . self::API_KEY . '&'
        . 'channelId=' . self::CHANNEL_ID . '&';

        if ($nextPageToken)
            $requestUrl .= 'pageToken=' . $nextPageToken . '&';

        $response = json_decode(file_get_contents($requestUrl));

        self::setPlayListArray($response);

        if (property_exists($response, 'nextPageToken')) {
            $this->getPlayLists($response->nextPageToken);
        } else {
            $preparedArray = array();

            foreach ($this->playList as $playListId => $playList) {
                $preparedArray[] = [
                    'playlist_id'           => $playListId,
                    'published_at'          => DateTime::createFromFormat(self::YOU_TUBE_DATE_FORMAT, $playList['publishedAt'])->format(self::DB_FORMAT),
                    'title'                 => $playList['title'],
                    'description'           => $playList['description'],
                    'thumbnails'            => $playList['thumbnails'],
                    'video_count'           => $playList['videoCount'],
                    'player'                => $playList['player'],
                    'channel_id'            => $playList['channelId'],
                ];
            }

            YoutubePlaylist::query()->upsert(
                $preparedArray,
                'playlist_id',
                ['published_at', 'title', 'description', 'thumbnails', 'video_count', 'player']
            );

            $this->deleteDeletedPlaylists();
        }
    }

    private function setPlayListArray($response) {
        foreach ($response->items as $item) {
            if (!array_key_exists($item->id, $this->playList)) {
                $this->playList[$item->id] = array(
                    'publishedAt' => $item->snippet->publishedAt,
                    'title' => $item->snippet->title,
                    'description' => $item->snippet->description,
                    'thumbnails' => json_encode($item->snippet->thumbnails),
                    'videoCount' => $item->contentDetails->itemCount,
                    'player' => $item->player->embedHtml,
                    'channelId' => $item->snippet->channelId,
                );
            }
        }
    }

    private function deleteDeletedPlaylists() {
        $playLists = YoutubePlaylist::all('playlist_id')->toArray();

        $arPlayListIds = [];

        foreach ($playLists as $playList)
            $arPlayListIds[] = $playList['playlist_id'];


        foreach ($this->playList as $playListId => $playList) {
            $id = array_search($playListId, $arPlayListIds);

            if (isset($id))
                unset($arPlayListIds[$id]);
        }

        if ($arPlayListIds) {
            $where = [];

            foreach ($arPlayListIds as $id) {
                $where[] = array(
                    'playlist_id',
                    '=',
                    $id
                );
            }

            YoutubePlaylist::where($where)->delete();
        }
    }

    public function getVideosFormPlayList() {

        $playLists = YoutubePlaylist::all('playlist_id')->toArray();

        foreach ($playLists as $playList)
            $this->getPlaylistVideosIds($playList['playlist_id']);

        $preparedArray = array();

        foreach ($this->videos as $videoId => $video) {
            $preparedArray[] = [
                'video_id'              => $videoId,
                'published_at'          => DateTime::createFromFormat(self::YOU_TUBE_DATE_FORMAT, $video['publishedAt'])->format(self::DB_FORMAT),
                'title'                 => $video['title'],
                'description'           => $video['description'],
                'thumbnails'            => $video['thumbnails'],
                'playlist_id'           => $video['playlistId'],
                'position_in_playlist'  => $video['positionInPlaylist'],
                'status'                => $video['status'],
                'channel_id'            => $video['channelId'],
            ];
        }

        YoutubeVideo::query()->upsert(
            $preparedArray,
            'video_id',
            ['published_at', 'title', 'description', 'playlist_id', 'position_in_playlist', 'status', 'channel_id']
        );

        $this->deleteDeletedVideos();
    }

    public function getPlaylistVideosIds($playlistId, $nextPageToken = null) {
        $requestUrl = self::API_URL
        . 'playlistItems?'
        . 'part=contentDetails,id,snippet,status&'
        . 'maxResults=50&'
        . 'playlistId=' . $playlistId . '&'
        . 'key=' . self::API_KEY . '&';

        if ($nextPageToken)
            $requestUrl .= 'pageToken=' . $nextPageToken . '&';

        $response = json_decode(file_get_contents($requestUrl));

        self::setVideoListArray($response);

        if (property_exists($response, 'nextPageToken')) {
            $this->getPlaylistVideosIds($playlistId, $response->nextPageToken);
        }
    }

    private function setVideoListArray($response) {
        foreach ($response->items as $item) {
            if (!array_key_exists($item->snippet->resourceId->videoId, $this->videos)) {
                $this->videos[$item->snippet->resourceId->videoId] = array(
                    'publishedAt' => $item->snippet->publishedAt,
                    'title' => $item->snippet->title,
                    'description' => $item->snippet->description,
                    'thumbnails' => json_encode($item->snippet->thumbnails),
                    'playlistId' => $item->snippet->playlistId,
                    'positionInPlaylist' => $item->snippet->position,
                    'status' => $item->status->privacyStatus,
                    'channelId' => $item->snippet->channelId,
                );
            }
        }
    }

    private function deleteDeletedVideos() {
        $videos = YoutubeVideo::all('video_id')->toArray();

        $arVideosIds = [];

        foreach ($videos as $video)
            $arVideosIds[] = $video['video_id'];

        foreach ($this->videos as $videoId => $video) {
            $id = array_search($videoId, $arVideosIds);

            if (isset($id))
                unset($arVideosIds[$id]);
        }

        if ($arVideosIds) {
            $where = [];

            foreach ($arVideosIds as $id) {
                $where[] = array(
                    'video_id',
                    '=',
                    $id
                );
            }

            YoutubeVideo::where($where)->delete();
        }
    }

    /* Функции вывода данных из БД */
    public function getLastVideos(Request $request) {
        $lastVideo = YoutubeVideo::query()->where('status', 'public')->orderBy('published_at', 'desc')->take(5)->get();


        $returnData = array();

        foreach ($lastVideo as $video) {
            $returnData[] = YouTubeResource::make($video);
        }

        return $returnData;
    }
}
