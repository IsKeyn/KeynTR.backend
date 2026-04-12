<?php

namespace App\Services\Game;

use Illuminate\Support\Carbon;

class FilterService
{
    const KEYS_FOR_RETURN = [
        'gamePlatform' => 'gamePlatforms',
        'company' => 'companies',
        'dates' => 'minMaxData',
        'bgGamesList' => 'events',
    ];

    public function get($data, $filterList = [])
    {
        $result = [];

        if ($filterList) {
            foreach ($data as $game) {
                foreach ($filterList as $filterKey) {
                    $name = $this->getNameByKey($filterKey);
                    foreach ($game->{$name} as $element) {
                        if ($name === 'dates') {
                            if (isset($result[$filterKey]['min'])) {
                                if ($result[$filterKey]['min'] > $element->date) {
                                    $result[$filterKey]['min'] = $element->date;
                                }
                            } else {
                                $result[$filterKey]['min'] = $element->date;
                            }

                            if (isset($result[$filterKey]['max'])) {
                                if ($result[$filterKey]['max'] < $element->date) {
                                    $result[$filterKey]['max'] = $element->date;
                                }
                            } else {
                                $result[$filterKey]['max'] = $element->date;
                            }
                        } else if ($name === 'bgGamesList') {
                            if ($element->relationLoaded('boardGame') && $element->boardGame && $element->boardGame->slug !== 'demo') {
                                $result[$filterKey][$element->boardGame->id] = [
                                    'id' => $element->boardGame->id,
                                    'name' => $element->boardGame->name,
                                    'sort' => isset($element->boardGame->sort) ? $element->boardGame->sort : null,
                                    'active' => true,
                                ];
                            }
                        } else {
                            if (!isset($result[$filterKey][$element->id])) {
                                $result[$filterKey][$element->id] = [
                                    'id' => $element->id,
                                    'name' => $element->name,
                                    'sort' => isset($element->sort) ? $element->sort : null,
                                    'active' => true,
                                ];
                            }
                        }
                    }
                }
            }

            foreach ($filterList as $filterKey) {
                $name = $this->getNameByKey($filterKey);

                if ($name === 'dates') {
                    foreach ($result[$filterKey] as &$value) {
                        $value = Carbon::parse($value)->year;
                    }
                } else if (isset($result[$filterKey])) {
                    usort($result[$filterKey], function($a, $b) {
                        // Приоритет: сначала те, у которых есть sort (не null)
                        $aPriority = isset($a['sort']) && $a['sort'] !== null ? 0 : 1;
                        $bPriority = isset($b['sort']) && $b['sort'] !== null ? 0 : 1;

                        // Сравниваем по приоритету
                        if ($aPriority != $bPriority) {
                            return $aPriority <=> $bPriority;
                        }

                        // Если приоритет одинаковый и у обоих есть sort, сравниваем по sort
                        if ($aPriority === 0) {
                            if ($a['sort'] != $b['sort']) {
                                return $a['sort'] <=> $b['sort'];
                            }
                        }

                        // Если все равны, сравниваем по имени
                        return strcmp($a['name'], $b['name']);
                    });
                }
            }
        }

        return $result;
    }

    private function getNameByKey($keyForReturn)
    {
        $name = array_search($keyForReturn, self::KEYS_FOR_RETURN);

        return $name ? $name : $keyForReturn;
    }

    private function getKeyForReturn($filterKey)
    {
        return self::KEYS_FOR_RETURN[$filterKey] ? self::KEYS_FOR_RETURN[$filterKey] : $filterKey;
    }

    public function compareFilters($firstFilter, $secondFilter)
    {
        foreach ($firstFilter as $filterKey => &$filter) {
            if ($filterKey === 'minMaxData') continue;

            if (!isset($secondFilter[$filterKey])) {
                $filter = array_map(function($item) {
                    $item['active'] = false;
                    return $item;
                }, $filter);
            } else {
                // Извлекаем ID из второго массива для быстрого поиска
                $secondIds = array_column($secondFilter[$filterKey], 'id');

                // Проходим по элементам первого массива
                foreach ($firstFilter[$filterKey] as &$filterElement) {
                    // Если ID элемента нет во втором массиве, устанавливаем active => false
                    if (!in_array($filterElement['id'], $secondIds)) {
                        $filterElement['active'] = false;
                    }
                }
            }
        }

        return $firstFilter;
    }
}
