<?php

namespace App\Providers;

use App\Models\Material;
use App\Policies\MaterialPolicy;
use App\Models\Submission;
use App\Policies\SubmissionPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

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
        Gate::policy(Material::class, MaterialPolicy::class);
        Gate::policy(Submission::class, SubmissionPolicy::class);
    }
}