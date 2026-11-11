{{-- Session flash alerts (success-style: "status" and "success") and validation errors. --}}
@foreach (['status', 'success'] as $flashKey)
    @if (session($flashKey))
        <div class="container">
            <div class="alert alert-success" role="alert">
                {{ session($flashKey) }}
            </div>
        </div>
    @endif
@endforeach
@if ($errors->any())
    <div class="container">
        <div class="alert alert-warning">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
