<?php

namespace App\Models\BoardGame;

use App\Models\Media;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BoardGame extends Model
{
    use HasFactory;

    const CLOSE_STATUS = 0;
    const OPEN_STATUS = 1;
    const COMING_SOON = 2;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'settings',
        'active',
        'is_close',
        'started_at',
        'ended_at',
        'created_by',
    ];

    protected $casts = [
        'active' => 'boolean',
        'is_close' => 'boolean',
    ];

    public function getModelAttribute()
    {
        return get_class($this);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('is_close', false);
    }

    public function scopeFindBySlug($query, $slug)
    {
        return $query->where('slug', $slug);
    }

    public function scopeFindById($query, $id)
    {
        return $query->where('id', $id);
    }

    public function titleImage()
    {
        return $this->morphToMany(Media::class, 'media_bind')->withPivot('type');
    }

    public function media()
    {
        return $this->morphToMany(Media::class, 'media_bind');
    }

    public function players()
    {
        return $this->hasMany(BoardGamePlayer::class, 'board_game_id');
    }

    public function getStatusAttribute()
    {
        $status = self::OPEN_STATUS;

        if ($this->is_close) {
            $status = self::CLOSE_STATUS;
        } else if ($this->ended_at && Carbon::now() > $this->ended_at) {
            $status = self::CLOSE_STATUS;
        } else if ($this->started_at && Carbon::now() < $this->started_at) {
            $status = self::COMING_SOON;
        }

        return $status;
    }
}
