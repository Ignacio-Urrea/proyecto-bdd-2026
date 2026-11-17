@extends('layouts.app', ['title' => $action.' '.$title])

@section('content')
    <div class="panel">
        <h1>{{ $action }}: {{ $title }}</h1>
        <form method="POST" action="{{ $record ? route($routeName.'.update', $record->ID) : route($routeName.'.store') }}">
            @csrf
            @if($record)
                @method('PUT')
            @endif
            @foreach($fields as $field)
                @php
                    $name = $field['name'];
                    $value = $field['type'] === 'password' ? '' : old($name, $record?->{$name});
                    if ($field['type'] === 'date' && $value) {
                        $value = $value instanceof \DateTimeInterface ? $value->format('Y-m-d') : substr((string) $value, 0, 10);
                    }
                @endphp
                <label for="{{ $name }}">{{ $field['label'] }}</label>
                @if($field['type'] === 'select')
                    <select id="{{ $name }}" name="{{ $name }}" @if($field['required']) required @endif>
                        <option value="">Selecciona una opción</option>
                        @foreach($field['options'] as $optionValue => $optionLabel)
                            <option value="{{ $optionValue }}" @selected((string) $value === (string) $optionValue)>{{ $optionLabel }}</option>
                        @endforeach
                    </select>
                @elseif($field['type'] === 'textarea')
                    <textarea id="{{ $name }}" name="{{ $name }}" maxlength="{{ $field['maxlength'] ?? 1028 }}" @if($field['required']) required @endif>{{ $value }}</textarea>
                @else
                    <input
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
                @error($name)<div class="errors">{{ $message }}</div>@enderror
            @endforeach
            <div class="form-actions">
                <button type="submit">Guardar</button>
                <a href="{{ route($routeName.'.index') }}">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
