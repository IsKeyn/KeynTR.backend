<?php

namespace App\Services;

use App\Models\Version;

class VersionService
{
    public static function set($versionData, $entityType, $entityId)
    {
        if (!$versionData) return false;

        $newVersionData = [
            'data' => $versionData,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'sort' => null,
            'active' => true,
            'created_by' => auth()->id(),
        ];

        return Version::create($newVersionData);
    }
}
