<?php

namespace App\Services;

use BaconQrCode\Writer;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Filesystem\Filesystem;

class QrCodeService
{
    /** @var Filesystem */
    protected $disk;

    /** @return string url */
    public function makeSvgUrl(string $message): string
    {
        $path = $this->makeSvgFile($message);

        return $this->disk()->url('qr/' . $path);
    }

    /** @return string path */
    public function makeSvgFile(string $message): string
    {
        $content = $this->makeSvg($message);

        return $this->store($content, 'svg');
    }

    public function makeSvg(string $message): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle(400),
            new SvgImageBackEnd()
        );
        return (new Writer($renderer))->writeString($message);
    }

    protected function store(string &$content, string $extension): string
    {
        $path = Str::random(64) . '.' . $extension;
        $this->disk()->put('qr/' . $path, $content);
        return $path;
    }

    protected function disk(): Filesystem
    {
        if (!$this->disk) {
            $this->disk = Storage::disk('public');
        }
        return $this->disk;
    }
}
