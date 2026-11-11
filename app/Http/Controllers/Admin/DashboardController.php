<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;

class DashboardController extends Controller
{
    public function index()
    {
        $hariIni = now()->startOfDay();
        $user = auth()->user();

        $data = [
            'ordersToday' => Order::where('created_at', '>=', $hariIni)->count(),
            'waiting' => Order::where('status', Order::STATUS_PENDING)->count(),
            'processing' => Order::where('status', Order::STATUS_PROCESSING)->count(),
            'unpaid' => Order::whereIn('status', [Order::STATUS_PENDING, Order::STATUS_PROCESSING])
                ->where('payment_status', Order::PAYMENT_UNPAID)->count(),
            'soldOut' => Product::active()->where('is_available', false)->count(),
            'recent' => Order::with('items')->latest('id')->limit(8)->get(),
        ];

        // Angka penjualan hanya untuk Pemilik dan Super Admin (NFR-06).
        if ($user->isOwnerOrAbove()) {
            $counted = Order::counted()->where('created_at', '>=', $hariIni);
            $data['revenueToday'] = (int) (clone $counted)->sum('total');
            $data['doneToday'] = (clone $counted)->count();
        }

        return view('admin.dashboard', $data);
    }
}
