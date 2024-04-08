<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\MenuType;
use Illuminate\Http\Request;

class AdminMenuPagesController extends Controller {
    protected $model = Menu::class;

    public function index(Menu $menu) {
        return $menu::all();
    }

    public function create() {
        $menuTypes = MenuType::query()->get();

        return view('admin.menu.form', compact('menuTypes'));
    }

    public function store(Request $request)
    {
        $params = $request->all();

        return Menu::create($params);
    }

    public function update(Request $request, Menu $menu) {
        $params = $request->all();

        return $menu->update($params);
    }

    public function edit(Menu $menu)
    {
        return $menu;
    }

    public function destroy(Menu $menu) {
        return $menu->delete();
    }
}
