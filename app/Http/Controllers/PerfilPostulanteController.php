<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePerfilPostulanteRequest;
use App\Models\PerfilPostulante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Arr;

class PerfilPostulanteController extends Controller
{
    public function create(Request $request)
    {
        // Relación hasOne en User: user()->perfilPostulante
        $perfil = $request->user()->perfilPostulante;
        return view('perfil.create', compact('perfil'));
    }

    public function store(StorePerfilPostulanteRequest $request)
    {
        Log::info('PerfilPostulanteController@store: IN', [
            'user_id' => $request->user()->id,
        ]);

        // Datos validados por el FormRequest
        $data = $request->validated();
        Log::info('PerfilPostulanteController@store: validated', $data);

        // Normalización por si el FormRequest aún no lo hace:
        // - Mapear fecha_nac -> fecha_nacimiento
        if (array_key_exists('fecha_nac', $data)) {
            $data['fecha_nacimiento'] = $data['fecha_nac'];
            unset($data['fecha_nac']);
        }

        // JAMÁS persistimos edad: se calcula en cliente o on-the-fly
        unset($data['edad']);

        // Si en algún momento existió "genero", también lo ignoramos
        unset($data['genero']);

        // Forzamos el owner correcto
        $data['user_id'] = $request->user()->id;

        // Filtrar estrictamente a los campos fillable del modelo para evitar mass-assignment indeseado
        $fillable = (new PerfilPostulante())->getFillable();
        $whitelist = array_unique(array_merge($fillable, ['user_id']));
        $data = Arr::only($data, $whitelist);

        try {
            $perfil = PerfilPostulante::updateOrCreate(
                ['user_id' => $data['user_id']],
                $data
            );

            Log::info('PerfilPostulanteController@store: OK', [
                'perfil_id' => $perfil->id,
                'user_id'   => $perfil->user_id,
            ]);
        } catch (\Throwable $e) {
            Log::error('PerfilPostulanteController@store: ERROR', [
                'msg'   => $e->getMessage(),
                'code'  => $e->getCode(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()
                ->withErrors('No se pudo guardar la ficha: ' . $e->getMessage())
                ->withInput();
        }

        // Redirección final (ajusta si quieres volver a la propia ficha)
        $to = session('postular_redirect_to') ?: route('dashboard');
        session()->forget('postular_redirect_to');

        return redirect($to)->with('status', 'Ficha guardada correctamente.');
    }
}
