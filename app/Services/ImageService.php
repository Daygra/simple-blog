<?php

namespace App\Services;


use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ImageService implements ImageServiceInterface
{

    public function saveImage(string $path, UploadedFile $image)
    {
        return $image->store($path);
    }

    public function deleteImage(string $path): bool
    {
       return Storage::delete($path);
    }

    public function isImagesAreSame(string $firstImageContent, string $secondImageContent): bool
    {
        return hash('md5',$firstImageContent) === hash('md5',$secondImageContent);
    }
    public function getImgContentByPath($path): string
    {
     return Storage::get($path);
    }
}
