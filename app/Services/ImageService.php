<?php

namespace App\Services;


use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ImageService implements ImageServiceInterface
{

    public function saveImage(string $path, UploadedFile $image, string $disk = 'public'): string
    {
        return $image->store($path,['disk' => $disk]);
    }

    public function deleteImage(string $path, string $disk = 'public'): bool
    {
        return Storage::disk($disk)->delete($path);
    }

    public function isImagesAreSame(string $firstImageContent, string $secondImageContent): bool
    {
        return  $firstImageContent === $secondImageContent;
    }

    public function getImgContentByPath($path, string $disk = 'public'): string
    {
        return Storage::disk($disk)->get($path );
    }
}
