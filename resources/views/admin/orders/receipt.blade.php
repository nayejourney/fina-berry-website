@extends('layouts.print')

@section('title', 'Struk '.$order->code)

@section('content')
<div class="receipt">
    <div style="text-align:center">
        <strong>{{ setting('store_name') }}</strong><br>
        <small>{{ setting('address') }}</small><br>
        <small>WA +{{ wa_number(setting('wa_number')) }}</small>
    </div>
    <hr>
    <div class="row2"><span>Kode</span><span>{{ $order->code }}</span></div>
    <div class="row2"><span>Tanggal</span><span>{{ $order->created_at->format('d/m/Y H:i') }}</span></div>
    @if($order->customer_name)<div class="row2"><span>Pelanggan</span><span>{{ $order->customer_name }}</span></div>@endif
    <div class="row2"><span>Layanan</span><span>{{ $order->fulfillment_label }}</span></div>
    <hr>
    @foreach($order->items as $i)
        <div>{{ $i->product_name }}</div>
        <div class="row2"><span>{{ $i->quantity }} x {{ number_format($i->unit_price, 0, ',', '.') }}</span><span>{{ number_format($i->line_total, 0, ',', '.') }}</span></div>
    @endforeach
    <hr>
    <div class="row2"><strong>TOTAL</strong><strong>{{ rupiah($order->total) }}</strong></div>
    <div class="row2"><span>Bayar</span><span>{{ $order->payment_method_label }} · {{ $order->is_paid ? 'LUNAS' : 'BELUM BAYAR' }}</span></div>
    <hr>
    <div style="text-align:center"><small>Terima kasih sudah makan di {{ setting('store_name') }}</small></div>
</div>
@endsection
