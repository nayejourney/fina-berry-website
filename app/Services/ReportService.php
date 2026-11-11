<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Order;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Perhitungan laporan penjualan (Pemilik dan Super Admin) dan laporan keuangan/analitik (Super Admin).
 * Pendapatan = jumlah total pesanan berstatus Selesai dengan pembayaran tervalidasi, semua kanal (FR-17, NFR-07).
 * Periode memakai tanggal pesanan dibuat (created_at).
 */
class ReportService
{
    private const MAX_HARI = 366;

    /** @return array{0:Carbon,1:Carbon} */
    public function range(?string $dari, ?string $sampai): array
    {
        try {
            $to = $sampai ? Carbon::parse($sampai)->endOfDay() : now()->endOfDay();
        } catch (\Throwable) {
            $to = now()->endOfDay();
        }

        try {
            $from = $dari ? Carbon::parse($dari)->startOfDay() : now()->startOfMonth();
        } catch (\Throwable) {
            $from = now()->startOfMonth();
        }

        if ($from->gt($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }
        if ((int) $from->diffInDays($to) > self::MAX_HARI) {
            $from = $to->copy()->subDays(self::MAX_HARI)->startOfDay();
        }

        return [$from, $to];
    }

    /** Laporan penjualan untuk satu periode. */
    public function sales(Carbon $from, Carbon $to): array
    {
        $orders = Order::counted()
            ->whereBetween('created_at', [$from, $to])
            ->with(['items.product.category'])
            ->orderBy('created_at')
            ->get();

        $revenue = (int) $orders->sum('total');
        $count = $orders->count();

        $byDay = [];
        foreach ($this->days($from, $to) as $day) {
            $byDay[$day] = ['date' => Carbon::parse($day), 'orders' => 0, 'revenue' => 0];
        }
        foreach ($orders as $o) {
            $key = $o->created_at->toDateString();
            $byDay[$key] ??= ['date' => Carbon::parse($key), 'orders' => 0, 'revenue' => 0];
            $byDay[$key]['orders']++;
            $byDay[$key]['revenue'] += $o->total;
        }

        $byChannel = [
            Order::CHANNEL_ONLINE => ['label' => 'Online (website/WhatsApp)', 'orders' => 0, 'revenue' => 0],
            Order::CHANNEL_OFFLINE => ['label' => 'Offline (di warung)', 'orders' => 0, 'revenue' => 0],
        ];
        $byMethod = [];
        foreach (Order::PAYMENT_METHODS as $k => $label) {
            $byMethod[$k] = ['label' => $label, 'orders' => 0, 'revenue' => 0];
        }
        foreach ($orders as $o) {
            $byChannel[$o->channel]['orders']++;
            $byChannel[$o->channel]['revenue'] += $o->total;
            if (isset($byMethod[$o->payment_method])) {
                $byMethod[$o->payment_method]['orders']++;
                $byMethod[$o->payment_method]['revenue'] += $o->total;
            }
        }

        $menus = [];
        $categories = [];
        $itemsSold = 0;
        foreach ($orders as $o) {
            foreach ($o->items as $item) {
                $itemsSold += $item->quantity;
                $m = $menus[$item->product_name] ?? ['name' => $item->product_name, 'qty' => 0, 'revenue' => 0];
                $m['qty'] += $item->quantity;
                $m['revenue'] += $item->line_total;
                $menus[$item->product_name] = $m;

                $cat = $item->product?->category?->name ?? 'Tanpa kategori';
                $c = $categories[$cat] ?? ['name' => $cat, 'qty' => 0, 'revenue' => 0];
                $c['qty'] += $item->quantity;
                $c['revenue'] += $item->line_total;
                $categories[$cat] = $c;
            }
        }

        $cancelled = Order::where('status', Order::STATUS_CANCELLED)->whereBetween('created_at', [$from, $to])->count();

        return [
            'from' => $from,
            'to' => $to,
            'order_count' => $count,
            'revenue' => $revenue,
            'avg_order' => $count ? (int) round($revenue / $count) : 0,
            'items_sold' => $itemsSold,
            'cancelled_count' => $cancelled,
            'by_day' => array_values($byDay),
            'by_channel' => $byChannel,
            'by_method' => $byMethod,
            'by_menu' => collect($menus)->sortByDesc('revenue')->values(),
            'by_category' => collect($categories)->sortByDesc('revenue')->values(),
            'orders' => $orders,
        ];
    }

    /** Laporan keuangan: laba rugi, margin, dan analitik (khusus Super Admin). */
    public function financial(Carbon $from, Carbon $to): array
    {
        $sales = $this->sales($from, $to);
        $orders = $sales['orders'];

        $cogs = 0;
        $profitByMenu = [];
        $dailyCogs = [];
        foreach ($orders as $o) {
            $day = $o->created_at->toDateString();
            foreach ($o->items as $item) {
                $itemCogs = $item->unit_cost * $item->quantity;
                $cogs += $itemCogs;
                $dailyCogs[$day] = ($dailyCogs[$day] ?? 0) + $itemCogs;

                $p = $profitByMenu[$item->product_name] ?? ['name' => $item->product_name, 'qty' => 0, 'revenue' => 0, 'cogs' => 0];
                $p['qty'] += $item->quantity;
                $p['revenue'] += $item->line_total;
                $p['cogs'] += $itemCogs;
                $profitByMenu[$item->product_name] = $p;
            }
        }

        $expenseRows = Expense::whereBetween('spent_on', [$from->toDateString(), $to->toDateString()])
            ->orderBy('spent_on')->get();
        $opex = (int) $expenseRows->sum('amount');
        $dailyOpex = [];
        foreach ($expenseRows as $e) {
            $k = $e->spent_on->toDateString();
            $dailyOpex[$k] = ($dailyOpex[$k] ?? 0) + $e->amount;
        }

        $expenseByCategory = [];
        foreach ($expenseRows as $e) {
            $expenseByCategory[$e->category] = ($expenseByCategory[$e->category] ?? 0) + $e->amount;
        }
        arsort($expenseByCategory);

        $revenue = $sales['revenue'];
        $gross = $revenue - $cogs;
        $net = $gross - $opex;

        $daily = [];
        foreach ($sales['by_day'] as $d) {
            $key = $d['date']->toDateString();
            $dayCogs = (int) ($dailyCogs[$key] ?? 0);
            $dayOpex = (int) ($dailyOpex[$key] ?? 0);
            $daily[] = [
                'date' => $d['date'],
                'revenue' => $d['revenue'],
                'cogs' => $dayCogs,
                'opex' => $dayOpex,
                'net' => $d['revenue'] - $dayCogs - $dayOpex,
            ];
        }

        $menuProfit = collect($profitByMenu)->map(function ($m) {
            $m['profit'] = $m['revenue'] - $m['cogs'];
            $m['margin'] = $m['revenue'] > 0 ? round($m['profit'] / $m['revenue'] * 100, 1) : 0;

            return $m;
        })->sortByDesc('profit')->values();

        $byHour = array_fill(0, 24, 0);
        $byWeekday = array_fill(1, 7, 0); // 1 = Senin ... 7 = Minggu
        foreach ($orders as $o) {
            $byHour[(int) $o->created_at->format('G')]++;
            $byWeekday[(int) $o->created_at->isoWeekday()]++;
        }

        $totalAll = Order::whereBetween('created_at', [$from, $to])->count();

        // Periode pembanding: rentang sama panjang tepat sebelum periode ini.
        $days = (int) round($from->copy()->startOfDay()->diffInDays($to->copy()->startOfDay())) + 1;
        $prevTo = $from->copy()->subDay()->endOfDay();
        $prevFrom = $prevTo->copy()->subDays($days - 1)->startOfDay();
        $prev = $this->headline($prevFrom, $prevTo);

        return [
            'from' => $from,
            'to' => $to,
            'sales' => $sales,
            'revenue' => $revenue,
            'cogs' => $cogs,
            'gross_profit' => $gross,
            'gross_margin' => $revenue > 0 ? round($gross / $revenue * 100, 1) : 0,
            'opex' => $opex,
            'net_profit' => $net,
            'net_margin' => $revenue > 0 ? round($net / $revenue * 100, 1) : 0,
            'expense_by_category' => $expenseByCategory,
            'expenses' => $expenseRows,
            'daily' => $daily,
            'menu_profit' => $menuProfit,
            'by_hour' => $byHour,
            'by_weekday' => $byWeekday,
            'cancel_rate' => $totalAll ? round($sales['cancelled_count'] / $totalAll * 100, 1) : 0,
            'prev' => $prev + ['from' => $prevFrom, 'to' => $prevTo],
            'revenue_growth' => $this->growth($revenue, $prev['revenue']),
            'net_growth' => $this->growth($net, $prev['net']),
        ];
    }

    /** Angka ringkas (pendapatan, laba bersih, jumlah pesanan) untuk periode pembanding. */
    private function headline(Carbon $from, Carbon $to): array
    {
        $orders = Order::counted()->whereBetween('created_at', [$from, $to])->with('items')->get();
        $revenue = (int) $orders->sum('total');
        $cogs = 0;
        foreach ($orders as $o) {
            foreach ($o->items as $item) {
                $cogs += $item->unit_cost * $item->quantity;
            }
        }
        $opex = (int) Expense::whereBetween('spent_on', [$from->toDateString(), $to->toDateString()])->sum('amount');

        return [
            'orders' => $orders->count(),
            'revenue' => $revenue,
            'net' => $revenue - $cogs - $opex,
        ];
    }

    private function growth(int $now, int $before): ?float
    {
        if ($before === 0) {
            return null;
        }

        return round(($now - $before) / abs($before) * 100, 1);
    }

    /** @return array<int,string> daftar tanggal Y-m-d dari awal sampai akhir periode */
    private function days(Carbon $from, Carbon $to): array
    {
        $out = [];
        $cursor = $from->copy()->startOfDay();
        $end = $to->copy()->startOfDay();
        while ($cursor->lte($end)) {
            $out[] = $cursor->toDateString();
            $cursor->addDay();
        }

        return $out;
    }

    /** Baris CSV ringkasan penjualan (FR-21). */
    public function salesCsvRows(array $s): array
    {
        $rows = [
            ['LAPORAN PENJUALAN '.strtoupper(setting('store_name'))],
            ['Periode', $s['from']->format('d/m/Y').' - '.$s['to']->format('d/m/Y')],
            ['Jumlah pesanan selesai', $s['order_count']],
            ['Total pendapatan (Rp)', $s['revenue']],
            ['Rata-rata per pesanan (Rp)', $s['avg_order']],
            ['Porsi terjual', $s['items_sold']],
            ['Pesanan dibatalkan', $s['cancelled_count']],
            [],
            ['REKAP PER HARI'],
            ['Tanggal', 'Pesanan', 'Pendapatan (Rp)'],
        ];
        foreach ($s['by_day'] as $d) {
            $rows[] = [$d['date']->format('d/m/Y'), $d['orders'], $d['revenue']];
        }

        $rows[] = [];
        $rows[] = ['REKAP PER KANAL'];
        $rows[] = ['Kanal', 'Pesanan', 'Pendapatan (Rp)'];
        foreach ($s['by_channel'] as $c) {
            $rows[] = [$c['label'], $c['orders'], $c['revenue']];
        }

        $rows[] = [];
        $rows[] = ['REKAP PER METODE PEMBAYARAN'];
        $rows[] = ['Metode', 'Pesanan', 'Pendapatan (Rp)'];
        foreach ($s['by_method'] as $c) {
            $rows[] = [$c['label'], $c['orders'], $c['revenue']];
        }

        $rows[] = [];
        $rows[] = ['REKAP PER MENU'];
        $rows[] = ['Menu', 'Porsi', 'Pendapatan (Rp)'];
        foreach ($s['by_menu'] as $m) {
            $rows[] = [$m['name'], $m['qty'], $m['revenue']];
        }

        return $rows;
    }

    /** Baris CSV riwayat transaksi (satu baris per pesanan). */
    public function transactionCsvRows(Collection $orders): array
    {
        $rows = [['Kode', 'Tanggal', 'Kanal', 'Pelanggan', 'Pengambilan', 'Metode bayar', 'Dibayar pada', 'Total (Rp)', 'Item']];
        foreach ($orders as $o) {
            $rows[] = [
                $o->code,
                $o->created_at->format('d/m/Y H:i'),
                $o->channel,
                $o->customer_name ?: '-',
                $o->fulfillment_label,
                $o->payment_method_label,
                $o->paid_at?->format('d/m/Y H:i'),
                $o->total,
                $o->items->map(fn ($i) => $i->product_name.' x'.$i->quantity)->implode('; '),
            ];
        }

        return $rows;
    }

    /** Baris CSV laporan keuangan dan analitik. */
    public function financialCsvRows(array $f): array
    {
        $rows = [
            ['LAPORAN LABA RUGI DAN ANALITIK '.strtoupper(setting('store_name'))],
            ['Periode', $f['from']->format('d/m/Y').' - '.$f['to']->format('d/m/Y')],
            [],
            ['RINGKASAN LABA RUGI', 'Rp'],
            ['Pendapatan (pesanan selesai dan lunas)', $f['revenue']],
            ['HPP (harga pokok penjualan)', $f['cogs']],
            ['Laba kotor', $f['gross_profit']],
            ['Margin kotor (%)', $f['gross_margin']],
            ['Biaya operasional', $f['opex']],
            ['Laba bersih', $f['net_profit']],
            ['Margin bersih (%)', $f['net_margin']],
            ['Jumlah pesanan', $f['sales']['order_count']],
            ['Rata-rata per pesanan', $f['sales']['avg_order']],
            ['Tingkat pembatalan (%)', $f['cancel_rate']],
            [],
            ['BIAYA OPERASIONAL PER KATEGORI', 'Rp'],
        ];
        foreach ($f['expense_by_category'] as $k => $v) {
            $rows[] = [Expense::CATEGORIES[$k] ?? $k, $v];
        }

        $rows[] = [];
        $rows[] = ['TREN HARIAN'];
        $rows[] = ['Tanggal', 'Pendapatan', 'HPP', 'Biaya operasional', 'Laba bersih'];
        foreach ($f['daily'] as $d) {
            $rows[] = [$d['date']->format('d/m/Y'), $d['revenue'], $d['cogs'], $d['opex'], $d['net']];
        }

        $rows[] = [];
        $rows[] = ['KONTRIBUSI LABA PER MENU'];
        $rows[] = ['Menu', 'Porsi', 'Pendapatan', 'HPP', 'Laba', 'Margin (%)'];
        foreach ($f['menu_profit'] as $m) {
            $rows[] = [$m['name'], $m['qty'], $m['revenue'], $m['cogs'], $m['profit'], $m['margin']];
        }

        return $rows;
    }
}
