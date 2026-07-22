@extends('layouts.app')

@section('title', 'No Location Data')

@section('content')
<div class="d-flex justify-content-center align-items-center" style="min-height: 400px;">
    <div class="text-center">
        <i class="bi bi-geo-alt text-muted" style="font-size: 4rem;"></i>
        <h3 class="mt-3 text-muted">No Location Data Available</h3>
        <p class="text-muted">
            Your household does not have location coordinates on record.<br>
            Please contact your village secretary to update this information.
        </p>
        <a href="{{ route('dashboard.resident') }}" class="btn btn-primary">
            <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
        </a>
    </div>
</div>
@endsection
