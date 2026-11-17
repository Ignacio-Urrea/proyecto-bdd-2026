@extends('layouts.app', ['title' => 'Eliminar '.$title])

@section('content')
    <div class="card border-danger">
        <div class="card-header text-bg-danger">
            <h3 class="card-title">Eliminar: {{ $title }} #{{ $record->ID }}</h3>
        </div>
        <div class="card-body">
            <p>¿Confirmas que deseas eliminar este registro? Esta acción no se puede deshacer.</p>
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
        <form method="POST" action="{{ route($routeName.'.destroy', $record->ID) }}">
            @csrf
            @method('DELETE')
            <div class="card-footer d-flex gap-2">
                <button class="btn btn-danger" type="submit"><i class="bi bi-trash me-1"></i>Confirmar eliminación</button>
                <a class="btn btn-outline-secondary" href="{{ route($routeName.'.index') }}">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
