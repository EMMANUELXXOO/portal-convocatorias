<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

// Models
use App\Models\Convocatoria;

// Controllers público/aspirantes
use App\Http\Controllers\ConvocatoriaController;
use App\Http\Controllers\PostulacionController;
use App\Http\Controllers\PerfilPostulanteController;
use App\Http\Controllers\DashboardController; // Dashboard aspirante

// Controllers admin
use App\Http\Controllers\AdminConvocatoriaController;
use App\Http\Controllers\AdminPostulacionController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\AuditController;
use App\Http\Controllers\Admin\GrupoExamenController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController; // Dashboard admin

// Middleware
use App\Http\Middleware\EnsureApplicantProfile;

/*
|--------------------------------------------------------------------------
| Público
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    $today = now()->toDateString();

    $convocatorias = Convocatoria::query()
        ->where('estatus', 'activa')
        ->where(fn ($q) => $q->whereNull('fecha_fin')->orWhereDate('fecha_fin', '>=', $today))
        ->orderByRaw('ISNULL(fecha_inicio), fecha_inicio ASC')
        ->orderByDesc('id')
        ->withCount('postulaciones')
        ->paginate(12)
        ->withQueryString();

    return view('welcome', compact('convocatorias'));
})->name('home');

// Listado y detalle públicos
Route::resource('convocatorias', ConvocatoriaController::class)->only(['index','show']);

/*
|--------------------------------------------------------------------------
| Área autenticada (común)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth','verified'])->group(function () {
    Route::get('/dashboard', function (Request $request) {
        $user = $request->user();
        if ($user && $user->can('backoffice')) {
            return redirect()->route('admin.dashboard');
        }
        return app(DashboardController::class)($request);
    })->name('dashboard');

    // Ficha de aspirante
    Route::get('/perfil', [PerfilPostulanteController::class,'create'])->name('perfil.create');
    Route::post('/perfil', [PerfilPostulanteController::class,'store'])->name('perfil.store');

    // Aspirantes
    Route::middleware(['can:no-admin'])->group(function () {
        Route::post('/convocatorias/{convocatoria}/postular', [PostulacionController::class,'store'])
            ->middleware(EnsureApplicantProfile::class)->name('postulaciones.store');

        Route::post('/convocatorias/{convocatoria}/aplicar', [PostulacionController::class,'store'])
            ->middleware(EnsureApplicantProfile::class)->name('convocatorias.aplicar');

        Route::middleware(EnsureApplicantProfile::class)->group(function () {
            Route::get('/mis-postulaciones', [PostulacionController::class,'misPostulaciones'])->name('postulaciones.index');
            Route::get('/mis-postulaciones/{postulacion}/editar', [PostulacionController::class,'edit'])
                ->whereNumber('postulacion')->name('postulaciones.edit');
            Route::patch('/mis-postulaciones/{postulacion}', [PostulacionController::class,'update'])
                ->whereNumber('postulacion')->name('postulaciones.update');

            Route::get('/postulaciones/{postulacion}/pago', [PostulacionController::class,'pago'])
                ->whereNumber('postulacion')->name('postulaciones.pago');
            Route::post('/postulaciones/{postulacion}/confirmar-pago', [PostulacionController::class,'confirmarPago'])
                ->whereNumber('postulacion')->name('postulaciones.confirmar-pago');

            Route::get('/postulaciones/{postulacion}/recibo', [PostulacionController::class,'recibo'])
                ->whereNumber('postulacion')->name('postulaciones.recibo');
            Route::post('/postulaciones/{postulacion}/reenviar-recibo', [PostulacionController::class,'reenviarRecibo'])
                ->middleware('throttle:mail-resend')->whereNumber('postulacion')
                ->name('postulaciones.reenviar-recibo');

            Route::delete('/mis-postulaciones/{postulacion}', [PostulacionController::class,'destroy'])
                ->whereNumber('postulacion')->name('postulaciones.destroy');
        });
    });
});

/*
|--------------------------------------------------------------------------
| Área administrativa (Admin + Subadmin)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth','verified','can:backoffice'])
    ->prefix('admin')->as('admin.')
    ->group(function () {

        // Dashboard admin
        Route::get('/', [AdminDashboardController::class,'index'])->name('home');
        Route::get('/dashboard', [AdminDashboardController::class,'index'])->name('dashboard');

        // Convocatorias
        Route::resource('convocatorias', AdminConvocatoriaController::class)->except(['show']);

       // remove una imagen de la galería
Route::delete('convocatorias/{convocatoria}/remove-image/{index}',
    [AdminConvocatoriaController::class, 'removeImage'])
    ->whereNumber('convocatoria')->whereNumber('index')
    ->name('convocatorias.removeImage');

// remove la portada
Route::delete('convocatorias/{convocatoria}/remove-cover',
    [AdminConvocatoriaController::class, 'removeCover'])
    ->whereNumber('convocatoria')
    ->name('convocatorias.removeCover');


        // Postulaciones
        Route::get('/postulaciones', [AdminPostulacionController::class,'index'])->name('postulaciones.index');
        Route::get('/postulaciones/export', [AdminPostulacionController::class,'export'])->name('postulaciones.export');
        Route::get('/postulaciones/{postulacion}', [AdminPostulacionController::class,'show'])
            ->whereNumber('postulacion')->name('postulaciones.show');
        Route::patch('/postulaciones/{postulacion}', [AdminPostulacionController::class,'update'])
            ->whereNumber('postulacion')->name('postulaciones.update');
        Route::post('/postulaciones/{postulacion}/reenviar-recibo', [AdminPostulacionController::class,'reenviarRecibo'])
            ->whereNumber('postulacion')->name('postulaciones.reenviar-recibo');

        // Usuarios
        Route::resource('users', AdminUserController::class)->except(['show']);

        // Grupos de examen
        Route::resource('grupos', GrupoExamenController::class)->except(['show']);
        Route::get('grupos/{grupo}/asignar', [GrupoExamenController::class,'asignarForm'])
            ->whereNumber('grupo')->name('grupos.asignar.form');
        Route::post('grupos/{grupo}/asignar', [GrupoExamenController::class,'asignarStore'])
            ->whereNumber('grupo')->name('grupos.asignar.store');
        Route::delete('grupos/{grupo}/desasignar/{postulacion}', [GrupoExamenController::class,'desasignar'])
            ->whereNumber('grupo')->whereNumber('postulacion')->name('grupos.desasignar');
        Route::post('grupos/{grupo}/desasignar-masivo', [GrupoExamenController::class,'desasignarMasivo'])
            ->whereNumber('grupo')->name('grupos.desasignar_masivo');

        Route::get('grupos/{grupo}/asignados', [GrupoExamenController::class,'asignados'])
            ->whereNumber('grupo')->name('grupos.asignados');

        Route::get('grupos/{grupo}/export-asistencia', [GrupoExamenController::class,'exportAsistencia'])
            ->whereNumber('grupo')->name('grupos.export_asistencia');
        Route::get('grupos/{grupo}/export', [GrupoExamenController::class,'exportAsistencia'])
            ->whereNumber('grupo')->name('grupos.export');
        Route::get('grupos/{grupo}/asistencia.csv', [GrupoExamenController::class,'exportAsistencia'])
            ->whereNumber('grupo')->name('grupos.export_csv');

        Route::match(['POST','DELETE'], 'grupos/{grupo}/notificar', [GrupoExamenController::class,'notificarSeleccion'])
            ->whereNumber('grupo')->name('grupos.notificar');

        // Settings / Auditoría (solo admin)
        Route::middleware('can:manage-settings')->group(function () {
            Route::get('settings', [SettingsController::class,'edit'])->name('settings.edit');
            Route::post('settings', [SettingsController::class,'update'])->name('settings.update');
            Route::post('settings/test-email', [SettingsController::class,'testEmail'])
                ->middleware('throttle:test-email')->name('settings.test-email');

            Route::get('audits', [AuditController::class,'index'])->name('audits.index');
        });
    });

// Auth y profile
require __DIR__.'/auth.php';
require __DIR__.'/profile.php';
