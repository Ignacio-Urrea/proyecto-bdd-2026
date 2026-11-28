<?php

namespace App\Providers;

use Illuminate\Foundation\Console\ServeCommand;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        if (! $this->app->runningInConsole() || ! in_array('serve', $_SERVER['argv'] ?? [], true)) {
            return;
        }

        $phpIni = php_ini_loaded_file();

        if ($phpIni === false) {
            return;
        }

        $phpIniDirectory = dirname($phpIni);

        putenv('PHPRC='.$phpIniDirectory);
        $_ENV['PHPRC'] = $phpIniDirectory;
        $_SERVER['PHPRC'] = $phpIniDirectory;

        if (! in_array('PHPRC', ServeCommand::$passthroughVariables, true)) {
            ServeCommand::$passthroughVariables[] = 'PHPRC';
        }
    }
}
