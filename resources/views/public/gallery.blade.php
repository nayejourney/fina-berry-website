@extends('layouts.public')

@section('title', 'Galeri | '.setting('store_name'))
@section('description', 'Foto hidangan dan suasana '.setting('store_name').'.')

@section('content')
<section class="container py-5">
    <p class="fb-tag mb-1">Galeri</p>
    <h1 class="fb-section-title mb-4">Foto hidangan dan suasana warung</h1>

    @if($photos->isEmpty())
        <div class="alert alert-light border">Galeri belum diisi.</div>
    @else
        <div class="row g-3 fb-gallery">
            @foreach($photos as $photo)
                <div class="col-6 col-md-4 col-lg-3">
                    <figure class="m-0">
                        <img src="{{ $photo->image_url }}" alt="{{ $photo->caption ?: 'Foto '.setting('store_name') }}" loading="lazy" width="400" height="400">
                        @if($photo->caption)<figcaption class="small text-muted mt-1">{{ $photo->caption }}</figcaption>@endif
                    </figure>
                </div>
            @endforeach
        </div>
    @endif
</section>
@endsection
