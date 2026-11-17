@extends('layouts.app', ['title' => $title])

@section('content')
    <h1>{{ $title }}</h1>
    <div class="table-wrap">
        <table>
            <thead>
            <tr>@foreach($columns as $label)<th>{{ $label }}</th>@endforeach</tr>
            </thead>
            <tbody>
            @forelse($rows as $row)
                <tr>
                    @foreach($columns as $key => $label)
                        <td>{{ $row->{$key} ?? '' }}</td>
                    @endforeach
                </tr>
            @empty
                <tr><td colspan="{{ count($columns) }}">No hay datos para el reporte.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
