<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePerfilPostulanteRequest;
use App\Mail\ReciboPagoPostulacion;
use App\Models\Convocatoria;
use App\Models\PerfilPostulante;
use App\Models\Postulacion;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PostulacionController extends Controller
{
    /**
     * POST /convocatorias/{convocatoria}/postular
     */
    public function store(Request $request, Convocatoria $convocatoria)
    {
        $this->authorize('postular', $convocatoria);

        // Si no tiene ficha, lo mandamos a crearla y guardamos a dónde volver
        if (! $request->user()->perfilPostulante) {
            session(['postular_redirect_to' => route('postulaciones.pago.placeholder', ['c' => $convocatoria->id])]);
            return redirect()
                ->route('perfil.create')
                ->with('status', 'Completa tu ficha para continuar.');
        }

        // Crear la postulación si no existe (user_id + convocatoria_id únicos)
        $postulacion = DB::transaction(function () use ($request, $convocatoria) {
            return Postulacion::firstOrCreate(
                [
                    'user_id'         => $request->user()->id,
                    'convocatoria_id' => $convocatoria->id,
                ],
                [
                    'estatus'         => Postulacion::ESTATUS_PAGO_PENDIENTE,
                    'folio'           => $this->generateUniqueFolio(),
                    'referencia_pago' => $this->generatePaymentReference($convocatoria->id, $request->user()->id),
                    'ip_registro'     => $request->ip(),
                    'agente'          => substr($request->userAgent() ?? '', 0, 255),
                ]
            );
        });

        return redirect()
            ->route('postulaciones.pago', $postulacion)
            ->with('status', "Folio: {$postulacion->folio}. Continúa con el pago de tu ficha.");
    }

    /**
     * GET /mis-postulaciones
     */
    public function misPostulaciones(Request $request)
    {
        $user = $request->user();

        $items = Postulacion::with([
                'convocatoria:id,titulo,fecha_inicio,fecha_fin',
                'gruposExamen:id,convocatoria_id,tipo,tipo_detalle,fecha_hora,lugar',
            ])
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(12);

        return view('postulaciones.index', compact('items'));
    }

    /**
     * GET /postulaciones/{postulacion}/pago
     */
    public function pago(Request $request, Postulacion $postulacion)
    {
        $this->authorize('view', $postulacion);

        if (! $postulacion->folio || ! $postulacion->referencia_pago) {
            $postulacion->update([
                'folio'           => $postulacion->folio ?? $this->generateUniqueFolio(),
                'referencia_pago' => $postulacion->referencia_pago
                    ?? $this->generatePaymentReference($postulacion->convocatoria_id, $postulacion->user_id),
            ]);
        }

        return view('postulaciones.pago', compact('postulacion'));
    }

    /**
     * POST /postulaciones/{postulacion}/confirmar-pago
     */
    public function confirmarPago(Request $request, Postulacion $postulacion)
    {
        $this->authorize('view', $postulacion);

        if ($postulacion->estatus === Postulacion::ESTATUS_PAGADO) {
            return back()->with('status', 'Este pago ya fue confirmado previamente.');
        }

        $data = $request->validate([
            'folio_banco' => ['required','string','max:64','unique:postulaciones,folio_banco'],
        ], [
            'folio_banco.unique' => 'Este folio bancario ya fue registrado.',
        ]);

        $postulacion->update([
            'estatus'     => Postulacion::ESTATUS_PAGADO,
            'fecha_pago'  => $postulacion->fecha_pago ?: now(),
            'folio_banco' => $data['folio_banco'],
        ]);

        $postulacion->loadMissing('convocatoria','user');

        // Enviar recibo (en cola, tras commit)
        Mail::to($request->user()->email, $request->user()->name ?? null)
            ->queue((new ReciboPagoPostulacion($postulacion->id))->afterCommit());

        if (empty($postulacion->recibo_enviado_at)) {
            $postulacion->recibo_enviado_at = now();
            $postulacion->save();
        }

        return redirect()
            ->route('postulaciones.index')
            ->with('status', 'Pago confirmado ✅. Recibo enviado a tu correo.');
    }

    /**
     * GET /postulaciones/{postulacion}/recibo
     */
    public function recibo(Request $request, Postulacion $postulacion)
    {
        $this->authorize('view', $postulacion);

        if ($postulacion->estatus !== Postulacion::ESTATUS_PAGADO) {
            return back()->with('status', 'Aún no puedes descargar el recibo: pago no confirmado.');
        }

        $p = $postulacion->loadMissing('convocatoria','user');

        $pdf = Pdf::loadView('pdf.recibo_postulacion', ['p' => $p])->setPaper('A4');
        return $pdf->download('recibo-'.$p->folio.'.pdf');
    }

    /**
     * DELETE /mis-postulaciones/{postulacion}
     */
    public function destroy(Request $request, Postulacion $postulacion)
    {
        $this->authorize('delete', $postulacion);

        $postulacion->delete();

        return redirect()
            ->route('postulaciones.index')
            ->with('status', 'Tu postulación fue eliminada.');
    }

    /**
     * GET /mis-postulaciones/{postulacion}/editar
     */
    public function edit(Request $request, Postulacion $postulacion)
    {
        $this->authorize('update', $postulacion);

        $perfil = optional($request->user()->perfilPostulante);
        return view('postulaciones.edit', compact('postulacion','perfil'));
    }

    /**
     * PATCH /mis-postulaciones/{postulacion}
     * Guarda TODA la ficha usando la misma FormRequest.
     */
    public function update(StorePerfilPostulanteRequest $request, Postulacion $postulacion)
    {
        $this->authorize('update', $postulacion);

        // 1) Valida con la FormRequest completa
        $data = $request->validated();

        // 2) Mapear fecha_nac (del form) a fecha_nacimiento (columna real)
        if (array_key_exists('fecha_nac', $data)) {
            $data['fecha_nacimiento'] = $data['fecha_nac'];
            unset($data['fecha_nac']);
        }

        // Normalizaciones útiles
        if (!empty($data['curp'])) {
            $data['curp'] = strtoupper(trim($data['curp']));
        }

        // 3) Guardar/actualizar perfil
        $user   = $request->user();
        $perfil = $user->perfilPostulante ?: new PerfilPostulante(['user_id' => $user->id]);
        $perfil->fill($data);
        $perfil->save(); // calcula edad en boot() del modelo

        // 4) Marcar edición (si usas esta métrica)
        $postulacion->user_edit_count = (int) ($postulacion->user_edit_count ?? 0) + 1;
        if (empty($postulacion->user_first_edit_at)) {
            $postulacion->user_first_edit_at = now();
        }
        $postulacion->save();

        return redirect()
            ->route('postulaciones.index')
            ->with('status', 'Tu ficha fue actualizada correctamente.');
    }

    /**
     * POST /postulaciones/{postulacion}/reenviar-recibo
     */
    public function reenviarRecibo(Request $request, Postulacion $postulacion)
    {
        if ($postulacion->estatus !== Postulacion::ESTATUS_PAGADO) {
            return back()->with('status', 'Aún no puedes reenviar el recibo: pago no confirmado.');
        }

        $postulacion->loadMissing('user');

        Mail::to($postulacion->user->email, $postulacion->user->name ?? null)
            ->queue((new ReciboPagoPostulacion($postulacion->id))->afterCommit());

        $postulacion->recibo_reenvios = (int) ($postulacion->recibo_reenvios ?? 0) + 1;
        $postulacion->recibo_enviado_at = now();
        $postulacion->save();

        return back()->with('status', 'Recibo reenviado a '.$postulacion->user->email.'.');
    }

    // ---------------- Helpers ----------------

    private function generateUniqueFolio(int $tries = 5): string
    {
        for ($i = 0; $i < $tries; $i++) {
            $folio = 'C'.now()->format('ymd').'-'.strtoupper(Str::random(6));
            if (! Postulacion::where('folio', $folio)->exists()) {
                return $folio;
            }
        }
        return 'C'.now()->format('ymd').'-'.strtoupper(Str::random(10));
    }

    private function generatePaymentReference(int $convocatoriaId, int $userId): string
    {
        return sprintf('CV%02d-U%04d-%s',
            $convocatoriaId % 100,
            $userId % 10000,
            strtoupper(Str::random(4))
        );
    }
}
