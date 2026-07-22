<?php

namespace App\Models\BoardGame;

use App\Models\Setting;
use App\Models\Traits\ExtendModelTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BoardGame extends Model
{
    use HasFactory,
        ExtendModelTrait,
        SoftDeletes;

    /*
     * Настройки BoardGame
     * type String upon-request - закрытая игра, registrationIsClose - регистрация закрыта
     * item_roll_default_count Int Достуное количество круток рулетки предметов, для нового игрока
     * step_default_count Int Доступное количество шагов по игровой доске, для нового игрока
     * board_type String Тип доски, который используется в настольной игре
     * subtract_points Int Количество очков, которое отнимается при рероле
     * time_limit Int В часах, максимальное количество времени для челенджа, используется в таймере
     * event_type String Тип окончания ивента для игрока null - лимит времени, board-last-cell - достижение последней клетки игры
     * max_negative_points_for_roll_game Int Минимальное количество очков, при котором игрок более не может крутить рулетку игр
     * eventGamePlatforms Json Игровые платформы в ивенте
     * Пример eventGamePlatforms: [{"id":25,"name":"Nintendo Entertainment System (NES)","active":true,"difficult":80,"minCountForAdd":2}]
     * hasExceptionPlatforms Boolean Могут ли игроки исключать платформы из пула рулетки
     * factorHours Int Количество часов, после которого дается 1 очков за еденицу сложности (в остальных случаях 0.5)
     * usePlatformDifficultInCalc Int Использовать сложность платформы в расчете очков за игру
     * addingGamesConditions Json Условия добавления игр
     * Пример {"position":30,"finishedGames":3}
     * last_players_with_every_day_status_effect Int Количество игроков, которое ислючаются для поиска нового игрока для предмета
     * bonus_for_coop Json Бонус начисляемый после прохождения игры в коопе, для игрока, пригласившего в кооп
     */

    public const CACHE_NAME = 'board-game';
    public const TABLE_NAME = 'board_games';

    const CLOSE_STATUS = 0;
    const OPEN_STATUS = 1;
    const COMING_SOON = 2;

    public const PUBLIC_CONTROLLER = 'App\Http\Controllers\BoardGame\BoardController';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'active',
        'sort',
        'is_close',
        'is_test',
        'started_at',
        'ended_at',
        'created_by',
    ];

    protected $casts = [
        'active' => 'boolean',
        'is_close' => 'boolean',
        'is_test' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('is_close', false);
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

    public function settings()
    {
        return $this->morphMany(Setting::class, 'entity');
    }

    public function players()
    {
        return $this->hasMany(BoardGamePlayer::class, 'board_game_id');
    }

    public function statusEffectBinds(): HasMany
    {
        return $this->hasMany(StatusEffectBind::class, 'board_game_id');
    }

    public function boardPositionEffectsBinds(): HasMany
    {
        return $this->hasMany(BoardPositionEffectsBind::class, 'board_game_id');
    }

    public function statusEffects(): BelongsToMany
    {
        return $this->belongsToMany(
            StatusEffect::class,
            'bg_status_effects_binds',
            'board_game_id',
            'status_effect_id'
        )
            ->withPivot('active', 'created_by')
            ->withTimestamps();
    }

    public function games(): HasMany
    {
        return $this->hasMany(BoardGameGameList::class, 'board_game_id');
    }
}
