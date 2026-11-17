@extends('layouts.app', ['title' => $title])

@section('content')
    <div class="toolbar">
        <h1>{{ $title }}</h1>
        <a class="button" href="{{ route($routeName.'.create') }}">Nuevo registro</a>
    </div>
    <div class="table-wrap">
        <table>
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
                    <td class="actions">
                        <a href="{{ route($routeName.'.show', $record->ID) }}">Ver</a>
                        <a href="{{ route($routeName.'.edit', $record->ID) }}">Editar</a>
                        <a class="danger" href="{{ route($routeName.'.delete', $record->ID) }}">Eliminar</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="{{ count($fields) + 2 }}">No hay registros.</td></tr>
            @endforelse
            </tbody>
        </table>
        {{ $records->links() }}
    </div>
@endsection
