<?php

namespace App\Providers;

use App\Services\Storage\StorageManager;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Storage;

class StorageServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(StorageManager::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Storage::extend('do_spaces', function () {
            $manager = app(StorageManager::class);

            if (!$manager->isDoSpacesConfigured()) {
                throw new \Exception('DO Spaces is not configured. Please set up your credentials in settings.');
            }

            $config = $manager->getDoSpacesConfig();

            return Storage::createS3Driver($config);
        });

        Storage::extend('google-drive', function () {
            $manager = app(StorageManager::class);

            if (!$manager->isGoogleDriveConfigured()) {
                throw new \Exception('Google Drive is not configured. Please set up your OAuth credentials in settings.');
            }

            return $manager->createGoogleDriveAdapter();
        });
    }
}
