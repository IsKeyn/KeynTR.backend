<?php

namespace App\Services;

use App\Models\Seo;

class RelatedDataService
{
    public function set($model, $validated)
    {
        if (isset($validated['title_image']) || isset($validated['covers'])  || isset($validated['avatar'])) {
            $mediaService = new MediaService();

            if (isset($validated['title_image'])) {
                $mediaService->setTitleImage($model, $validated['title_image']);
            }

            if (isset($validated['covers'])) {
                $mediaService->setCovers($model, $validated['covers']);
            }

            if (isset($validated['avatar'])) {
                $mediaService->setAvatar($model, $validated['avatar']);
            }
        }

        if (isset($validated['sound'])) {
            $mediaService = new MediaService();

            if (isset($validated['sound'])) {
                $mediaService->setSound($model, $validated['sound']);
            }
        }

        if (isset($validated['additional_fields'])) {
            $additionalFieldsService = new AdditionalFieldsService();
            $additionalFieldsService->sync($model, $validated['additional_fields']);
        }

        if (isset($validated['settings'])) {
            $settingService = new SettingService();
            $settingService->sync($model, $validated['settings']);
        }

        if (isset($validated['series'])) {
            SeriesService::set($model, $validated['series']);
        }

        if (isset($validated['people'])) {
            PersonService::set($model, $validated['people']);
        }

        if (isset($validated['characters'])) {
            CharacterService::set($model, $validated['characters']);
        }

        if (isset($validated['groups'])) {
            GroupService::set($model, $validated['groups']);
        }

        if (isset($validated['genres'])) {
            GenreService::set($model, $validated['genres']);
        }

        if (isset($validated['companies'])) {
            CompanyService::set($model, $validated['companies']);
        }

        if (isset($validated['tags'])) {
            TagService::attacheTagsToEntity($model, $validated['tags']);
        }

        if (isset($validated['seo']) && $validated['seo']) {
            if ($model->seo) {
                if (isset($validated['seo']['updated_at'])) unset($validated['seo']['updated_at']);
                if (isset($validated['seo']['created_at'])) unset($validated['seo']['created_at']);

                $model->seo()->update($validated['seo']);
            } else {
                $meta = new Seo($validated['seo']);
                $model->seo()->save($meta);
            }
        }

        if (isset($validated['anons_dates'])) {
            GameService::setAnonsDates($model, $validated['anons_dates']);
        }

        if (isset($validated['release_dates'])) {
            GameService::setReleaseDates($model, $validated['release_dates']);
        }

        if (isset($validated['links'])) {
            LinkService::set($model, $validated['links']);
        }

        if (isset($validated['blocks'])) {
            BlockService::set($model, $validated['blocks']);
        }

        if (isset($validated['game'])) {
            GameService::set($model, $validated['game']);
        }

        if (isset($validated['roles'])) {
            RoleService::set($model, $validated['roles']);
        }

        if (isset($validated['permissions'])) {
            PermissionService::set($model, $validated['permissions']);
        }
    }
}
