<div class="card fb-card {{ $product->is_available ? '' : 'fb-soldout' }}">
    @if($product->image_url)
        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="fb-card-img" loading="lazy" width="400" height="300">
    @else
        <div class="fb-card-ph" aria-hidden="true">🍽️</div>
    @endif
    <div class="card-body d-flex flex-column">
        <div class="small text-muted">{{ $product->category->name ?? '' }}</div>
        <h3 class="h6 fw-bold mb-1" style="font-family: inherit;">{{ $product->name }}</h3>
        @if($product->description)
            <p class="small text-muted mb-2">{{ $product->description }}</p>
        @endif
        <div class="mt-auto d-flex align-items-center justify-content-between pt-2">
            <span class="fb-price">{{ rupiah($product->price) }}</span>
            @if($product->is_available)
                <form method="POST" action="{{ route('cart.add') }}">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-bag-plus"></i> Tambah</button>
                </form>
            @else
                <span class="badge text-bg-secondary">Habis</span>
            @endif
        </div>
    </div>
</div>
