<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;

class AdminPageController extends Controller {
    public function index(Page $page) {
        return $page::all();
    }

    public function store(Request $request)
    {
        $data = $request->all();

        $data['path'] = trim($data['path'], '/');

        return Page::create($data);
    }

    public function update(Request $request, Page $page) {
        $data = $request->all();

        $data['path'] = trim($data['path'], '/');

        return $page->update($data);
    }

    public function edit(Page $page)
    {
        return $page;
    }
}
