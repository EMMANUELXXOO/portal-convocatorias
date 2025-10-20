<?php

namespace App\Mail;

use App\Models\GrupoExamen;
use App\Models\Postulacion;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotificacionFechaExamen extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public GrupoExamen $grupo;
    public Postulacion $postulacion;
    public ?string $mensajeLibre;
    public string $asunto;

    public function __construct(
        GrupoExamen $grupo,
        Postulacion $postulacion,
        ?string $mensajeLibre = null,
        ?string $asunto = null
    ) {
        $this->asunto = $asunto ?: 'Fecha de examen — ' . ($grupo->convocatoria->titulo ?? 'Convocatoria');
        $this->grupo        = $grupo->withoutRelations();
        $this->postulacion  = $postulacion->withoutRelations();
        $this->mensajeLibre = $mensajeLibre;
    }

    public function build()
    {
        $fechaLocal = optional($this->grupo->fecha_hora)?->timezone('America/Tijuana');
        $fecha      = $fechaLocal?->format('Y-m-d');
        $hora       = $fechaLocal?->format('H:i');

        $tipoLegible = ucfirst($this->grupo->tipo)
            . (($this->grupo->tipo === 'otro' && $this->grupo->tipo_detalle) ? ' — ' . $this->grupo->tipo_detalle : '');

        $replyToAddress = config('mail.reply_to.address', env('MAIL_REPLYTO_ADDRESS', 'recepcion@escuelacruzrojatijuana.org'));
        $replyToName    = config('mail.reply_to.name',    env('MAIL_REPLYTO_NAME',    'Recepción'));

        return $this->subject($this->asunto)
            ->replyTo($replyToAddress, $replyToName)
            ->markdown('mail.grupos.notificacion_fecha_examen', [
                'convocatoria'     => $this->grupo->convocatoria ?? null,
                'grupo'            => $this->grupo,
                'postulacion'      => $this->postulacion,
                'fecha'            => $fecha,
                'hora'             => $hora,
                'lugar'            => $this->grupo->lugar ?: 'Por definir',
                'tipoLegible'      => $tipoLegible,
                'mensajeLibre'     => $this->mensajeLibre,
                'urlPostulaciones' => route('postulaciones.index'),
            ]);
    }
}
