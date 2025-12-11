<?php
namespace App\Traits;

use Carbon\Carbon;

trait HasDateParsing
{
    protected function parseDateString($dateString)
    {
        if (empty($dateString)) {
            return null;
        }

        $dateString = trim($dateString);

        // Только год
        if (preg_match('/^\d{4}$/', $dateString)) {
            return Carbon::createFromDate((int)$dateString, 1, 1);
        }

        // Преобразуем в единый формат
        $normalized = str_replace('.', '/', $dateString);

        // Попытка автоматического парсинга
        try {
            return Carbon::parse($normalized);
        } catch (\Exception $e) {
            // Ручной парсинг для специфичных форматов
            $formats = [
                'd/m/Y', 'd/m/y', 'j/n/Y', 'j/n/y',
                'Y/m/d', 'Y-m-d', 'm/d/Y'
            ];

            foreach ($formats as $format) {
                try {
                    return Carbon::createFromFormat($format, $normalized);
                } catch (\Exception $e) {
                    continue;
                }
            }
        }

        return null;
    }
}
