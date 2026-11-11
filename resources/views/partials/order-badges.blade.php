<span class="badge {{ $order->badge_class }}">{{ $order->status_label }}</span>
<span class="badge {{ $order->is_paid ? 'text-bg-success' : 'text-bg-danger' }}">{{ $order->is_paid ? 'Lunas' : 'Belum bayar' }}</span>
