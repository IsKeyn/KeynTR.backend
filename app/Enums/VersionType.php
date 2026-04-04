<?php
namespace App\Enums;

enum VersionType: int
{
    case Create = 1;
    case Update = 2;
    case SoftDelete = 3;
    case Recovery = 4;
    case Delete = 5;

    public function label(): string
    {
        return match($this) {
            self::Create => __('actions.create'),
            self::Update => __('actions.update'),
            self::SoftDelete => __('actions.soft_delete'),
            self::Recovery => __('actions.recovery'),
            self::Delete => __('actions.delete'),
        };
    }
}
