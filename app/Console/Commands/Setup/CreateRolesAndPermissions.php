<?php

namespace App\Console\Commands\Setup;

use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\Role;
use Illuminate\Console\Command;

class CreateRolesAndPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'setup:create-role-and-permissions {resetData}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Создание ролей и разрешений';

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
        $reset = $this->argument('resetData');

        if ($reset === 'true') {
            Role::truncate();
            Permission::truncate();
            PermissionRole::truncate();
        }

        $this->createRoles();
        $this->createPermissions();
        $this->syncPermissionRole();

        return 0;
    }

    protected function createRoles()
    {
        $elements = [
            [
                'id' => 1,
                'name' => 'Супер админ',
                'system_name' => 'super_admin',
                'sort' => 10,
                'active' => true,
            ],
            [
                'id' => 2,
                'name' => 'Админ',
                'system_name' => 'admin',
                'sort' => 20,
                'active' => true,
            ],
            [
                'id' => 3,
                'name' => 'Редактор',
                'system_name' => 'editor',
                'sort' => 30,
                'active' => true,
            ],
            [
                'id' => 4,
                'name' => 'Редактор сайта',
                'system_name' => 'site.editor',
                'sort' => 40,
                'active' => true,
            ],
            [
                'id' => 5,
                'name' => 'Редактор меню',
                'system_name' => 'menu.editor',
                'sort' => 50,
                'active' => true,
            ],
            [
                'id' => 6,
                'name' => 'Редактор пользователей',
                'system_name' => 'user.editor',
                'sort' => 60,
                'active' => true,
            ],
            [
                'id' => 7,
                'name' => 'Редактор справочников',
                'system_name' => 'reference.editor',
                'sort' => 70,
                'active' => true,
            ],
            [
                'id' => 8,
                'name' => 'Редактор статей',
                'system_name' => 'reference.editor',
                'sort' => 80,
                'active' => true,
            ],
            [
                'id' => 9,
                'name' => 'Редактор страниц',
                'system_name' => 'page.editor',
                'sort' => 90,
                'active' => true,
            ],
            [
                'id' => 10,
                'name' => 'Редактор игр',
                'system_name' => 'game.editor',
                'sort' => 100,
                'active' => true,
            ],
            [
                'id' => 11,
                'name' => 'Редактор серий',
                'system_name' => 'series.editor',
                'sort' => 110,
                'active' => true,
            ],
            [
                'id' => 12,
                'name' => 'Редактор фильмов',
                'system_name' => 'movies.editor',
                'sort' => 120,
                'active' => true,
            ],
            [
                'id' => 13,
                'name' => 'Редактор слайдов',
                'system_name' => 'slide.editor',
                'sort' => 130,
                'active' => true,
            ],
            [
                'id' => 14,
                'name' => 'Редактор рекомендаций',
                'system_name' => 'recommend.editor',
                'sort' => 140,
                'active' => true,
            ],
            [
                'id' => 15,
                'name' => 'Редактор ссылок',
                'system_name' => 'link.editor',
                'sort' => 150,
                'active' => true,
            ],
            [
                'id' => 16,
                'name' => 'Редактор медиа групп',
                'system_name' => 'media_group.editor',
                'sort' => 160,
                'active' => true,
            ],
            [
                'id' => 17,
                'name' => 'Модератор комментариев',
                'system_name' => 'comment.editor',
                'sort' => 170,
                'active' => true,
            ],
            [
                'id' => 18,
                'name' => 'Редактор ивентов',
                'system_name' => 'board_game.editor',
                'sort' => 180,
                'active' => true,
            ],
            [
                'id' => 19,
                'name' => 'Модератор логов',
                'system_name' => 'logs.editor',
                'sort' => 190,
                'active' => true,
            ],
        ];

        foreach ($elements as $element) {
            $role = Role::updateOrCreate(
                ['name' => $element['name']],
                $element
            );

            echo '<pre>';
            print_r($role);
            echo '</pre>';
        }
    }

    protected function createPermissions()
    {
        $elements = [
            [
                'id' => 1,
                'name' => 'Супер администратор',
                'system_name' => 'admin.super',
                'entity_type' => null,
                'sort' => 10,
                'active' => true,
            ],
            [
                'id' => 2,
                'name' => 'Доступ в админку',
                'system_name' => 'admin.dashboard',
                'entity_type' => null,
                'sort' => 20,
                'active' => true,
            ],
            [
                'id' => 3,
                'name' => 'Доступ на главную страницу админки',
                'system_name' => 'admin.index',
                'entity_type' => null,
                'sort' => 30,
                'active' => true,
            ],
            [
                'id' => 45,
                'name' => 'Редактор версий',
                'system_name' => 'version.edit',
                'entity_type' => null,
                'sort' => 35,
                'active' => true,
            ],
            [
                'id' => 4,
                'name' => 'Редактирование сайта',
                'system_name' => 'site.edit',
                'entity_type' => null,
                'sort' => 40,
                'active' => true,
            ],
            [
                'id' => 5,
                'name' => 'Редактирование меню',
                'system_name' => 'menu.edit',
                'entity_type' => null,
                'sort' => 50,
                'active' => true,
            ],
            [
                'id' => 6,
                'name' => 'Редактирование пользователей',
                'system_name' => 'user.edit',
                'entity_type' => null,
                'sort' => 60,
                'active' => true,
            ],
            [
                'id' => 7,
                'name' => 'Редактирование оповещений',
                'system_name' => 'user.notification.edit',
                'entity_type' => null,
                'sort' => 70,
                'active' => true,
            ],
            [
                'id' => 8,
                'name' => 'Редактирование ролей',
                'system_name' => 'user.roles.edit',
                'entity_type' => null,
                'sort' => 80,
                'active' => true,
            ],
            [
                'id' => 9,
                'name' => 'Редактирование разрешений',
                'system_name' => 'user.permission.edit',
                'entity_type' => null,
                'sort' => 90,
                'active' => true,
            ],
            [
                'id' => 10,
                'name' => 'Редактирование медиа',
                'system_name' => 'media.edit',
                'entity_type' => null,
                'sort' => 100,
                'active' => true,
            ],
            [
                'id' => 11,
                'name' => 'Редактирование тегов',
                'system_name' => 'tags.edit',
                'entity_type' => null,
                'sort' => 110,
                'active' => true,
            ],
            [
                'id' => 12,
                'name' => 'Редактирование игровых платформ',
                'system_name' => 'gaming-platform.edit',
                'entity_type' => null,
                'sort' => 120,
                'active' => true,
            ],
            [
                'id' => 13,
                'name' => 'Редактирование жанров',
                'system_name' => 'genre.edit',
                'entity_type' => null,
                'sort' => 130,
                'active' => true,
            ],
            [
                'id' => 14,
                'name' => 'Редактирование компаний',
                'system_name' => 'company.edit',
                'entity_type' => null,
                'sort' => 140,
                'active' => true,
            ],
            [
                'id' => 15,
                'name' => 'Редактирование групп',
                'system_name' => 'group.edit',
                'entity_type' => null,
                'sort' => 150,
                'active' => true,
            ],
            [
                'id' => 16,
                'name' => 'Редактирование персон',
                'system_name' => 'person.edit',
                'entity_type' => null,
                'sort' => 160,
                'active' => true,
            ],
            [
                'id' => 17,
                'name' => 'Редактирование статей',
                'system_name' => 'article.edit',
                'entity_type' => null,
                'sort' => 170,
                'active' => true,
            ],
            [
                'id' => 18,
                'name' => 'Редактирование страниц',
                'system_name' => 'page.edit',
                'entity_type' => null,
                'sort' => 180,
                'active' => true,
            ],
            [
                'id' => 19,
                'name' => 'Редактирование игр',
                'system_name' => 'game.edit',
                'entity_type' => null,
                'sort' => 190,
                'active' => true,
            ],
            [
                'id' => 20,
                'name' => 'Редактирование серий',
                'system_name' => 'series.edit',
                'entity_type' => null,
                'sort' => 200,
                'active' => true,
            ],
            [
                'id' => 21,
                'name' => 'Редактирование фильмов',
                'system_name' => 'movie.edit',
                'entity_type' => null,
                'sort' => 210,
                'active' => true,
            ],
            [
                'id' => 22,
                'name' => 'Редактирование слайдов',
                'system_name' => 'slide.edit',
                'entity_type' => null,
                'sort' => 220,
                'active' => true,
            ],
            [
                'id' => 23,
                'name' => 'Редактирование рекомендаций',
                'system_name' => 'recommend.edit',
                'entity_type' => null,
                'sort' => 230,
                'active' => true,
            ],
            [
                'id' => 24,
                'name' => 'Редактирование ссылок',
                'system_name' => 'link.edit',
                'entity_type' => null,
                'sort' => 240,
                'active' => true,
            ],
            [
                'id' => 25,
                'name' => 'Редактирование медиа групп',
                'system_name' => 'media-group.edit',
                'entity_type' => null,
                'sort' => 250,
                'active' => true,
            ],
            [
                'id' => 26,
                'name' => 'Редактирование комментариев',
                'system_name' => 'comment.edit',
                'entity_type' => null,
                'sort' => 260,
                'active' => true,
            ],
            [
                'id' => 27,
                'name' => 'Редактирование настольной игры - ни',
                'system_name' => 'bg.edit',
                'entity_type' => null,
                'sort' => 270,
                'active' => true,
            ],
            [
                'id' => 28,
                'name' => 'Редактирование игроков (ни)',
                'system_name' => 'bg.players.edit',
                'entity_type' => null,
                'sort' => 280,
                'active' => true,
            ],
            [
                'id' => 29,
                'name' => 'Редактирование предметов (ни)',
                'system_name' => 'bg.item.edit',
                'entity_type' => null,
                'sort' => 290,
                'active' => true,
            ],
            [
                'id' => 30,
                'name' => 'Редактирование привязок предметов (ни)',
                'system_name' => 'bg.item-bind.edit',
                'entity_type' => null,
                'sort' => 300,
                'active' => true,
            ],
            [
                'id' => 31,
                'name' => 'Редактирование инвенторя игроков (ни)',
                'system_name' => 'bg.player-inventory.edit',
                'entity_type' => null,
                'sort' => 310,
                'active' => true,
            ],
            [
                'id' => 32,
                'name' => 'Редактирование статус эффектов (ни)',
                'system_name' => 'bg.status-effect.edit',
                'entity_type' => null,
                'sort' => 320,
                'active' => true,
            ],
            [
                'id' => 44,
                'name' => 'Редактирование привязки статус эффектов (ни)',
                'system_name' => 'bg.status-effect-bind.edit',
                'entity_type' => null,
                'sort' => 325,
                'active' => true,
            ],
            [
                'id' => 33,
                'name' => 'Редактирование статус эффектов на игроках (ни)',
                'system_name' => 'bg.status-effect-on-player.edit',
                'entity_type' => null,
                'sort' => 330,
                'active' => true,
            ],
            [
                'id' => 34,
                'name' => 'Редактирование игрового поля (ни)',
                'system_name' => 'bg.board.edit',
                'entity_type' => null,
                'sort' => 340,
                'active' => true,
            ],
            [
                'id' => 35,
                'name' => 'Редактирование эффектов игрового поля (ни)',
                'system_name' => 'bg.board-position-effect.edit',
                'entity_type' => null,
                'sort' => 350,
                'active' => true,
            ],
            [
                'id' => 36,
                'name' => 'Редактирование привязок эффектов игрового поля (ни)',
                'system_name' => 'bg.board-position-effects-bind.edit',
                'entity_type' => null,
                'sort' => 360,
                'active' => true,
            ],
            [
                'id' => 37,
                'name' => 'Редактирование позиций игроков на игровом поле (ни)',
                'system_name' => 'bg.player-position.edit',
                'entity_type' => null,
                'sort' => 370,
                'active' => true,
            ],
            [
                'id' => 38,
                'name' => 'Редактирование списка игр (ни)',
                'system_name' => 'bg.game-list.edit',
                'entity_type' => null,
                'sort' => 380,
                'active' => true,
            ],
            [
                'id' => 39,
                'name' => 'Редактирование игр участников (ни)',
                'system_name' => 'bg.player-game.edit',
                'entity_type' => null,
                'sort' => 390,
                'active' => true,
            ],
            [
                'id' => 40,
                'name' => 'Редактирование таймеров (ни)',
                'system_name' => 'bg.timer.edit',
                'entity_type' => null,
                'sort' => 400,
                'active' => true,
            ],
            [
                'id' => 41,
                'name' => 'Редактирование запусков, остановок таймера (ни)',
                'system_name' => 'bg.player-timer.edit',
                'entity_type' => null,
                'sort' => 410,
                'active' => true,
            ],
            [
                'id' => 42,
                'name' => 'Редактирование логов (ни)',
                'system_name' => 'bg.log.edit',
                'entity_type' => null,
                'sort' => 420,
                'active' => true,
            ],
            [
                'id' => 43,
                'name' => 'Редактирование логов голосов',
                'system_name' => 'votes-logs.edit',
                'entity_type' => null,
                'sort' => 430,
                'active' => true,
            ],
        ];

        foreach ($elements as $element) {
            $permission = Permission::updateOrCreate(
                ['name' => $element['name']],
                $element
            );

            // Привязывать к роли

            echo '<pre>';
            print_r($permission);
            echo '</pre>';
        }
    }

    protected function syncPermissionRole()
    {
        $elements = [
            [
                'permission_id' => 1,
                'role_id' => 1,
            ],
            [
                'permission_id' => 2,
                'role_id' => 2,
            ],
            [
                'permission_id' => 3,
                'role_id' => 2,
            ],
            [
                'permission_id' => 4,
                'role_id' => 2,
            ],
            [
                'permission_id' => 2,
                'role_id' => 3,
            ],
            [
                'permission_id' => 5,
                'role_id' => 5,
            ],
            [
                'permission_id' => 6,
                'role_id' => 6,
            ],
            [
                'permission_id' => 7,
                'role_id' => 6,
            ],
            [
                'permission_id' => 8,
                'role_id' => 6,
            ],
            [
                'permission_id' => 9,
                'role_id' => 6,
            ],
            [
                'permission_id' => 10,
                'role_id' => 7,
            ],
            [
                'permission_id' => 11,
                'role_id' => 7,
            ],
            [
                'permission_id' => 12,
                'role_id' => 7,
            ],
            [
                'permission_id' => 13,
                'role_id' => 7,
            ],
            [
                'permission_id' => 14,
                'role_id' => 7,
            ],
            [
                'permission_id' => 15,
                'role_id' => 7,
            ],
            [
                'permission_id' => 16,
                'role_id' => 7,
            ],
            [
                'permission_id' => 17,
                'role_id' => 8,
            ],
            [
                'permission_id' => 18,
                'role_id' => 9,
            ],
            [
                'permission_id' => 19,
                'role_id' => 10,
            ],
            [
                'permission_id' => 20,
                'role_id' => 11,
            ],
            [
                'permission_id' => 21,
                'role_id' => 12,
            ],
            [
                'permission_id' => 22,
                'role_id' => 13,
            ],
            [
                'permission_id' => 23,
                'role_id' => 14,
            ],
            [
                'permission_id' => 24,
                'role_id' => 15,
            ],
            [
                'permission_id' => 25,
                'role_id' => 16,
            ],
            [
                'permission_id' => 26,
                'role_id' => 17,
            ],
            [
                'permission_id' => 27,
                'role_id' => 18,
            ],
            [
                'permission_id' => 28,
                'role_id' => 18,
            ],
            [
                'permission_id' => 29,
                'role_id' => 18,
            ],
            [
                'permission_id' => 30,
                'role_id' => 18,
            ],
            [
                'permission_id' => 31,
                'role_id' => 18,
            ],
            [
                'permission_id' => 32,
                'role_id' => 18,
            ],
            [
                'permission_id' => 33,
                'role_id' => 18,
            ],
            [
                'permission_id' => 34,
                'role_id' => 18,
            ],
            [
                'permission_id' => 35,
                'role_id' => 18,
            ],
            [
                'permission_id' => 36,
                'role_id' => 18,
            ],
            [
                'permission_id' => 37,
                'role_id' => 18,
            ],
            [
                'permission_id' => 38,
                'role_id' => 18,
            ],
            [
                'permission_id' => 39,
                'role_id' => 18,
            ],
            [
                'permission_id' => 40,
                'role_id' => 18,
            ],
            [
                'permission_id' => 41,
                'role_id' => 18,
            ],
            [
                'permission_id' => 42,
                'role_id' => 18,
            ],
            [
                'permission_id' => 43,
                'role_id' => 19,
            ],
        ];

        foreach ($elements as $element) {
            $permissionRole = PermissionRole::firstOrCreate(
                [
                    'permission_id' => $element['permission_id'],
                    'role_id' => $element['role_id'],
                ],
                $element
            );

            // Привязывать к роли

            echo '<pre>';
            print_r($permissionRole);
            echo '</pre>';
        }
    }
}
