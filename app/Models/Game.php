<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Game
 * @package App\Models
 * @property-read Game[]|array $fields
 */

class Game extends Model
{
    use HasFactory;

    public const EDITABLE_FIELDS = [
        [
            'description' => 'Наименование',
            'name' => 'name',
            'type' => 'input'
        ],
        [
            'description' => 'Slug',
            'name' => 'slug',
            'type' => 'input'
        ],
        [
            'description' => 'Описание',
            'name' => 'description',
            'type' => 'input'
        ],
        [
            'description' => 'Дополнительные поля',
            'name' => 'fields',
            'type' => 'additional_fields',
            'array_fields' => [
                [
                    'description' => 'Название поля',
                    'name' => 'name',
                    'type' => 'input'
                ],
                [
                    'description' => 'Значение поля',
                    'name' => 'value',
                    'type' => 'input'
                ],
                [
                    'description' => 'Сортировка',
                    'name' => 'sort',
                    'type' => 'input'
                ],
            ],
        ],
    ];

    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    public function platforms()
    {
        return $this->morphToMany(GamingPlatform::class, 'gaming_platform_bind');
    }

    public function fields()
    {
        return $this->morphMany(AdditionalField::class, 'entity');
    }

    public function media()
    {
        return $this->morphToMany(Media::class, 'media_bind');
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'tag_binds');
    }

    public function titleImage()
    {
        return $this->morphToMany(Media::class, 'media_bind')->withPivot('type');
    }

    public function dates()
    {
        return $this->morphToMany(Date::class, 'date_bind')->withPivot('type');
    }

    public function gamePlatform()
    {
        return $this->morphToMany(GamingPlatform::class, 'gaming_platform_bind');
    }
}
