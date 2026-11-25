@extends('layouts.app')

@section('title', 'Create Announcement')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title mb-1">Create Announcement</h1>
        <p class="page-subtitle text-muted">Create a new announcement for {{ $village->village_name ?? 'your village' }}</p>
    </div>
    <a href="{{ route('announcements.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back
    </a>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card shadow-sm">
    <div class="card-body">
        <form action="{{ route('announcements.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            @include('announcements._form-fields', ['context' => 'create', 'idPrefix' => ''])

            <hr class="my-4">

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('announcements.index') }}" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> Create Announcement
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
