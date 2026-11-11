<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use App\Support\Csv;
use Illuminate\Http\Request;

/** Laba rugi, margin, dan analitik bisnis: khusus Super Admin (si bos/manajer). */
class AnalyticsController extends Controller
{
    public function index(Request $request, ReportService $reports)
    {
        [$from, $to] = $reports->range($request->query('dari'), $request->query('sampai'));

        return view('admin.analytics.index', ['f' => $reports->financial($from, $to)]);
    }

    public function print(Request $request, ReportService $reports)
    {
        [$from, $to] = $reports->range($request->query('dari'), $request->query('sampai'));

        return view('admin.analytics.print', ['f' => $reports->financial($from, $to)]);
    }

    public function export(Request $request, ReportService $reports)
    {
        [$from, $to] = $reports->range($request->query('dari'), $request->query('sampai'));
        $f = $reports->financial($from, $to);

        return Csv::download("analitik-laba-rugi-{$from->format('Ymd')}-{$to->format('Ymd')}.csv", $reports->financialCsvRows($f));
    }
}
