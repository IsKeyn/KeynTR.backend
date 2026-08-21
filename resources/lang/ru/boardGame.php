<?php

return [
    'user_not_found' => 'Пользователь не найден',

    'not_found' => 'Ивент не найден',
    'not_received_slug' => 'Slug ивента не получен',
    'not_found_or_not_active' => 'Ивент не найден или не активен',

    'registration_is_close' => 'Регистрация на ивент закрыта',
    'is_finish' => 'Ивент закончился',
    'coming_soon' => 'Ивент скоро начнется',

    'not_received_type' => 'Тип не получен',
    'board_type_not_found' => 'В ивенте отсуствует настройка board_type, указывающая на тип игрового поля',

    'timer_not_found' => 'Таймер не найден',
    'timer_settings_not_received' => 'Настройки не получены',
    'timer_settings_updated' => 'Настройки обновлены',

    'setting' => [
      'debug_disable' => 'Дебаг мод отключен',
    ],

    'player' => [
        'not_found' => 'Игрок не найден',
        'player_not_received' => 'Игрок не получен',
        'you_must_participate_in_event' => 'Вы должны участвовать в этом ивенте, чтобы иметь доступ к данному функционалу',
        'you_must_be_active_player' => 'Вы должны быть активным участником ивента, причина не активности: :message',
        'already_in_event' => 'Данный пользователь уже участвует в ивенте',
        'on_moderate' => 'Ожидает одобрения модератора',
        'request_on_moderate' => 'Заявка на участие успешно отправлена и рассматривается модераторами',
        'applied_to_participate_in_the_event' => 'подал заявку на участие в ивенте',
        'success_registration_in_event' => 'Вы успешно зарегистрированы в ивенте',
        'joined_in_event' => 'присоединился к ивенту',
        'settings' => [
            'dont_received_setting_name' => 'Не получено название опции',
            'dont_received_setting_value' => 'Не получено значение опции',
            'to_many_exception_platforms' => 'Выбрано слишком много платформ',
        ],
        'dont_have_items_roll' => 'Нет ни одной доступной крутки рулетки',
        'inventory' => [
            'not_received_inventory_id' => 'Не получен ID предмета инвентаря',
            'not_found' => 'Элемент инвентаря не найден',
            'update_failed' => 'Обновление элемент инвентаря не удалось',
            'create_failed' => 'Создание элемента инвентаря не удалось',
        ],
        'status_effect' => [
            'dont_received_se_id' => 'Не получен ID статус эффекта игрока',
            'dont_dont_have_or_inactive' => 'Статус эффект не наложен или он более не активен',
            'not_found_or_inactive' => 'Статус эффект не найден или он более не активен',
            'accepted' => 'принял действие статус эффекта :name',
            'denied' => 'отказался от статус эффекта :name',
            'successfully_applied' => 'Успешно применено',
        ],
        'interactions' => [
            'created_by_not_found' => 'Не найден игрок создавший взаимодействие',
            'with_player_not_found' => 'Не найден игрок для взаимодействия',
        ],
    ],

    'status_effect' => [
        'dont_received_se_bind_id' => 'Не получен ID привязки статус эффекта',
        'dont_dont_have_or_inactive' => 'Не привязки не существует или она не активна',
        'dont_received_slug' => 'Не получен slug',
    ],

    'shop' => [
        'create_failed' => 'Не удалось создать элемент магазина',
        'success_saved' => 'Лот магазина успешно создан',
        'to_many_products_for_sale' => 'Вы не можете одновременно продавать более 3 предметов',
        'put_for_sale' => 'выставил на продажу :name',
        'shop_item_not_found' => 'Товар не найден в магазине',
        'not_enough_points' => 'Не достаточно очков для приобретения товара',
        'buy_item_log' => 'купил предмет ":name" и потратил на это :points очков',
        'sell_item_log' => 'продал предмет ":name" и получил за него :points очков',
        'sell_item_notification_message' => 'Вы продали предмет ":name" и получил за него :points очков',
        'successful_purchase' => 'Предмет ":name" был успешно приобретен за :points очков',
        'withdrawn_item' => 'снял с продажи предмет ":name"',
        'successful_withdrawn' => 'Предмет ":name" был успешно снят с продажи и возвращен в ваш инвентарь',
    ],

    'board' => [
        'not_found' => 'Игровое поле не найдено',
        'position_not_received' => 'Номер позиции не получен',
    ],

    'player_game' => [
        'cant_roll_new_game_because_finish_board' => 'Вы не можете крутить игру, так как достигли последней клетки игрового поля',
        'cant_roll_new_game_because_finish_timer' => 'Вы не можете крутить игру, так как исчерпали время таймера',
        'you_must_use_item_rolls_and_board_steps' => 'Перед круткой рулетки игр вы должны использовать доступные крутки рулетки предметов, а такж использовать доступные ходы на игровом поле',
        'dont_have_game_for_roll' => 'У вас не осталось игр, для рулетки',
        'choice_game_error' => 'Ошибка выбора игры',
        'create_current_game_error' => 'Ошибка создания новой текущей игры',
        'roll_game_and_now_play' => 'Крутанул рулетку и выбил игру :name',
        'cant_roll_new_game_because_have_so_many_negative_points' => 'Вы не можете крутить игру, так как достигли максимального количества отрицательных очков, у вас должно быть больше :negativePoints, у вас сейчас :playerPoints',
    ],

    'add_game' => [
      'already_added' => 'Вы уже добавили игры в ивент',
      'can_add' => 'Вы можете добавить игры в ивент',
      'cant_add' => 'Вы не можете добавлять игры в ивент',
    ],
];
