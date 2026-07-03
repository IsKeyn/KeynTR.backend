<?php

namespace App\Console\Commands\Setup;

use App\Models\Menu;
use App\Models\MenuType;
use App\Models\Permission;
use App\Services\PermissionService;
use Illuminate\Console\Command;

class CreateAdminMenu extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'setup:create-admin-menu';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Создание админ меню';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->createMenuTypes();
        $this->createMenuElements();
        $this->syncMenuPermissions();

        return 0;
    }

    protected function createMenuTypes()
    {
        $types = [
            [
                'id' => 36,
                'name' => 'Пользователи',
                'code' => 'admin',
                'group' => 1,
                'group_icon' => 'fa-people-group',
                'menu_type_bind_id' => null,
                'sort' => 30,
                'active' => true,
            ],
            [
                'id' => 35,
                'name' => 'Справочники',
                'code' => 'admin',
                'group' => 1,
                'group_icon' => 'fa-book',
                'menu_type_bind_id' => null,
                'sort' => 40,
                'active' => true,
            ],
            [
                'id' => 37,
                'name' => 'Сущности',
                'code' => 'admin',
                'group' => 1,
                'group_icon' => 'fa-compact-disc',
                'menu_type_bind_id' => null,
                'sort' => 50,
                'active' => true,
            ],
            [
                'id' => 34,
                'name' => 'Настольная игры',
                'code' => 'admin',
                'group' => 1,
                'group_icon' => 'fa-solid fa-dice',
                'menu_type_bind_id' => null,
                'sort' => 100,
                'active' => true,
            ],
        ];

        foreach ($types as $type) {
            $menuType = MenuType::updateOrCreate(
                ['code' => $type['name']],
                $type
            );

            echo '<pre>';
            print_r($menuType);
            echo '</pre>';
        }
    }

    protected function createMenuElements()
    {
        $elements = [
            [
                'id' => 15,
                'name' => 'Главная',
                'url' => '/admin/',
                'target' => null,
                'menu_type_id' => 5,
                'link_type' => 'route',
                'icon' => 'fa-user',
                'sort' => 10,
                'active' => true,
            ],
            [
                'name' => 'Пользователи',
                'url' => '/admin/user/',
                'target' => null,
                'menu_type_id' => 36,
                'link_type' => 'route',
                'icon' => 'fa-user',
                'sort' => 10,
                'active' => true,
            ],
            [
                'name' => 'Оповещения',
                'url' => '/admin/notification/',
                'target' => null,
                'menu_type_id' => 36,
                'link_type' => 'route',
                'icon' => 'fa-bell',
                'sort' => 20,
                'active' => true,
            ],
            [
                'name' => 'Роли',
                'url' => '/admin/role/',
                'target' => null,
                'menu_type_id' => 36,
                'link_type' => 'route',
                'icon' => 'fa-person-digging',
                'sort' => 30,
                'active' => true,
            ],
            [
                'id' => 113,
                'name' => 'Разрешения',
                'url' => '/admin/permission/',
                'target' => null,
                'menu_type_id' => 36,
                'link_type' => 'route',
                'icon' => 'fa-lock-open',
                'sort' => 40,
                'active' => true,
            ],
            [
                'name' => 'Персоны',
                'url' => '/admin/person/',
                'target' => null,
                'menu_type_id' => 35,
                'link_type' => 'route',
                'icon' => null,
                'sort' => 30,
                'active' => true,
            ],
            [
                'name' => 'Игры',
                'url' => '/admin/games/',
                'target' => null,
                'menu_type_id' => 37,
                'link_type' => 'route',
                'icon' => null,
                'sort' => 30,
                'active' => true,
            ],
            [
                'name' => 'Серии',
                'url' => '/admin/series/',
                'target' => null,
                'menu_type_id' => 37,
                'link_type' => 'route',
                'icon' => null,
                'sort' => 35,
                'active' => true,
            ],
            [
                'name' => 'Слайды',
                'url' => '/admin/slides/',
                'target' => null,
                'menu_type_id' => 37,
                'link_type' => 'route',
                'icon' => null,
                'sort' => 50,
                'active' => true,
            ],
            [
                'name' => 'Рекомендации',
                'url' => '/admin/recommendation/',
                'target' => null,
                'menu_type_id' => 37,
                'link_type' => 'route',
                'icon' => null,
                'sort' => 55,
                'active' => true,
            ],
            [
                'name' => 'Предметы магазина',
                'url' => '/admin/board-game-shop-item/',
                'target' => null,
                'menu_type_id' => 34,
                'link_type' => 'route',
                'icon' => null,
                'sort' => 55,
                'active' => true,
            ],
            [
                'name' => 'Привязка статус эффектов',
                'url' => '/admin/board-game-status-effect-bind/',
                'target' => null,
                'menu_type_id' => 34,
                'link_type' => 'route',
                'icon' => null,
                'sort' => 45,
                'active' => true,
            ],
            [
                'name' => 'Взаимодействия игроков',
                'url' => '/admin/board-game-player-interaction',
                'target' => null,
                'menu_type_id' => 34,
                'link_type' => 'route',
                'icon' => null,
                'sort' => 65,
                'active' => true,
            ],
        ];

        foreach ($elements as $element) {
            $menu = Menu::updateOrCreate(
                ['url' => $element['url']],
                $element
            );

            echo '<pre>';
            print_r($menu);
            echo '</pre>';
        }
    }

    protected function syncMenuPermissions()
    {
        $elements = [
            [
                'url' => '/admin/',
                'permissions' => ['admin.index'],
            ],
            [
                'url' => '/admin/sites',
                'permissions' => ['site.edit'],
            ],
            [
                'url' => '/admin/settings/',
                'permissions' => ['site.edit'],
            ],
            [
                'url' => '/admin/socials/',
                'permissions' => ['site.edit'],
            ],
            [
                'url' => '/admin/menu-types/',
                'permissions' => ['menu.edit'],
            ],
            [
                'url' => '/admin/menu/',
                'permissions' => ['menu.edit'],
            ],
            [
                'url' => '/admin/user/',
                'permissions' => ['user.edit'],
            ],
            [
                'url' => '/admin/notification/',
                'permissions' => ['user.notification.edit'],
            ],
            [
                'url' => '/admin/role/',
                'permissions' => ['user.roles.edit'],
            ],
            [
                'url' => '/admin/permission/',
                'permissions' => ['user.permission.edit'],
            ],
            [
                'url' => '/admin/media/',
                'permissions' => ['media.edit'],
            ],
            [
                'url' => '/admin/tags/',
                'permissions' => ['tags.edit'],
            ],
            [
                'url' => '/admin/gaming-platforms/',
                'permissions' => ['gaming-platform.edit'],
            ],
            [
                'url' => '/admin/genres/',
                'permissions' => ['genre.edit'],
            ],
            [
                'url' => '/admin/companies/',
                'permissions' => ['company.edit'],
            ],
            [
                'url' => '/admin/groups/',
                'permissions' => ['group.edit'],
            ],
            [
                'url' => '/admin/person/',
                'permissions' => ['person.edit'],
            ],
            [
                'url' => '/admin/articles/',
                'permissions' => ['article.edit'],
            ],
            [
                'url' => '/admin/pages/',
                'permissions' => ['page.edit'],
            ],
            [
                'url' => '/admin/games/',
                'permissions' => ['game.edit'],
            ],
            [
                'url' => '/admin/series/',
                'permissions' => ['series.edit'],
            ],
            [
                'url' => '/admin/movie/',
                'permissions' => ['movie.edit'],
            ],
            [
                'url' => '/admin/slides/',
                'permissions' => ['slide.edit'],
            ],
            [
                'url' => '/admin/recommendation/',
                'permissions' => ['recommend.edit'],
            ],
            [
                'url' => '/admin/links/',
                'permissions' => ['link.edit'],
            ],
            [
                'url' => '/admin/media-group/',
                'permissions' => ['media-group.edit'],
            ],
            [
                'url' => '/admin/comments/',
                'permissions' => ['comment.edit'],
            ],
            [
                'url' => '/admin/board-game',
                'permissions' => ['bg.edit'],
            ],
            [
                'url' => '/admin/board-game-players',
                'permissions' => ['bg.players.edit'],
            ],
            [
                'url' => '/admin/board-game-item',
                'permissions' => ['bg.item.edit'],
            ],
            [
                'url' => '/admin/board-game-item-bind',
                'permissions' => ['bg.item-bind.edit'],
            ],
            [
                'url' => '/admin/board-game-inventory',
                'permissions' => ['bg.player-inventory.edit'],
            ],
            [
                'url' => '/admin/board-game-status-effect',
                'permissions' => ['bg.status-effect.edit'],
            ],
            [
                'url' => '/admin/board-game-status-effect-bind',
                'permissions' => ['bg.status-effect-bind.edit'],
            ],
            [
                'url' => '/admin/board-game-player-status-effect',
                'permissions' => ['bg.status-effect-on-player.edit'],
            ],
            [
                'url' => '/admin/board-game-board',
                'permissions' => ['bg.board.edit'],
            ],
            [
                'url' => '/admin/board-position-effect',
                'permissions' => ['bg.board-position-effect.edit'],
            ],
            [
                'url' => '/admin/board-position-effects-bind/',
                'permissions' => ['bg.board-position-effects-bind.edit'],
            ],
            [
                'url' => '/admin/board-game-shop-item/',
                'permissions' => ['bg.shop-item.edit'],
            ],
            [
                'url' => '/admin/board-game-player-position',
                'permissions' => ['bg.player-position.edit'],
            ],
            [
                'url' => '/admin/board-game-player-interaction',
                'permissions' => ['bg.player-interaction.edit'],
            ],
            [
                'url' => '/admin/board-game-game-list',
                'permissions' => ['bg.game-list.edit'],
            ],
            [
                'url' => '/admin/board-game-player-game',
                'permissions' => ['bg.player-game.edit'],
            ],
            [
                'url' => '/admin/board-game-timer',
                'permissions' => ['bg.timer.edit'],
            ],
            [
                'url' => '/admin/board-game-player-timer',
                'permissions' => ['bg.player-timer.edit'],
            ],
            [
                'url' => '/admin/board-game-log',
                'permissions' => ['bg.log.edit'],
            ],
            [
                'url' => '/admin/votes-logs/',
                'permissions' => ['votes-logs.edit'],
            ],
        ];

        foreach ($elements as $element) {
            $menu = Menu::query()->where('url', $element['url'])->first();

            if ($menu) {
                $arPermissionIds = [];

                foreach ($element['permissions'] as $permission) {
                    $entity = Permission::query()->where('system_name', $permission)->first();

                    if ($entity) {
                        $arPermissionIds[] = ['permissions' => $entity->id];
                    }
                }

                PermissionService::set($menu, $arPermissionIds);

                echo '<pre>';
                print_r($element);
                echo '</pre>';
            }
        }
    }
}
