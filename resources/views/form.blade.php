@extends('layouts.bs05')

@section('title', 'Property Search')

@section('content')
    <h1>Simple Form Search</h1>

    <form action="/search" method="GET">
        <!-- Name -->
        <div class="row mb-3 align-items-center">
            <div class="col-md-2">
                <label class="form-label mb-0">Name</label>
            </div>
            <div class="col-md-10">
                <input type="text" class="form-control" name="name" value="{{ request('name') }}" placeholder="Property name...">
            </div>
        </div>

        <x-field label="Price" name="price" />
        <x-field label="Bedrooms" name="bedrooms" />
        <x-field label="Bathrooms" name="bathrooms" />
        <x-field label="Storeys" name="storeys" />
        <x-field label="Garages" name="garages" />

        <!-- Submit -->
        <div class="row">
            <div class="col-md-2"></div>
            <div class="col-md-10">
                <button type="submit" class="btn btn-primary">Search</button>
                <a href="/search" class="btn btn-outline-secondary">Reset</a>
            </div>
        </div>
    </form>

    <!-- Search Results -->
    @if(isset($properties))
        <hr class="my-4">
        <h4>Results: {{ $properties->total() }}</h4>
        
        @forelse($properties as $property)
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">{{ $property->name }}</h5>
                    <p class="card-text">
                        <strong>${{ number_format($property->price) }}</strong><br>
                        {{ $property->bedrooms }} bed &bull; 
                        {{ $property->bathrooms }} bath &bull; 
                        {{ $property->storeys }} storey &bull; 
                        {{ $property->garages }} garage
                    </p>
                </div>
            </div>
        @empty
            <p class="text-muted">Nothing found.</p>
        @endforelse

        {{ $properties->links() }}
    @endif
@endsection