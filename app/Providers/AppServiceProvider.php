<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        // Pakai tampilan pagination sederhana (tanpa Tailwind/Bootstrap), gayanya diatur di layouts.blade.php
        Paginator::useBootstrapFour();
    }
}
