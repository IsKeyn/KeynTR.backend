<?php

namespace App\Services\Cache;

use App\Models\Comments;
use Illuminate\Support\Facades\Cache;

class CommentCacheService
{
    public const LIST_PREFIX = 'comment_list_cache';
    public const TIME = 6 * 30 * 24 * 60 * 60;

    public function clearAllCache()
    {

    }

    public function clearListCacheByEntity($type, $id, $showMessage = false)
    {
        $perPageArray = [10];

        foreach ($perPageArray as $perPage) {
            $lastPage = Comments::query()
                ->active()
                ->paginate($perPage)->lastPage();

            for ($i = 1; $i <= $lastPage; $i++) {
                $cacheKey = self::LIST_PREFIX . '_' . $type . '_' . $id . '_' . $i . '_' . $perPage;

                Cache::forget($cacheKey);

                if ($showMessage) {
                    echo $cacheKey . ' очищен' . PHP_EOL;
                }
            }
        }
    }
}
