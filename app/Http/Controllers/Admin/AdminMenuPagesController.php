<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\MenuType;
use Illuminate\Http\Request;

class AdminMenuPagesController extends Controller {
    protected $model = Menu::class;

    public function index(Menu $menu) {
        $menuElements = $menu::all();

        return view('admin.menu.index', compact('menuElements'));
    }

    public function create() {
        $menuTypes = MenuType::query()->get();

        return view('admin.menu.form', compact('menuTypes'));
    }

    public function store(Request $request)
    {
        $params = $request->all();

        Menu::create($params);

        return redirect()->route('admin.menu.index');
    }

    public function update(Request $request, Menu $menu) {
        $params = $request->all();

        $menu->update($params);
        return redirect()->route('admin.menu.index');
    }

    public function edit(Menu $menu)
    {
        return view('admin.menu.form', compact('menu'));
    }
}
