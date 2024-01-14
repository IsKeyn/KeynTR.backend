<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminMediaPagesController extends Controller
{
    public function index() {
        $medias = Media::all();

        return view('admin.media.index', compact('medias'));
    }

    public function store(Request $request) {
        // Получаем запись с максимальным id
        $mediaCount = Media::query()->count();
        $name = $request->file('image')->hashName();
        $path = $request->file('image')->storeAs(
            'media/' . ++$mediaCount,
            $name,
            'public'
        );

        $fileData = [
            'name' => null,
            'description' => null,
            'type' => null,
            'file_name' => $name,
            'mime_type' => $request->file('image')->extension(),
            'size' => Storage::size('public/' . $path),
        ];

        Media::create($fileData);

        $medias = Media::all();

        return view('admin.media.index', compact('medias'));
    }
}
