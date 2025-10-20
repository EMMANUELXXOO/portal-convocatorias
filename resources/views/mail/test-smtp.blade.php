<!doctype html>
<html lang="es">
  <body style="font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif">
    <h2>Correo de prueba — {{ $app }}</h2>
    <p>Este mensaje confirma que la configuración SMTP funciona.</p>
    <ul>
      <li><strong>Remitente (From):</strong> {{ $from['name'] }} &lt;{{ $from['address'] }}&gt;</li>
      <li><strong>Fecha/Hora:</strong> {{ $time }}</li>
      <li><strong>Entorno:</strong> {{ config('app.env') }}</li>
    </ul>
    <p>Si ves este correo, el servidor SMTP está operativo y acepta el remitente configurado.</p>
  </body>
</html>
