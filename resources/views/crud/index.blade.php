@extends('layouts.app', ['title' => $title])

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ $title }}</h3>
            <div class="card-tools">
                <a class="btn btn-primary btn-sm" href="{{ route($routeName.'.create') }}">
                    <i class="bi bi-plus-lg me-1"></i>Nuevo registro
                </a>
            </div>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover align-middle mb-0">
            <thead>
            <tr>
                <th>ID</th>
                @foreach($fields as $field)
                    <th>{{ $field['label'] }}</th>
                @endforeach
                <th>Acciones</th>
            </tr>
            </thead>
            <tbody>
            @forelse($records as $record)
                <tr>
                    <td>{{ $record->ID }}</td>
                    @foreach($fields as $field)
                        @php($value = $record->{$field['name']})
                        <td>
                            @if(in_array($field['name'], ['HABILITADA', 'EJECUTADA', 'ADENDUM'], true))
                                {{ (int) $value === 1 ? 'Sí' : 'No' }}
                            @else
                                {{ $value }}
                            @endif
                        </td>
                    @endforeach
                    <td class="text-nowrap">
                        <a class="btn btn-outline-info btn-sm" href="{{ route($routeName.'.show', $record->ID) }}">Ver</a>
                        <a class="btn btn-outline-primary btn-sm" href="{{ route($routeName.'.edit', $record->ID) }}">Editar</a>
                        <a class="btn btn-outline-danger btn-sm" href="{{ route($routeName.'.delete', $record->ID) }}">Eliminar</a>
                    </td>
                </tr>
            @empty
                <tr><td class="text-center text-body-secondary py-4" colspan="{{ count($fields) + 2 }}">No hay registros.</td></tr>
            @endforelse
            </tbody>
            </table>
        </div>
        @if($records->hasPages())
            <div class="card-footer clearfix">
                {{ $records->links() }}
            </div>
        @endif
    </div>
@endsection
