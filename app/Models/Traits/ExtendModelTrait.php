<?php

namespace App\Models\Traits;

use App\Models\Media;
use Illuminate\Database\Eloquent\Builder;

trait ExtendModelTrait
{
    public function scopeFindById($query, $id)
    {
        return $query->where('id', $id);
    }

    public function scopeFindBySlug($query, $slug)
    {
        return $query->where('slug', $slug);
    }

    public function scopeFindByName($query, $name)
    {
        return $query->where('name', $name);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    public function titleImage() // TODO логично переименовать в "media"
    {
        return $this->morphToMany(Media::class, 'media_bind')->withPivot('type');
    }

    public function getModelAttribute()
    {
        return get_class($this);
    }

    public function getTitleImageAttribute()
    {
        return $this->titleImage()
            ->wherePivot('type', '=', Media::TITLE_TYPE)
            ->first();
    }
}
