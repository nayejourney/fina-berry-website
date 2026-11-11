<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use App\Support\Csv;
use Illuminate\Http\Request;

/** Laporan penjualan: Pemilik dan Super Admin (NFR-06). */
class ReportController extends Controller
{
    public function index(Request $request, ReportService $reports)
    {
        [$from, $to] = $reports->range($request->query('dari'), $request->query('sampai'));

        return view('admin.reports.index', [
            'r' => $reports->sales($from, $to),
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function print(Request $request, ReportService $reports)
    {
        [$from, $to] = $reports->range($request->query('dari'), $request->query('sampai'));

        return view('admin.reports.print', [
            'r' => $reports->sales($from, $to),
            'withTransactions' => $request->boolean('transaksi', true),
        ]);
    }

    public function export(Request $request, ReportService $reports)
    {
        [$from, $to] = $reports->range($request->query('dari'), $request->query('sampai'));
        $sales = $reports->sales($from, $to);
        $tipe = $request->query('tipe') === 'transaksi' ? 'transaksi' : 'ringkasan';

        $rows = $tipe === 'transaksi'
            ? $reports->transactionCsvRows($sales['orders'])
            : $reports->salesCsvRows($sales);

        return Csv::download("laporan-{$tipe}-{$from->format('Ymd')}-{$to->format('Ymd')}.csv", $rows);
    }
}
