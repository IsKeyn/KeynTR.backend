<?php

namespace App\Models\Traits;

use App\Models\AdditionalField;
use App\Models\Block;
use App\Models\Comments;
use App\Models\Group;
use App\Models\Media;
use App\Models\MenuType;
use App\Models\Seo;
use App\Models\Tag;
use App\Models\ViewsCount;
use App\Models\VotesCount;
use App\Models\VotesLog;
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

    public function media()
    {
        return $this->morphToMany(Media::class, 'media_bind')->withPivot('type', 'sort')->withTimestamps();
    }

    public function titleImage()
    {
        return $this->morphToMany(Media::class, 'media_bind')->withPivot('type', 'sort')->wherePivot('type', '=', Media::TITLE_TYPE)->withTimestamps();
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'tag_binds')->withTimestamps();
    }

    public function additionalFields()
    {
        return $this->morphMany(AdditionalField::class, 'entity');
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

    public function comments()
    {
        return $this->morphMany(Comments::class, 'entity');
    }

    public function views()
    {
        return $this->morphOne(ViewsCount::class, 'entity');
    }

    public function likes()
    {
        return $this->morphOne(VotesCount::class, 'entity')->where('vote_type', VotesLog::LIKE);
    }

    public function seo()
    {
        return $this->morphOne(Seo::class, 'entity');
    }

    public function menu()
    {
        return $this->morphMany(MenuType::class, 'menu_type_bind');
    }

    public function blocks()
    {
        return $this->morphToMany(Block::class, 'block_bind')->withPivot('type')->orderBy('position', 'asc')->withTimestamps();
    }

    public function group($id = null, $type = null)
    {
        return $this->morphToMany(Group::class, 'group_bind')
            ->withPivot(['first_b_id', 'first_b_type'])
            ->wherePivot('first_b_id', '=', $id)
            ->wherePivot('first_b_type', '=', $type)
            ->wherePivot('group_bind_id', '=', $this->id)
            ->withTimestamps();
    }
}
