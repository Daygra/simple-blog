<?php

namespace App\Providers;

use App\Models\User;
use App\Services\CommentCRUDService;
use App\Services\CommentCRUDServiceInterface;
use App\Services\ImageService;
use App\Services\ImageServiceInterface;
use App\Services\PostCRUDService;
use App\Services\PostCRUDServiceInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(PostCRUDServiceInterface::class,PostCRUDService::class);
        $this->app->bind(CommentCRUDServiceInterface::class,CommentCRUDService::class);
        $this->app->bind(ImageServiceInterface::class,ImageService::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
       // Auth::login(User::first());
    }
}
