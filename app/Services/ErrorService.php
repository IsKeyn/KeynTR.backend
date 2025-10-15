<?php

namespace App\Services;

class ErrorService
{
    public static function message($message): array
    {
        return ['error' => $message];
    }
}
