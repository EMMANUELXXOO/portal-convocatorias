@php
  $p = $p ?? null;
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Recibo {{ $p->folio ?? $p->id }}</title>
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; }
    .h1 { font-size: 20px; font-weight: 700; margin-bottom: 6px; }
    .muted { color: #555; }
    .row { margin: 6px 0; }
    .box { border:1px solid #ddd; padding:10px; border-radius:6px; margin-top:12px;}
    table { width:100%; border-collapse: collapse; margin-top:8px;}
    th, td { text-align:left; padding:6px; border-bottom:1px solid #eee;}
  </style>
</head>
<body>
  <div class="h1">Recibo de pago</div>
  <div class="muted">Postulación #{{ $p->id }} — Folio: {{ $p->folio ?? '—' }}</div>

  <div class="box">
    <table>
      <tr>
        <th>Convocatoria</th>
        <td>{{ $p->convocatoria->titulo ?? '—' }}</td>
      </tr>
      <tr>
        <th>Nombre</th>
        <td>{{ $p->user->name ?? '—' }}</td>
      </tr>
      <tr>
        <th>Correo</th>
        <td>{{ $p->user->email ?? '—' }}</td>
      </tr>
      <tr>
        <th>Referencia de pago</th>
        <td>{{ $p->referencia_pago ?? '—' }}</td>
      </tr>
      <tr>
        <th>Fecha de pago</th>
        <td>{{ optional($p->fecha_pago)->format('d/m/Y H:i') ?? '—' }}</td>
      </tr>
      <tr>
        <th>Generado</th>
        <td>{{ now()->format('d/m/Y H:i') }}</td>
      </tr>
    </table>
  </div>

  <p class="muted" style="margin-top:16px">
    Documento generado automáticamente por {{ config('app.name') }}.
  </p>
</body>
</html>
