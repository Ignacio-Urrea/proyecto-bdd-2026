@extends('layouts.app', ['title' => $action.' '.$title])

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ $action }}: {{ $title }}</h3>
        </div>
        <form method="POST" action="{{ $record ? route($routeName.'.update', $record->ID) : route($routeName.'.store') }}">
            @csrf
            @if($record)
                @method('PUT')
            @endif
            <div class="card-body">
                @foreach($fields as $field)
                    @php
                        $name = $field['name'];
                        $value = $field['type'] === 'password' ? '' : old($name, $record?->{$name});
                        if ($field['type'] === 'date' && $value) {
                            $value = $value instanceof \DateTimeInterface ? $value->format('Y-m-d') : substr((string) $value, 0, 10);
                        }
                    @endphp
                    <div class="mb-3">
                        <label class="form-label" for="{{ $name }}">{{ $field['label'] }}</label>
                        @if($field['type'] === 'select')
                            <select class="form-select @error($name) is-invalid @enderror" id="{{ $name }}" name="{{ $name }}" @if($field['required']) required @endif>
                                <option value="">Selecciona una opción</option>
                                @foreach($field['options'] as $optionValue => $optionLabel)
                                    <option value="{{ $optionValue }}" @selected((string) $value === (string) $optionValue)>{{ $optionLabel }}</option>
                                @endforeach
                            </select>
                        @elseif($field['type'] === 'textarea')
                            <textarea class="form-control @error($name) is-invalid @enderror" id="{{ $name }}" name="{{ $name }}" maxlength="{{ $field['maxlength'] ?? 1028 }}" @if($field['required']) required @endif>{{ $value }}</textarea>
                        @else
                            <input
                                class="form-control @error($name) is-invalid @enderror"
                                id="{{ $name }}"
                                name="{{ $name }}"
                                type="{{ $field['type'] }}"
                                value="{{ $value }}"
                                @if(isset($field['placeholder'])) placeholder="{{ $field['placeholder'] }}" @endif
                                @if($field['type'] === 'password') autocomplete="new-password" @endif
                                @if(isset($field['step'])) step="{{ $field['step'] }}" @endif
                                @if(isset($field['maxlength'])) maxlength="{{ $field['maxlength'] }}" @endif
                                @if($field['required']) required @endif
                            >
                        @endif
                        @error($name)<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                @endforeach
            </div>
            <div class="card-footer d-flex gap-2">
                <button class="btn btn-primary" type="submit"><i class="bi bi-check-lg me-1"></i>Guardar</button>
                <a class="btn btn-outline-secondary" href="{{ route($routeName.'.index') }}">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
