<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\MenuType;
use Illuminate\Http\Request;

class AdminMenuTypesPagesController extends Controller {
    protected $model = MenuType::class;

    public function index(MenuType $menuType) {
        $menuTypes = $menuType::all();

        return view('admin.menu-types.index', compact('menuTypes'));
    }

    public function create() {
        return view('admin.menu-types.form');
    }

    public function store(Request $request)
    {
        $params = $request->all();

        MenuType::create($params);

        return redirect()->route('admin.menu-types.index');
    }

    public function update(Request $request, MenuType $menuType) {
        $params = $request->all();

        $menuType->update($params);
        return redirect()->route('admin.menu-types.index');
    }

    public function edit(MenuType $menuType)
    {
        return view('admin.menu-types.form', compact('menuType'));
    }
}
