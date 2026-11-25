@extends('layouts.app', ['title' => $title])

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ $title }}</h3>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover align-middle mb-0">
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
                <tr><td class="text-center text-body-secondary py-4" colspan="{{ count($columns) }}">No hay datos para el reporte.</td></tr>
            @endforelse
            </tbody>
            </table>
        </div>
    </div>
@endsection
