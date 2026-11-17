@extends('layouts.app', ['title' => 'Mantenedores'])

@section('content')
    <h1>Mantenedores</h1>
    <p>Selecciona un módulo según tu perfil de acceso.</p>
    <div class="card-grid">
        @php($currentRole = Auth::user()->rol?->NOMBRE_ROL)
        @foreach(config('imaj.navigation') as $item)
            @if(in_array(strtoupper((string) $currentRole), config('imaj.permissions.'.$item['permission'], []), true))
                <div class="card">
                    <a href="{{ route($item['route']) }}">{{ $item['label'] }}</a>
                </div>
            @endif
        @endforeach
    </div>
@endsection
