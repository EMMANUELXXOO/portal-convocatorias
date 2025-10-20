<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Models\Setting;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:backoffice');
        $this->middleware('can:manage-settings');
    }

    public function edit()
    {
        $data = [
            'from_name'   => Setting::get('mail_from_name'),
            'from_email'  => Setting::get('mail_from_address'),
            'mailer'      => Setting::get('mail_mailer','smtp'),
            'host'        => Setting::get('mail_host'),
            'port'        => Setting::get('mail_port', 587),
            'encryption'  => Setting::get('mail_encryption','tls'),
            'username'    => Setting::get('mail_username'),
            'password'    => Setting::getSecret('mail_password'), // se muestra vacío por UX
            'timeout'     => Setting::get('mail_timeout', 30),
        ];

        return view('admin.settings.edit', compact('data'));
    }

    public function update(Request $request)
    {
        $v = $request->validate([
            'from_name'  => ['required','string','max:120'],
            'from_email' => ['required','email','max:150'],
            'mailer'     => ['required','in:smtp,sendmail,log,array'],
            'host'       => ['nullable','string','max:150'],
            'port'       => ['nullable','integer','min:1','max:65535'],
            'encryption' => ['nullable','in:tls,ssl,null'],
            'username'   => ['nullable','string','max:150'],
            // password es opcional; si viene vacío, no se cambia
            'password'   => ['nullable','string','max:255'],
            'timeout'    => ['nullable','integer','min:5','max:120'],
        ]);

        // persistir
        Setting::put('mail_from_name',    $v['from_name']);
        Setting::put('mail_from_address', $v['from_email']);
        Setting::put('mail_mailer',       $v['mailer']);
        Setting::put('mail_host',         $v['host'] ?? null);
        Setting::put('mail_port',         $v['port'] ?? null);
        Setting::put('mail_encryption',   $v['encryption'] ?? null);
        Setting::put('mail_username',     $v['username'] ?? null);
        Setting::put('mail_timeout',      $v['timeout'] ?? 30);

        if (!empty($v['password'])) {
            Setting::putSecret('mail_password', $v['password']);
        }

        audit_log('settings.updated', 'settings', ['keys'=>array_keys($v)]);

        return back()->with('status','Configuración de correo guardada.');
    }

    public function testEmail(Request $request)
    {
        $to = $request->validate(['to' => ['required','email']])['to'];

        // Tomar valores guardados
        $fromName  = Setting::get('mail_from_name',    config('mail.from.name'));
        $fromEmail = Setting::get('mail_from_address', config('mail.from.address'));

        $cfg = [
            'transport'  => 'smtp',
            'host'       => Setting::get('mail_host', config('mail.mailers.smtp.host')),
            'port'       => (int) Setting::get('mail_port', config('mail.mailers.smtp.port')),
            'encryption' => Setting::get('mail_encryption', config('mail.mailers.smtp.encryption')),
            'username'   => Setting::get('mail_username', config('mail.mailers.smtp.username')),
            'password'   => Setting::getSecret('mail_password') ?? config('mail.mailers.smtp.password'),
            'timeout'    => (int) Setting::get('mail_timeout', 30),
        ];

        // Sobrescribir config en runtime solo para este envío
        config([
            'mail.default' => 'dynamic_smtp',
            'mail.mailers.dynamic_smtp' => $cfg,
            'mail.from.address' => $fromEmail,
            'mail.from.name'    => $fromName,
        ]);

        try {
            Mail::mailer('dynamic_smtp')->raw(
                "Correo de prueba desde el portal.\n\nFrom: {$fromName} <{$fromEmail}>\n" .
                "Servidor: {$cfg['host']}:{$cfg['port']} ({$cfg['encryption']})",
                function ($m) use ($to, $fromEmail, $fromName) {
                    $m->to($to)
                      ->from($fromEmail, $fromName)
                      ->subject('✔ Prueba de SMTP — Portal Convocatorias');
                }
            );

            audit_log('settings.test_email_sent', 'settings', ['to'=>$to]);
            return back()->with('status',"Enviado a {$to}. Revisa tu bandeja y spam.");

        } catch (\Throwable $e) {
            audit_log('settings.test_email_failed', 'settings', ['to'=>$to, 'error'=>$e->getMessage()]);
            return back()->withErrors('Falló el envío: '.$e->getMessage());
        }
    }
}
