@extends('layouts.app', ['title' => 'Mantenedores'])

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h1 class="h3 mb-1">Mantenedores</h1>
            <p class="text-body-secondary mb-0">Selecciona un módulo según tu perfil de acceso.</p>
        </div>
    </div>
    <div class="row g-3">
        @php($currentRole = Auth::user()->rol?->NOMBRE_ROL)
        @foreach(config('imaj.navigation') as $item)
            @if(in_array(strtoupper((string) $currentRole), config('imaj.permissions.'.$item['permission'], []), true))
                <div class="col-sm-6 col-xl-3">
                    <a class="card text-decoration-none h-100" href="{{ route($item['route']) }}">
                        <div class="card-body d-flex align-items-center gap-3">
                            <span class="fs-4 text-primary"><i class="bi bi-grid"></i></span>
                            <span class="fw-semibold text-body">{{ $item['label'] }}</span>
                        </div>
                    </a>
                </div>
            @endif
        @endforeach
    </div>
@endsection
