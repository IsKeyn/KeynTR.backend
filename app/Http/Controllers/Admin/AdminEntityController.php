<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Entity\EntityListResource;
use App\Services\CacheService;
use App\Services\MediaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Database\Eloquent\Model;

class AdminEntityController extends Controller {
    /*
     * Контроллер админки для управления любой сущностью
     */

    public const ENTITY_WITH_BINDS = [
        'Tag'
    ];

    public function index($firstParam, $secondParam = null)
    {
        if ($secondParam) {
            $entityFolder = $firstParam;
            $entityName = $secondParam;

            $model = 'App\Models\\' . $entityFolder . '\\' . $entityName;
        } else {
            $entityName = $firstParam;

            $model = 'App\Models\\' . $entityName;
        }

        return $model::query()->get();
    }

    public function store(Request $request, $firstParam, $secondParam = null)
    {
        if ($secondParam) {
            $entityFolder = $firstParam;
            $entityName = $secondParam;

            $model = 'App\Models\\' . $entityFolder . '\\' . $entityName;
        } else {
            $entityName = $firstParam;

            $model = 'App\Models\\' . $entityName;
        }

        $params = $request->all();

        $entity = $model::create($params);

        $mediaService = new MediaService();

        if (isset($params['title_image'])) {
            $mediaService->setTitleImage($entity, $params['title_image']);
        }

//        $mediaService = new MediaService();

//        if (isset($params['gallery'])) {
//            $mediaService->setGallery($entity, $params['gallery']);
//        }

        /* Сброс кеша */
        if (isset($entityName) && isset($entityFolder)) {
            CacheService::setJobForDelete(
                $entityName,
                $entityFolder,
                isset($params['slug']) ? $params['slug'] : null,
                isset($params['ended_at']) ? $params['ended_at'] : null,
            );
        }

        return $entity;
    }

    public function update(Request $request, $firstParam, $secondParam = null, $thirdParam = null) {
        if ($thirdParam) {
            $entityFolder = $firstParam;
            $entityName = $secondParam;
            $id = $thirdParam;

            $model = 'App\Models\\' . $entityFolder . '\\' . $entityName;
        } else {
            $entityName = $firstParam;
            $id = $secondParam;

            $model = 'App\Models\\' . $entityName;
        }

        if ($id) {
            if ($entity = $model::where('id', $id)->first()) {
                $params = $request->all();

                $mediaService = new MediaService();

                if (isset($params['title_image'])) {
                    $mediaService->setTitleImage($entity, $params['title_image']);
                }

                if (isset($params['image'])) {
                    $mediaService->setTitleImage($entity, $params['image']);
                }

//                $mediaService = new MediaService();
//
//                if (isset($params['gallery'])) {
//                    $mediaService->setGallery($entity, $params['gallery']);
//                }
                /* Сброс кеша */
                if (isset($entityName) && isset($entityFolder)) {
                    CacheService::forgetEntityCache(
                        $entityName,
                        $entityFolder,
                        isset($params['slug']) ? $params['slug'] : null,
                        isset($params['ended_at']) ? $params['ended_at'] : null,
                        $entity->ended_at,
                    );
                }

                return $entity->update($params);
            } else {
                echo 'Такой сущности нет'; // TODO Сделать общий вывод ошибок, типа error();
            }
        } else {
            echo 'Не получен ID сущности'; // TODO Сделать общий вывод ошибок, типа error();
        }
    }

    public function edit($firstParam, $secondParam = null, $thirdParam = null) {
        if ($thirdParam) {
            $entityFolder = $firstParam;
            $entityName = $secondParam;
            $id = $thirdParam;

            $model = 'App\Models\\' . $entityFolder . '\\' . $entityName;
        } else {
            $entityName = $firstParam;
            $id = $secondParam;

            $model = 'App\Models\\' . $entityName;
        }

        return $model::query()->where('id', $id)->first();
//        $data = [
//            'element' => $model::query()->where('id', $id)->first(),
//            'name' => $entityName,
//            'editableFields' => $model::EDITABLE_FIELDS
//        ];
//
//        return view('admin.entity.form', compact('data'));
    }

