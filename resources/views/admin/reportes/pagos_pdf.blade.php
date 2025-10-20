<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Pagos</title>
<style>
  body{font-family: DejaVu Sans, sans-serif; font-size:12px}
  table{width:100%;border-collapse:collapse}
  th,td{border:1px solid #ddd;padding:6px}
  th{background:#f3f4f6}
</style>
</head>
<body>
<h3>Reporte de Pagos</h3>
<table>
  <thead>
    <tr>
      <th>ID</th><th>Folio</th><th>Referencia</th><th>Folio banco</th><th>Fecha pago</th><th>Usuario</th><th>Convocatoria</th>
    </tr>
  </thead>
  <tbody>
  @foreach($rows as $r)
    <tr>
      <td>{{ $r->id }}</td>
      <td>{{ $r->folio }}</td>
      <td>{{ $r->referencia_pago }}</td>
      <td>{{ $r->folio_banco }}</td>
      <td>{{ optional($r->fecha_pago)->format('Y-m-d H:i') }}</td>
      <td>{{ $r->usuario }}</td>
      <td>{{ $r->convocatoria }}</td>
    </tr>
  @endforeach
  </tbody>
</table>
</body>
</html>
