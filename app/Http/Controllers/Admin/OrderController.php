<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use DomainException;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $f = $request->validate([
            'status' => ['nullable', 'in:aktif,menunggu,diproses,selesai,dibatalkan'],
            'channel' => ['nullable', 'in:online,offline'],
            'payment' => ['nullable', 'in:belum_bayar,lunas'],
            'dari' => ['nullable', 'date'],
            'sampai' => ['nullable', 'date'],
            'q' => ['nullable', 'string', 'max:60'],
        ]);

        $orders = Order::with('items')
            ->when($f['status'] ?? null, function ($q, $s) {
                return $s === 'aktif'
                    ? $q->whereIn('status', [Order::STATUS_PENDING, Order::STATUS_PROCESSING])
                    : $q->where('status', $s);
            })
            ->when($f['channel'] ?? null, fn ($q, $v) => $q->where('channel', $v))
            ->when($f['payment'] ?? null, fn ($q, $v) => $q->where('payment_status', $v))
            ->when($f['dari'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($f['sampai'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->when($f['q'] ?? null, function ($q, $v) {
                $q->where(function ($w) use ($v) {
                    $w->where('code', 'like', "%{$v}%")
                        ->orWhere('customer_name', 'like', "%{$v}%")
                        ->orWhere('customer_phone', 'like', "%{$v}%");
                });
            })
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.orders.index', [
            'orders' => $orders,
            'filters' => $f,
            'latestId' => (int) Order::max('id'),
        ]);
    }

    /** Dipanggil berkala oleh browser untuk penanda pesanan baru (FR-14). */
    public function poll()
    {
        return response()->json([
            'latest_id' => (int) Order::max('id'),
            'waiting' => Order::where('status', Order::STATUS_PENDING)->count(),
        ]);
    }

    public function show(Order $order, OrderService $service)
    {
        $order->load(['items', 'statusLogs.user', 'creator', 'paymentValidator']);

        return view('admin.orders.show', [
            'order' => $order,
            'waCustomer' => $service->waCustomerUrl($order),
        ]);
    }

    public function receipt(Order $order)
    {
        $order->load('items');

        return view('admin.orders.receipt', compact('order'));
    }

    public function status(Request $request, Order $order, OrderService $service)
    {
        $data = $request->validate([
            'to' => ['required', 'in:menunggu,diproses,selesai,dibatalkan'],
            'reason' => ['nullable', 'string', 'max:200'],
        ]);

        try {
            $service->changeStatus($order, $data['to'], $request->user(), $data['reason'] ?? null);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Status pesanan '.$order->code.' diubah menjadi '.$order->fresh()->status_label.'.');
    }

    public function pay(Request $request, Order $order, OrderService $service)
    {
        $data = $request->validate([
            'payment_method' => ['required', 'in:tunai,transfer,qris'],
            'note' => ['nullable', 'string', 'max:200'],
        ]);

        try {
            $service->validatePayment($order, $data['payment_method'], $data['note'] ?? null, $request->user());
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Pembayaran tervalidasi dan tercatat di rekap.');
    }

    public function revertPay(Request $request, Order $order, OrderService $service)
    {
        $data = $request->validate(['reason' => ['required', 'string', 'min:5', 'max:200']]);

        try {
            $service->revertPayment($order, $data['reason'], $request->user());
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Validasi pembayaran dibatalkan.');
    }
}
