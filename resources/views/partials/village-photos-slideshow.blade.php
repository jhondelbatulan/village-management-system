@if($villagePhotos->isNotEmpty())
@php $carouselId = 'villagePhotoCarousel_' . uniqid(); @endphp
<div class="card border-0 shadow-sm mb-4 overflow-hidden">
    <div id="{{ $carouselId }}" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="5000">
        <div class="carousel-indicators">
            @foreach($villagePhotos as $i => $photo)
                <button type="button" data-bs-target="#{{ $carouselId }}" data-bs-slide-to="{{ $i }}" class="{{ $i === 0 ? 'active' : '' }}" aria-label="Photo {{ $i + 1 }}"></button>
            @endforeach
        </div>
        <div class="carousel-inner" style="max-height:400px;">
            @foreach($villagePhotos as $i => $photo)
                <div class="carousel-item {{ $i === 0 ? 'active' : '' }}">
                    <img src="{{ $photo->photo_url }}" class="d-block w-100" alt="Village Photo" style="height:400px;object-fit:cover;">
                    @if($photo->caption)
                        <div class="carousel-caption d-none d-md-block">
                            <p class="mb-0">{{ $photo->caption }}</p>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
        @if($villagePhotos->count() > 1)
            <button class="carousel-control-prev" type="button" data-bs-target="#{{ $carouselId }}" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#{{ $carouselId }}" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        @endif
    </div>
</div>
@endif
