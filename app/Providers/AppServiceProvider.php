<?php

namespace App\Providers;

use App\Support\Translation\MergingFileLoader;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Translation\FileLoader;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->registerTranslationLoader();
    }

    /**
     * Swap the framework translation loader for one that merges group
     * subdirectories (e.g. lang/pt_BR/app/auth.php → app.auth.*) so dot
     * notation keeps working across split files.
     */
    protected function registerTranslationLoader(): void
    {
        $this->app->extend('translation.loader', function (FileLoader $loader, $app): MergingFileLoader {
            $merging = new MergingFileLoader($app['files'], $loader->paths());

            foreach ($loader->jsonPaths() as $jsonPath) {
                $merging->addJsonPath($jsonPath);
            }

            foreach ($loader->namespaces() as $namespace => $hint) {
                $merging->addNamespace($namespace, $hint);
            }

            return $merging;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        Model::preventLazyLoading(! app()->isProduction());

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