    public function destroy($firstParam, $secondParam = null, $thirdParam = null) {
        if ($thirdParam) {
            $entityFolder = $firstParam;
            $entityName = $secondParam;
            $id = $thirdParam;

            $model = 'App\Models\\' . $entityFolder . '\\' . $entityName;
        } else {
            $entityName = $firstParam;
            $id = $secondParam;

            $model = 'App\Models\\' . $entityName;
        }

        $query = $model::query()->where('id', $id)->first();

        // TODO возможно правильнее поискать решение через метолы Laravel
        // Удаление связанных элементов
        if (in_array($entityName, self::ENTITY_WITH_BINDS)) {
            $bindTableName = strtolower($entityName . '_binds');
            $tableColumnName = strtolower($entityName . '_id');

            DB::table($bindTableName)->where($tableColumnName, $id)->delete();
        }

        return $query->delete();
    }

    public function detail($entityName) {
        $model = 'App\Models\\' . $entityName;
        $data = [
            'list' => $model::query()->get(),
            'name' => $entityName
        ];

        return view('admin.entity.detail', compact('data'));
    }

    public function add($entityName) {
        $model = 'App\Models\\' . $entityName;

        $data = [
            'name' => $entityName,
            'editableFields' => $model::EDITABLE_FIELDS
        ];

        return view('admin.entity.form', compact('data'));
    }


    public function storeAdditionalField(Request $request, $firstParam, $secondParam = null, $thirdParam = null) {
        if ($thirdParam) {
            $entityFolder = $firstParam;
            $entityName = $secondParam;
            $id = $thirdParam;

            $model = 'App\Models\\' . $entityFolder . '\\' . $entityName;
        } else {
            $entityName = $firstParam;
            $id = $secondParam;

            $model = 'App\Models\\' . $entityName;
        }

        if ($id) {
            if ($element = $model::where('id', $id)->first()) {
                $params = $request->all();

                $element->additionalFields()->create($params);
            } else {
                echo 'Такой сущности нет'; // TODO Сделать общий вывод ошибок, типа error();
            }
        } else {
            echo 'Не получен ID сущности'; // TODO Сделать общий вывод ошибок, типа error();
        }


        return redirect()->route('admin.entity.', $entityName);
    }

    public function updateAdditionalField(Request $request, $firstParam, $secondParam = null, $thirdParam = null) {
        if ($thirdParam) {
            $entityFolder = $firstParam;
            $entityName = $secondParam;
            $id = $thirdParam;

            $model = 'App\Models\\' . $entityFolder . '\\' . $entityName;
        } else {
            $entityName = $firstParam;
            $id = $secondParam;

            $model = 'App\Models\\' . $entityName;
        }

        if ($id) {
            if ($element = $model::where('id', $id)->first()) {
                $params = $request->all();

                $element->additionalFields()->where('id', $params['id'])->first()->update($params);
            } else {
                echo 'Такой сущности нет'; // TODO Сделать общий вывод ошибок, типа error();
            }
        } else {
            echo 'Не получен ID сущности'; // TODO Сделать общий вывод ошибок, типа error();
        }


        return redirect()->route('admin.entity.', $entityName);
    }

    public function deleteAdditionalField(Request $request, $firstParam, $secondParam = null, $thirdParam = null) {
        if ($thirdParam) {
            $entityFolder = $firstParam;
            $entityName = $secondParam;
            $id = $thirdParam;

            $model = 'App\Models\\' . $entityFolder . '\\' . $entityName;
        } else {
            $entityName = $firstParam;
            $id = $secondParam;

            $model = 'App\Models\\' . $entityName;
        }

        if ($id) {
            if ($element = $model::where('id', $id)->first()) {
                $params = $request->all();

                $element->additionalFields()->where('id', $params['id'])->first()->delete();
            } else {
                echo 'Такой сущности нет'; // TODO Сделать общий вывод ошибок, типа error();
            }
        } else {
            echo 'Не получен ID сущности'; // TODO Сделать общий вывод ошибок, типа error();
        }

        return redirect()->route('admin.entity.', $entityName);
    }

    public function getEntityList($directory = null)
    {
        $directory = $directory ?? app_path('Models');
        $files = File::allFiles($directory);

        $models = collect(); // Создаем пустую коллекцию

        foreach ($files as $file) {
            // Получаем namespace на основе пути файла
            $relativePath = str_replace([app_path(), '.php'], '', $file->getRealPath());
            $className = 'App' . str_replace('/', '\\', $relativePath);

            if (class_exists($className) && is_subclass_of($className, Model::class)) {
                $models->push([ // Используем push() для добавления элемента в коллекцию
                    'name' => $className,
                    'value' => $className,
                ]);
            }
        }

        return $models;
    }

    public function getFields(Request $request)
    {
        if ($model = $request->entity) {
            return $model::all();
        }
    }
}
