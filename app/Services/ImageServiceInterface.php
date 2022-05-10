<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;

interface ImageServiceInterface
{
    public function saveImage(string $path, UploadedFile $image): string;

    public function deleteImage(string $path): bool;

    public function isImagesAreSame(string $firstImageContent, string $secondImageContent): bool;

    public function getImgContentByPath($path): string;
}
