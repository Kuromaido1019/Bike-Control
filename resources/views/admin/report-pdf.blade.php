@php
    use Carbon\Carbon;
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'Reporte' }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 13px; color: #222; }
        h1, h2, h3 { color: #395B50; }
        .header {
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            border-bottom: 4px solid #395B50;
            padding-bottom: 10px;
        }
        .header-logo {
            height: 60px;
            margin-right: 18px;
        }
        .header-title {
            flex: 1;
        }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #bbb; padding: 7px 10px; text-align: left; }
        th {
            background: linear-gradient(90deg, #92AFD7 0%, #395B50 100%);
            color: #fff;
            font-weight: bold;
        }
        tr:nth-child(even) { background: #f7fafc; }
        tr:nth-child(odd) { background: #e9f5f2; }
        ul { background: #f2f2f2; border-radius: 6px; padding: 12px 18px; }
        .footer { margin-top: 30px; font-size: 11px; color: #888; text-align: right; }
        .section-title { color: #2d7a5f; background: #e0f7ef; padding: 6px 12px; border-radius: 4px; margin-top: 18px; }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ public_path('img/bike_logo.png') }}" class="header-logo" alt="BikeControl Logo">
        <div class="header-title">
            <h1 style="margin:0;">{{ $title ?? 'Reporte' }}</h1>
            <p style="margin:0; font-size:12px; color:#395B50;">Generado: {{ $generated ?? Carbon::now()->format('d/m/Y H:i') }}</p>
        </div>
    </div>
    <h2 class="section-title">Accesos registrados</h2>
    <table>
        <thead>
            <tr>
                <th>Usuario</th>
                <th>Guardia</th>
                <th>Bicicleta</th>
                <th>Entrada</th>
                <th>Salida</th>
                <th>Observación</th>
            </tr>
        </thead>
        <tbody>
        @forelse($accesses as $access)
            <tr>
                <td>{{ $access->user ? $access->user->name : '---' }}</td>
                <td>{{ $access->guardUser ? $access->guardUser->name : '---' }}</td>
                <td>{{ $access->bike ? $access->bike->brand . ' ' . $access->bike->model : '---' }}</td>
                <td>{{ $access->entrance_time ? Carbon::parse($access->entrance_time)->format('d/m/Y H:i') : '---' }}</td>
                <td>{{ $access->exit_time ? Carbon::parse($access->exit_time)->format('d/m/Y H:i') : '---' }}</td>
                <td>{{ $access->observation ?? '' }}</td>
            </tr>
        @empty
            <tr><td colspan="6">Sin accesos registrados en este rango.</td></tr>
        @endforelse
        </tbody>
    </table>
    <div class="footer">
        <p>BikeControl &copy; {{ date('Y') }} | Reporte generado automáticamente.</p>
    </div>
</body>
</html>
