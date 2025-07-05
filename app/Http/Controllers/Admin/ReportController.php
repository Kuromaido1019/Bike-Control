<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Access;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        // Filtros para mostrar tabla previa (opcional)
        $date = $request->input('date', date('Y-m-d'));
        $start = $request->input('start_time', '00:00');
        $end = $request->input('end_time', '23:59');
        $accesses = Access::with(['user', 'guardUser', 'bike'])
            ->whereDate('entrance_time', $date)
            ->whereTime('entrance_time', '>=', $start)
            ->whereTime('entrance_time', '<=', $end)
            ->orderBy('entrance_time')
            ->get();
        return view('admin.reports', compact('accesses', 'date', 'start', 'end'));
    }

    public function pdf(Request $request)
    {
        $date = $request->input('date', date('Y-m-d'));
        $start = $request->input('start_time', '00:00');
        $end = $request->input('end_time', '23:59');
        $accesses = Access::with(['user', 'guardUser', 'bike'])
            ->whereDate('entrance_time', $date)
            ->whereTime('entrance_time', '>=', $start)
            ->whereTime('entrance_time', '<=', $end)
            ->orderBy('entrance_time')
            ->get();
        $title = "Reporte de Accesos del $date ($start a $end)";
        $generated = Carbon::now()->format('d/m/Y H:i');
        $pdf = Pdf::loadView('admin.report-pdf', compact('accesses', 'title', 'generated', 'date', 'start', 'end'));
        $filename = "Reporte_Accesos_{$date}_{$start}_{$end}.pdf";
        return $pdf->download($filename);
    }
}
