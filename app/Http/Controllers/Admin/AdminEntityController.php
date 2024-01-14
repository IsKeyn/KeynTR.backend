<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminEntityController extends Controller {
    /*
     * Контроллер админки для управления любой сущностью
     */

    public function index() {
        return view('admin.entity.index');
    }

    public function detail($entityName) {
        $model = 'App\Models\\' . $entityName;
        $data = [
            'list' => $model::query()->get(),
            'name' => $entityName
        ];

        return view('admin.entity.detail', compact('data'));
    }

    public function edit($entityName, $id) {
        $model = 'App\Models\\' . $entityName;

        $data = [
            'element' => $model::query()->where('id', $id)->first(),
            'name' => $entityName,
            'editableFields' => $model::EDITABLE_FIELDS
        ];

        return view('admin.entity.form', compact('data'));
    }

    public function add($entityName) {
        $model = 'App\Models\\' . $entityName;

        $data = [
            'name' => $entityName,
            'editableFields' => $model::EDITABLE_FIELDS
        ];

        return view('admin.entity.form', compact('data'));
    }

    public function store($entityName, Request $request)
    {
        $model = 'App\Models\\' . $entityName;

        $params = $request->all();
        $model::create($params);

        return redirect()->route('admin.entity.', $entityName);
    }

    public function update($entityName, $id, Request $request) {
        $model = 'App\Models\\' . $entityName;

        if ($id) {
            if ($element = $model::where('id', $id)->first()) {
                $params = $request->all();

                $element->update($params);
            } else {
                echo 'Такой сущности нет'; // TODO Сделать общий вывод ошибок, типа error();
            }
        } else {
            echo 'Не получен ID сущности'; // TODO Сделать общий вывод ошибок, типа error();
        }


        return redirect()->route('admin.entity.', $entityName);
    }

    public function storeAdditionalField($entityName, $id, Request $request) {
        $model = 'App\Models\\' . $entityName;

        if ($id) {
            if ($element = $model::where('id', $id)->first()) {
                $params = $request->all();

                $element->fields()->create($params);
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

                $element->fields()->where('id', $params['id'])->first()->update($params);
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

                $element->fields()->where('id', $params['id'])->first()->delete();
            } else {
                echo 'Такой сущности нет'; // TODO Сделать общий вывод ошибок, типа error();
            }
        } else {
            echo 'Не получен ID сущности'; // TODO Сделать общий вывод ошибок, типа error();
        }

        return redirect()->route('admin.entity.', $entityName);
    }
}
