@extends('layouts.app', ['title' => 'Ver '.$title])

@section('content')
    <div class="panel">
        <div class="toolbar">
            <h1>{{ $title }} #{{ $record->ID }}</h1>
            <div class="actions">
                <a href="{{ route($routeName.'.edit', $record->ID) }}">Editar</a>
                <a href="{{ route($routeName.'.index') }}">Volver al listado</a>
            </div>
        </div>
        <dl>
            @foreach($fields as $field)
                @php($value = $record->{$field['name']})
                <dt>{{ $field['label'] }}</dt>
                <dd>
                    @if(in_array($field['name'], ['HABILITADA', 'EJECUTADA', 'ADENDUM'], true))
                        {{ (int) $value === 1 ? 'Sí' : 'No' }}
                    @else
                        {{ $value }}
                    @endif
                </dd>
            @endforeach
        </dl>
    </div>
@endsection
