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
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #bbb; padding: 6px 8px; text-align: left; }
        th { background: #f2f2f2; }
        .header { margin-bottom: 20px; }
        .footer { margin-top: 30px; font-size: 11px; color: #888; text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title ?? 'Reporte' }}</h1>
        <p>Generado: {{ $generated ?? Carbon::now()->format('d/m/Y H:i') }}</p>
    </div>

    @switch($type)
        @case('general')
            <h2>Resumen General</h2>
            <ul>
                <li><strong>Usuarios registrados:</strong> {{ $totalUsers ?? '---' }}</li>
                <li><strong>Bicicletas registradas:</strong> {{ $totalBikes ?? '---' }}</li>
                <li><strong>Ingresos este mes:</strong> {{ $monthlyAccesses ?? '---' }}</li>
            </ul>
            <h3>Top 5 usuarios más activos del mes</h3>
            <table>
                <thead><tr><th>Usuario</th><th>Ingresos</th></tr></thead>
                <tbody>
                @forelse($topUsers as $userStat)
                    <tr>
                        <td>{{ $userStat->user ? $userStat->user->name : '---' }}</td>
                        <td>{{ $userStat->total }}</td>
                    </tr>
                @empty
                    <tr><td colspan="2">Sin datos</td></tr>
                @endforelse
                </tbody>
            </table>
            @break
        @case('accesos')
            <h2>Reporte de Accesos</h2>
            <table>
                <thead><tr><th>Usuario</th><th>Entrada</th><th>Salida</th><th>Observación</th></tr></thead>
                <tbody>
                @forelse($accesses as $access)
                    <tr>
                        <td>{{ $access->user ? $access->user->name : '---' }}</td>
                        <td>{{ $access->entrance_time ? Carbon::parse($access->entrance_time)->format('d/m/Y H:i') : '---' }}</td>
                        <td>{{ $access->exit_time ? Carbon::parse($access->exit_time)->format('d/m/Y H:i') : '---' }}</td>
                        <td>{{ $access->observation ?? '' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4">Sin accesos registrados</td></tr>
                @endforelse
                </tbody>
            </table>
            @break
        @case('usuarios')
            <h2>Usuarios más activos</h2>
            <table>
                <thead><tr><th>Usuario</th><th>Ingresos este mes</th></tr></thead>
                <tbody>
                @forelse($topUsers as $userStat)
                    <tr>
                        <td>{{ $userStat->user ? $userStat->user->name : '---' }}</td>
                        <td>{{ $userStat->total }}</td>
                    </tr>
                @empty
                    <tr><td colspan="2">Sin datos</td></tr>
                @endforelse
                </tbody>
            </table>
            @break
        @case('bicicletas')
            <h2>Reporte de Bicicletas</h2>
            <table>
                <thead><tr><th>Usuario</th><th>Marca</th><th>Modelo</th><th>Color</th><th>Registrada</th></tr></thead>
                <tbody>
                @forelse($bikes as $bike)
                    <tr>
                        <td>{{ $bike->user ? $bike->user->name : '---' }}</td>
                        <td>{{ $bike->brand }}</td>
                        <td>{{ $bike->model }}</td>
                        <td>{{ $bike->color }}</td>
                        <td>{{ $bike->created_at ? Carbon::parse($bike->created_at)->format('d/m/Y') : '---' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5">Sin bicicletas registradas</td></tr>
                @endforelse
                </tbody>
            </table>
            @break
        @case('incidentes')
            <h2>Reporte de Incidentes/Observaciones</h2>
            <table>
                <thead><tr><th>Usuario</th><th>Fecha</th><th>Tipo</th><th>Observación</th></tr></thead>
                <tbody>
                @forelse($incidents as $incident)
                    <tr>
                        <td>{{ $incident->user ? $incident->user->name : '---' }}</td>
                        <td>{{ $incident->created_at ? Carbon::parse($incident->created_at)->format('d/m/Y H:i') : '---' }}</td>
                        <td>{{ $incident->type ?? '---' }}</td>
                        <td>{{ $incident->observation ?? '' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4">Sin incidentes registrados</td></tr>
                @endforelse
                </tbody>
            </table>
            @break
        @default
            <p>No hay datos para mostrar.</p>
    @endswitch

    <div class="footer">
        <p>BikeControl &copy; {{ date('Y') }} | Reporte generado automáticamente.</p>
    </div>
</body>
</html>
