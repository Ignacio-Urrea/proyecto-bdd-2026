@extends('layouts.app', ['title' => 'Eliminar '.$title])

@section('content')
    <div class="panel">
        <h1>Eliminar: {{ $title }} #{{ $record->ID }}</h1>
        <p>¿Confirmas que deseas eliminar este registro? Esta acción no se puede deshacer.</p>
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
        <form method="POST" action="{{ route($routeName.'.destroy', $record->ID) }}">
            @csrf
            @method('DELETE')
            <div class="form-actions">
                <button class="danger" type="submit">Confirmar eliminación</button>
                <a href="{{ route($routeName.'.index') }}">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
