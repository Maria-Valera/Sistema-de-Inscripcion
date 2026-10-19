<?php

namespace App\Providers;

use Livewire\Livewire;
use App\Livewire\Admin\InstitucionProcedenciaIndex;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
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
        Livewire::component('admin.institucion-procedencia-index', InstitucionProcedenciaIndex::class);

        Gate::policy(\App\Models\Alumno::class, \App\Policies\AlumnoPolicy::class);
        Gate::policy(\App\Models\Inscripcion::class, \App\Policies\InscripcionPolicy::class);
        Gate::policy(\App\Models\Nota::class, \App\Policies\NotaPolicy::class);
        Gate::policy(\App\Models\Inasistencia::class, \App\Policies\InasistenciaPolicy::class);

        Gate::define('ver-datos-sensibles-alumno', function (\App\Models\User $user) {
            return !$user->esSoloDocente();
        });
    }
}
