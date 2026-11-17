@extends('layouts.app', ['title' => 'Ver '.$title])

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ $title }} #{{ $record->ID }}</h3>
        </div>
        <div class="card-body">
            <dl class="row mb-0">
                @foreach($fields as $field)
                    @php($value = $record->{$field['name']})
                    <dt class="col-sm-3">{{ $field['label'] }}</dt>
                    <dd class="col-sm-9">
                        @if(in_array($field['name'], ['HABILITADA', 'EJECUTADA', 'ADENDUM'], true))
                            {{ (int) $value === 1 ? 'Sí' : 'No' }}
                        @else
                            {{ $value }}
                        @endif
                    </dd>
                @endforeach
            </dl>
        </div>
        <div class="card-footer d-flex gap-2">
            <a class="btn btn-primary" href="{{ route($routeName.'.edit', $record->ID) }}">Editar</a>
            <a class="btn btn-outline-secondary" href="{{ route($routeName.'.index') }}">Volver al listado</a>
        </div>
    </div>
@endsection
