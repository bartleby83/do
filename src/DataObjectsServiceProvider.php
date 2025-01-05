<?php

namespace DO\Main;

use DO\Tools\Commands\ConfigCommand;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

/**
 * ServiceProvider für das DO-Package.
 */
class DataObjectsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     */
    public function boot()
    {
        $loader = AliasLoader::getInstance();
        if (!array_key_exists('DataObject', $loader->getAliases())) {
            $loader->alias('DataObject', \DO\Main\DataObject::class);
        }
        // Blade-Templates registrieren
        $this->loadViewsFrom(__DIR__ . '/../resources/views/DataObjects', 'DO');

        // Assets veröffentlichen
        $this->publishes([
            __DIR__ . '/../public/assets/' => public_path('vendor/do'),
        ], 'assets');

        // Blade-Templates veröffentlichen
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/do'),
        ], 'views');
        
        // Command zur Veröffentlichung bereitstellen
        $this->publishes([
            __DIR__ . '/../tools/Commands/ConfigCommand.php' => app_path('Console/Commands/ConfigCommand.php'),
        ], 'commands');

        // Statische Assets direkt verfügbar machen
        $this->serveAssetsDirectly();
    }

    /**
     * Register any application services.
     */
    public function register(): void {
        // Command registrieren
        $this->commands([
            ConfigCommand::class,
        ]);

        // Alias registrieren
        $loader = AliasLoader::getInstance();
        $loader->alias('DataObject', \DO\Main\DataObject::class);
    }

    /**
     * Statische Assets direkt aus dem Package verfügbar machen.
     */
    protected function serveAssetsDirectly()
    {
        Route::get('/vendor/do/{file}', function ($file) {
            $path = __DIR__ . '/../public/assets/' . $file;

            if (!file_exists($path)) {
                abort(404);
            }

            $mimeType = mime_content_type($path);
            return response()->file($path, ['Content-Type' => $mimeType]);
        })->where('file', '.*')->middleware('web');
    }
}