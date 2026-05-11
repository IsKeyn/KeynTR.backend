<?php

namespace App\Http\Controllers\Admin\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Http\Resources\Role\ShortResource;
use App\Models\Role;
use App\Models\User;
use App\Services\Cache\UserCacheService;
use App\Services\Entity\DefaultAdminEntityService;
use App\Services\RelatedDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    protected DefaultAdminEntityService $defaultAdminEntityService;

    public function __construct(DefaultAdminEntityService $defaultAdminEntityService)
    {
        $this->defaultAdminEntityService = $defaultAdminEntityService;
    }

    public function index(Request $request)
    {
        return $this->defaultAdminEntityService->index(
            $request,
            'App\Models\User',
            'App\Services\Cache\UserCacheService',
            'App\Filters\UserFilter',
            'App\Http\Resources\Admin\User\ListResource',
        );
    }


    public function store(UserRequest $request)
    {
        $validated = $request->validated();

        if (!isset($validated['created_by'])) {
            $validated['created_by'] = $request->user()->id;
        }

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
            $validated['remember_token'] = Str::random(60);
        } else {
            unset($validated['password']);
        }

        if (!isset($validated['created_at'])) {
            unset($validated['created_at']);
        }

        if ($item = User::create($validated)) {
            $relatedDataService = app(RelatedDataService::class);
            $relatedDataService->set($item, $validated);

            return $item;
        }
    }

    public function update(UserRequest $request, User $user)
    {
        $validated = $request->validated();

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
            $validated['remember_token'] = Str::random(60);
        } else {
            unset($validated['password']);
        }

        $user->fill($validated);

        /*
         * Код отвечает за то, чтобы Observer updated сработал только 1 раз, не зависимо от того, было обновления
         * основной таблицы или же обновились связи
         */
        $attributesChanged = $user->isDirty();

        if ($attributesChanged) {
            $user->save();
        }

        $relatedDataService = app(RelatedDataService::class);
        $relatedDataService->set($user, $validated);

        if (!$attributesChanged) {
            $user->touch();
        }

        return true;
    }

    public function edit(Request $request, $id)
    {
        return $this->defaultAdminEntityService->edit(
            $request,
            $id,
            'App\Services\User\UserService'
        );
    }

    public function destroy(User $user)
    {
        return $this->defaultAdminEntityService->destroy($user);
    }

    public function forceDelete($id)
    {
        return $this->defaultAdminEntityService->forceDelete(
            'App\Models\User',
            $id
        );
    }

    public function recovery($id)
    {
        return $this->defaultAdminEntityService->recovery(
            'App\Models\User',
            $id
        );
    }

    public function getListFilters(Request $request)
    {
        return $this->defaultAdminEntityService->getListFilters(
            $request,
            'App\Models\User',
            'App\Filters\UserFilter',
            'App\Services\Cache\UserCacheService',
        );
    }

    public function fullLogout($userId)
    {
        $user = User::find($userId);
        $user->update(['remember_token' => Str::random(60)]);
        $this->logoutUserById($userId);

        return true;
    }

    private function logoutUserById($userId)
    {
        // TODO Добавить проверку, какое значение стоит в SESSION_DRIVER и использовать разные методы разлогинивания
        $deleted = 0;
        $sessionFiles = glob(storage_path('framework/sessions/*'));

        foreach ($sessionFiles as $file) {
            $content = @file_get_contents($file);
            if ($content === false) continue;

            // два безопасных паттерна: сериализованный ключ s:N:"login_...";i:ID;
            // и вариант с "login_..." без s:\d+ префикса
            $patterns = [
                '/s:\d+:"login_[^"]*";i:' . (int)$userId . ';/',
                '/"login_[^"]*";i:' . (int)$userId . ';/',
            ];

            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $content)) {
                    @unlink($file); // удаляем файл сессии
                    $deleted++;
                    break;
                }
            }
        }

        return $deleted; // возвращает сколько файлов удалено
    }

    public function getAdditionalData()
    {
        return Cache::remember(UserCacheService::ADMIN_ADDDATA_PREFIX, UserCacheService::TIME, function () {
            return [
                'roles' => ShortResource::collection(Role::all()),
            ];
        });
    }
}
