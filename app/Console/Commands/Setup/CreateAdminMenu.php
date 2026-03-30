<?php

namespace App\Console\Commands\Setup;

use App\Models\Menu;
use App\Models\MenuType;
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

        return 0;
    }

    protected function createMenuTypes()
    {
        $types = [
            [
                'id' => 35,
                'name' => 'Справочники',
                'code' => 'admin',
                'group' => 1,
                'group_icon' => 'fa-solid fa-book',
                'menu_type_bind_id' => null,
                'sort' => 40,
                'active' => true,
            ],
            [
                'id' => 37,
                'name' => 'Сущности',
                'code' => 'admin',
                'group' => 1,
                'group_icon' => 'fa-solid fa-compact-disc',
                'menu_type_bind_id' => null,
                'sort' => 50,
                'active' => true,
            ],
        ];

        foreach ($types as $type) {
            $menuType = MenuType::firstOrCreate(
                ['name' => $type['name']],
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
        ];

        foreach ($elements as $element) {
            $menu = Menu::firstOrCreate(
                ['url' => $element['url']],
                $element
            );

            echo '<pre>';
            print_r($menu);
            echo '</pre>';
        }
    }
}
