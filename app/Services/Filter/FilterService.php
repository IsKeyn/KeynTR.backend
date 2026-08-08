<?php

namespace App\Services\Filter;

use Illuminate\Support\Carbon;

class FilterService
{
    /**
     * Ключ - название из запроса к api
     */
    const KEYS_FOR_RETURN = [
        'gamePlatforms' => 'gamePlatform',
        'companies' => 'company',
        'minMaxData' => 'dates',
        'events' => 'bgGamesList',
        'addedBy' => 'bgGamesList',
    ];

    /**
     * @param $data
     * @param array $filterList Список фильтров, приходящих из запроса к api
     * @return array|mixed
     */
    public function get($data, $filterList = [])
    {
        $result = [];

        if ($filterList) {
            foreach ($data as $game) {
                foreach ($filterList as $filterKey) {
                    $name = $this->getNameByValue($filterKey);

                    if ($game->{$name} instanceof \Illuminate\Support\Collection) {
                        foreach ($game->{$name} as $element) {
                            $result = $this->elementHandler($element, $name, $filterKey, $result);
                        }
                    } else {
                        $result = $this->elementHandler($game->{$name}, $name, $filterKey, $result);
                    }
                }
            }

            foreach ($filterList as $filterKey) {
                $name = $this->getNameByValue($filterKey);

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

    /**
     * Обработка одного элемента
     *
     * @param Mixed $element Один элемен сущности, например коллекция
     * @param String $name Наименование ключа\зависимости
     * @param String $filterKey Наименование ключа, возвращаемых данных
     * @param $result
     * @return mixed
     */
    private function elementHandler($element, $name, $filterKey, $result)
    {
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
            if (
                $element->relationLoaded('boardGame')
                && $element->boardGame
                && !$element->boardGame->is_test
                && $filterKey === 'events'
            ) {
                $result[$filterKey][$element->boardGame->id] = [
                    'id' => $element->boardGame->id,
                    'name' => $element->boardGame->name,
                    'sort' => isset($element->boardGame->sort) ? $element->boardGame->sort : null,
                    'active' => true,
                ];
            }

            if (
                $element->relationLoaded('addedBy')
                && $element->addedBy
                && $filterKey === 'addedBy'
            ) {
                $result[$filterKey][$element->addedBy->id] = [
                    'id' => $element->addedBy->id,
                    'name' => $element->addedBy->public_name ? $element->addedBy->public_name : $element->addedBy->name,
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

        return $result;
    }

    /**
     * Получаем ключ, под которым возвращаются данные
     *
     * @param $keyForReturn
     * @return int|mixed|string
     */
    private function getNameByKey($keyForReturn)
    {
        $name = array_search($keyForReturn, self::KEYS_FOR_RETURN);

        return $name ? $name : $keyForReturn;
    }

    private function getNameByValue($filterKey)
    {
        return isset(self::KEYS_FOR_RETURN[$filterKey]) ? self::KEYS_FOR_RETURN[$filterKey] : $filterKey;
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
