<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\MediaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminEntityController extends Controller {
    /*
     * Контроллер админки для управления любой сущностью
     */

    public const ENTITY_WITH_BINDS = [
        'Tag'
    ];

    public function index($entityName)
    {
        $model = 'App\Models\\' . $entityName;

        return $model::query()->get();
    }

    public function store($entityName, Request $request)
    {
        $model = 'App\Models\\' . $entityName;
        $params = $request->all();

        $entity = $model::create($params);

//        $mediaService = new MediaService();

//        if (isset($params['gallery'])) {
//            $mediaService->setGallery($entity, $params['gallery']);
//        }

        return $entity;
    }

    public function update($entityName, $id, Request $request) {
        $model = 'App\Models\\' . $entityName;

        if ($id) {
            if ($entity = $model::where('id', $id)->first()) {
                $params = $request->all();

//                $mediaService = new MediaService();
//
//                if (isset($params['gallery'])) {
//                    $mediaService->setGallery($entity, $params['gallery']);
//                }

                return $entity->update($params);
            } else {
                echo 'Такой сущности нет'; // TODO Сделать общий вывод ошибок, типа error();
            }
        } else {
            echo 'Не получен ID сущности'; // TODO Сделать общий вывод ошибок, типа error();
        }
    }

    public function edit($entityName, $id) {
        $model = 'App\Models\\' . $entityName;

        return $model::query()->where('id', $id)->first();
//        $data = [
//            'element' => $model::query()->where('id', $id)->first(),
//            'name' => $entityName,
//            'editableFields' => $model::EDITABLE_FIELDS
//        ];
//
//        return view('admin.entity.form', compact('data'));
    }

    public function destroy($entityName, $id) {
        $model = 'App\Models\\' . $entityName;
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


    public function storeAdditionalField($entityName, $id, Request $request) {
        $model = 'App\Models\\' . $entityName;

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

    public function updateAdditionalField($entityName, $id, Request $request) {
        $model = 'App\Models\\' . $entityName;

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

    public function deleteAdditionalField($entityName, $id, Request $request) {
        $model = 'App\Models\\' . $entityName;

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
}
