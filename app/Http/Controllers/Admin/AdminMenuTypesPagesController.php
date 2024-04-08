<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\MenuType;
use Illuminate\Http\Request;

class AdminMenuTypesPagesController extends Controller {
    protected $model = MenuType::class;

    public function index(Request $request, MenuType $menuType) {
        $menuTypes = $menuType::all();

        if ($request->getPathInfo() === "/admin/menu-types") {
            return view('admin.menu-types.index', compact('menuTypes'));
        } else {
            return $menuTypes;
        }
    }

    public function create() {
        return view('admin.menu-types.form');
    }

    public function store(Request $request)
    {
        $params = $request->all();

        return MenuType::create($params);
    }

    public function update(Request $request, MenuType $menuType) {
        $params = $request->all();

        return $menuType->update($params);
//        return redirect()->route('admin.menu-types.index');
    }

    public function edit(MenuType $menuType)
    {
        return $menuType;
//        return view('admin.menu-types.form', compact('menuType'));
    }

    public function destroy(MenuType $menuType) {
        return $menuType->delete();
    }
}
