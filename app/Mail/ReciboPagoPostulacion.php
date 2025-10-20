<?php

namespace App\Mail;

use App\Models\Postulacion;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;

class ReciboPagoPostulacion extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $postulacionId;

    public function __construct(int $postulacionId)
    {
        $this->postulacionId = $postulacionId; // Sólo ID: seguro para colas
    }

    public function build()
    {
        // Carga fresca dentro del worker
        $p = Postulacion::with(['user', 'user.perfilPostulante', 'convocatoria'])
            ->findOrFail($this->postulacionId);

        $folioOrId = $p->folio ?? ('Postulación #'.$p->id);

        // Remitente dinámico con fallback a mail.from
        $from = array_merge([
            'name'    => config('mail.from.name'),
            'address' => config('mail.from.address'),
        ], (array) Setting::get('mail_from', []));

        // Opcional: reply-to configurable
        $replyTo = [
            'address' => config('mail.reply_to.address', 'recepcion@tudominio.org'),
            'name'    => config('mail.reply_to.name', 'Recepción'),
        ];

        // Genera PDF (evita serializar binarios en la cola)
        $pdfBinary = Pdf::loadView('pdf.recibo_postulacion', ['p' => $p])
            ->setPaper('A4')
            ->output();

        return $this->from($from['address'], $from['name'])
            ->replyTo($replyTo['address'], $replyTo['name'])
            ->subject('Recibo de pago — '.$folioOrId)
            // Usa Markdown porque tu blade tiene mail::message, mail::button, etc.
            ->markdown('mail.postulaciones.recibo', ['p' => $p])
            // (Opcional) versión texto plano si la tienes creada
            // ->text('mail.postulaciones.recibo_plain', ['p' => $p])
            ->attachData(
                $pdfBinary,
                'recibo-'.($p->folio ?? $p->id).'.pdf',
                ['mime' => 'application/pdf']
            );
    }
}
