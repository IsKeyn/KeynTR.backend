<?php

namespace App\Observers;

use Illuminate\Support\Facades\Cache;

class ErrorObserver
{
    public function created() {
        Cache::forget('errors');
    }
}
