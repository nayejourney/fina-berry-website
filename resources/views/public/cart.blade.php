@extends('layouts.public')

@section('title', 'Keranjang | '.setting('store_name'))
@section('noindex', '1')

@section('content')
<section class="container py-5">
    <h1 class="fb-section-title mb-4">Keranjang</h1>

    @if($lines->isEmpty())
        <div class="text-center py-5">
            <div class="display-4">🛒</div>
            <p class="text-muted">Keranjang masih kosong.</p>
            <a href="{{ route('menu') }}" class="btn btn-primary">Pilih menu</a>
        </div>
    @else
        <form method="POST" action="{{ route('cart.update') }}" id="formCart">
            @csrf
            @method('PATCH')
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>Menu</th><th class="text-end">Harga</th><th style="width:130px">Jumlah</th><th class="text-end">Subtotal</th><th></th></tr></thead>
                    <tbody>
                    @foreach($lines as $line)
                        <tr class="{{ $line['orderable'] ? '' : 'table-warning' }}">
                            <td>
                                <strong>{{ $line['product']->name }}</strong>
                                @unless($line['orderable'])<div class="small text-danger">Sedang tidak tersedia, akan dikeluarkan saat checkout.</div>@endunless
                            </td>
                            <td class="text-end">{{ rupiah($line['product']->price) }}</td>
                            <td><input type="number" name="qty[{{ $line['product']->id }}]" value="{{ $line['qty'] }}" min="0" max="50" class="form-control form-control-sm" aria-label="Jumlah {{ $line['product']->name }}"></td>
                            <td class="text-end">{{ rupiah($line['line_total']) }}</td>
                            <td class="text-end">
                                <button type="submit" form="del{{ $line['product']->id }}" class="btn btn-sm btn-outline-danger" aria-label="Hapus {{ $line['product']->name }}"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot>
                        <tr><th colspan="3" class="text-end">Total</th><th class="text-end fs-5 fb-price">{{ rupiah($subtotal) }}</th><th></th></tr>
                    </tfoot>
                </table>
            </div>
            <div class="d-flex flex-wrap gap-2 justify-content-between">
                <a href="{{ route('menu') }}" class="btn btn-outline-primary"><i class="bi bi-arrow-left"></i> Tambah menu</a>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-outline-primary">Perbarui jumlah</button>
                    <a href="{{ route('checkout') }}" class="btn btn-primary">Lanjut checkout <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
        </form>
        @foreach($lines as $line)
            <form id="del{{ $line['product']->id }}" method="POST" action="{{ route('cart.remove', $line['product']->id) }}">@csrf @method('DELETE')</form>
        @endforeach
    @endif
</section>
@endsection
