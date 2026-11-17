@extends('layouts.app', ['title' => $title])

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ $title }}</h3>
        </div>
        <form method="POST" action="{{ route($routeName) }}">
            @csrf
            <div class="card-body">
                @foreach($fields as $field)
                    @php
                        $name = $field['name'];
                        $multiple = !empty($field['multiple']);
                        $selected = old($name, $multiple ? [] : '');
                    @endphp
                    <div class="mb-3">
                        <label class="form-label" for="{{ $name }}">{{ $field['label'] }}</label>
                        @if($field['type'] === 'select')
                            <select class="form-select @error($name) is-invalid @enderror" id="{{ $name }}" name="{{ $name }}{{ $multiple ? '[]' : '' }}" @if($multiple) multiple size="6" @endif required>
                                @unless($multiple)
                                    <option value="">Selecciona una opción</option>
                                @endunless
                                @foreach($field['options'] as $value => $label)
                                    <option value="{{ $value }}" @selected($multiple ? in_array((string) $value, array_map('strval', (array) $selected), true) : (string) $selected === (string) $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        @elseif($field['type'] === 'textarea')
                            <textarea class="form-control @error($name) is-invalid @enderror" id="{{ $name }}" name="{{ $name }}" maxlength="1028" required>{{ old($name) }}</textarea>
                        @else
                            <input class="form-control @error($name) is-invalid @enderror" id="{{ $name }}" name="{{ $name }}" type="{{ $field['type'] }}" value="{{ old($name) }}" @if(isset($field['step'])) step="{{ $field['step'] }}" @endif required>
                        @endif
                        @error($name)<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                @endforeach
            </div>
            <div class="card-footer d-flex gap-2">
                <button class="btn btn-primary" type="submit"><i class="bi bi-play-fill me-1"></i>Ejecutar gestor</button>
                <a class="btn btn-outline-secondary" href="{{ route('dashboard') }}">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
