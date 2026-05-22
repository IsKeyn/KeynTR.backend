<?php

use Illuminate\Support\Facades\Route;

Route::prefix('admin/')->name('v1.')->group(function() {
    Route::name('setting.')->middleware(['auth:sanctum', 'can:site.edit'])->group(base_path('routes/api/admin/fragments/Setting.php'));

    Route::name('user.')->middleware(['auth:sanctum', 'can:user.edit'])->group(base_path('routes/api/admin/fragments/User.php'));
    Route::name('notification.')->middleware(['auth:sanctum', 'can:user.notification.edit'])->group(base_path('routes/api/admin/fragments/Notification.php'));
    Route::name('role.')->middleware(['auth:sanctum', 'can:user.roles.edit'])->group(base_path('routes/api/admin/fragments/Role.php'));
    Route::name('permission.')->middleware(['auth:sanctum', 'can:user.permission.edit'])->group(base_path('routes/api/admin/fragments/Permission.php'));

    Route::name('version.')->middleware(['auth:sanctum', 'is_admin'])->group(base_path('routes/api/admin/fragments/Version.php'));
    Route::name('entity.')->middleware(['auth:sanctum', 'is_admin'])->group(base_path('routes/api/admin/fragments/Entity.php'));

    Route::name('param.')->middleware(['auth:sanctum', 'is_admin'])->group(base_path('routes/api/admin/fragments/Param.php'));
    Route::name('magic-link.')->middleware(['auth:sanctum', 'is_admin'])->group(base_path('routes/api/admin/fragments/MagicLink.php'));
    Route::name('menu.')->middleware(['auth:sanctum', 'can:menu.edit'])->group(base_path('routes/api/admin/fragments/Menu.php'));
    Route::name('slide.')->middleware(['auth:sanctum', 'can:slide.edit'])->group(base_path('routes/api/admin/fragments/Slide.php'));
    Route::name('recommendation.')->middleware(['auth:sanctum', 'can:recommend.edit'])->group(base_path('routes/api/admin/fragments/Recommendation.php'));

    Route::name('media.')->middleware(['auth:sanctum', 'can:media.edit'])->group(base_path('routes/api/admin/fragments/Media.php'));
    Route::name('tag.')->middleware(['auth:sanctum', 'can:tags.edit'])->group(base_path('routes/api/admin/fragments/Tag.php'));
    Route::name('media-group.')->middleware(['auth:sanctum', 'can:media-group.edit'])->group(base_path('routes/api/admin/fragments/MediaGroup.php'));
    Route::name('series.')->middleware(['auth:sanctum', 'can:series.edit'])->group(base_path('routes/api/admin/fragments/Series.php'));
    Route::name('person.')->middleware(['auth:sanctum', 'can:person.edit'])->group(base_path('routes/api/admin/fragments/Person.php'));
    Route::name('company.')->middleware(['auth:sanctum', 'can:company.edit'])->group(base_path('routes/api/admin/fragments/Company.php'));
    Route::name('group.')->middleware(['auth:sanctum', 'can:group.edit'])->group(base_path('routes/api/admin/fragments/Group.php'));
    Route::name('genre.')->middleware(['auth:sanctum', 'can:genre.edit'])->group(base_path('routes/api/admin/fragments/Genre.php'));
    Route::name('gaming-platform.')->middleware(['auth:sanctum', 'can:gaming-platform.edit'])->group(base_path('routes/api/admin/fragments/GamingPlatform.php'));

    Route::name('page.')->middleware(['auth:sanctum', 'can:page.edit'])->group(base_path('routes/api/admin/fragments/Page.php'));
    Route::name('article.')->middleware(['auth:sanctum', 'can:article.edit'])->group(base_path('routes/api/admin/fragments/Article.php'));
    Route::name('game.')->middleware(['auth:sanctum', 'can:game.edit'])->group(base_path('routes/api/admin/fragments/Game.php'));
    Route::name('movie.')->middleware(['auth:sanctum', 'can:movie.edit'])->group(base_path('routes/api/admin/fragments/Movie.php'));

    Route::name('BoardGame.')->middleware(['auth:sanctum'])->group(base_path('routes/api/admin/fragments/BoardGame.php'));

    Route::name('api.')->middleware(['auth:sanctum'])->group(base_path('routes/api/admin/fragments/Api.php'));
});
