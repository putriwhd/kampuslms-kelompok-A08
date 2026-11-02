<?php

namespace App\Providers;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Material;
use App\Models\Submission;
use App\Models\User;
use App\Policies\MaterialPolicy;
use App\Policies\SubmissionPolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Cache;
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
        Model::preventLazyLoading(! $this->app->isProduction());

        Gate::policy(Material::class, MaterialPolicy::class);
        Gate::policy(Submission::class, SubmissionPolicy::class);

        foreach ([User::class, Course::class, Material::class, Assignment::class, Submission::class, Grade::class] as $model) {
            $model::saved(fn () => $this->invalidateDashboardCache());
            $model::deleted(fn () => $this->invalidateDashboardCache());
        }
    }

    private function invalidateDashboardCache(): void
    {
        $version = (int) Cache::get('dashboard_stats_version', 1);
        Cache::forever('dashboard_stats_version', $version + 1);
    }
}