<?php

namespace App\Services;

use App\Models\Version;

class VersionService
{
    public static function set(
        $versionData,
        $entityType,
        $entityId,
        $name = null,
        $doType = null
    )
    {
        if (!$versionData) return false;

        $newVersionData = [
            'data' => $versionData,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'do_type' => $doType,
            'sort' => null,
            'active' => true,
            'created_by' => auth()->id(),
        ];

        if ($name) {
            $newVersionData['name'] = $name;
        }

        return Version::create($newVersionData);
    }
}
