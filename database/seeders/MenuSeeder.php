<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('menu_types')->insert(
            [
                ['name' => 'Колонка подвала 1', 'code' => 'footer_column_1', 'created_at' => Carbon::now()],
                ['name' => 'Колонка подвала 2', 'code' => 'footer_column_2', 'created_at' => Carbon::now()],
                ['name' => 'Колонка подвала 3', 'code' => 'footer_column_3', 'created_at' => Carbon::now()],
                ['name' => 'Колонка подвала 4', 'code' => 'footer_column_4', 'created_at' => Carbon::now()],
            ]
        );

        DB::table('menus')->insert(
            [
                [
                    'name' => 'YouTube канал',
                    'url' => 'http://www.youtube.com/KeynTR',
                    'target' => 'blank',
                    'menu_type_id' => DB::table('menu_types')->where('code', 'footer_column_1')->first()->id,
                    'link_type' => null,
                    'created_at' => Carbon::now(),
                ],
                [
                    'name' => 'Twitch канал',
                    'url' => 'https://www.twitch.tv/keyntr',
                    'target' => 'blank',
                    'menu_type_id' => DB::table('menu_types')->where('code', 'footer_column_1')->first()->id,
                    'link_type' => null,
                    'created_at' => Carbon::now(),
                ],
                [
                    'name' => 'GoodGame канал',
                    'url' => 'https://goodgame.ru/channel/KeynTR/',
                    'target' => 'blank',
                    'menu_type_id' => DB::table('menu_types')->where('code', 'footer_column_1')->first()->id,
                    'link_type' => null,
                    'created_at' => Carbon::now(),
                ],
                [
                    'name' => 'Trovo канал',
                    'url' => 'https://trovo.live/KeynTR',
                    'target' => 'blank',
                    'menu_type_id' => DB::table('menu_types')->where('code', 'footer_column_1')->first()->id,
                    'link_type' => null,
                    'created_at' => Carbon::now(),
                ],
                [
                    'name' => 'WASD канал',
                    'url' => 'https://wasd.tv/keyntr',
                    'target' => 'blank',
                    'menu_type_id' => DB::table('menu_types')->where('code', 'footer_column_1')->first()->id,
                    'link_type' => null,
                    'created_at' => Carbon::now(),
                ],
                [
                    'name' => 'Discord',
                    'url' => 'https://discord.gg/JwAcAPtWpq',
                    'target' => 'blank',
                    'menu_type_id' => DB::table('menu_types')->where('code', 'footer_column_2')->first()->id,
                    'link_type' => null,
                    'created_at' => Carbon::now(),
                ],
                [
                    'name' => 'Телеграм',
                    'url' => 'https://t.me/KeynTR',
                    'target' => 'blank',
                    'menu_type_id' => DB::table('menu_types')->where('code', 'footer_column_2')->first()->id,
                    'link_type' => null,
                    'created_at' => Carbon::now(),
                ],
                [
                    'name' => 'Чат в Телеграм',
                    'url' => 'https://t.me/KeynTRChat',
                    'target' => 'blank',
                    'menu_type_id' => DB::table('menu_types')->where('code', 'footer_column_2')->first()->id,
                    'link_type' => null,
                    'created_at' => Carbon::now(),
                ],
                [
                    'name' => 'ВКонтакте',
                    'url' => 'https://vk.com/keyntr',
                    'target' => 'blank',
                    'menu_type_id' => DB::table('menu_types')->where('code', 'footer_column_2')->first()->id,
                    'link_type' => null,
                    'created_at' => Carbon::now(),
                ],
                [
                    'name' => 'Яндекс.Дзен',
                    'url' => 'https://zen.yandex.ru/id/6234c28c365c026c3020f3bb',
                    'target' => 'blank',
                    'menu_type_id' => DB::table('menu_types')->where('code', 'footer_column_2')->first()->id,
                    'link_type' => null,
                    'created_at' => Carbon::now(),
                ],
                [
                    'name' => 'RuTube',
                    'url' => 'https://rutube.ru/channel/1368202/',
                    'target' => 'blank',
                    'menu_type_id' => DB::table('menu_types')->where('code', 'footer_column_2')->first()->id,
                    'link_type' => null,
                    'created_at' => Carbon::now(),
                ],
                [
                    'name' => 'Donationalerts',
                    'url' => 'http://www.donationalerts.ru/r/keyntr',
                    'target' => 'blank',
                    'menu_type_id' => DB::table('menu_types')->where('code', 'footer_column_3')->first()->id,
                    'link_type' => null,
                    'created_at' => Carbon::now(),
                ],
                [
                    'name' => 'Карта сайта',
                    'url' => '/site_map/',
                    'target' => null,
                    'menu_type_id' => DB::table('menu_types')->where('code', 'footer_column_4')->first()->id,
                    'link_type' => 'route',
                    'created_at' => Carbon::now(),
                ],
                [
                    'name' => 'Связаться с автором сайта',
                    'url' => '/site_map/',
                    'target' => null,
                    'menu_type_id' => DB::table('menu_types')->where('code', 'footer_column_4')->first()->id,
                    'link_type' => 'route',
                    'created_at' => Carbon::now(),
                ],
            ]
        );
    }
}
