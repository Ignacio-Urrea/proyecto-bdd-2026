@extends('layouts.app', ['title' => $title])

@section('content')
    <div class="panel">
        <h1>{{ $title }}</h1>
        <form method="POST" action="{{ route($routeName) }}">
            @csrf
            @foreach($fields as $field)
                @php($name = $field['name'])
                <label for="{{ $name }}">{{ $field['label'] }}</label>
                @if($field['type'] === 'select')
                    <select id="{{ $name }}" name="{{ $name }}{{ !empty($field['multiple']) ? '[]' : '' }}" @if(!empty($field['multiple'])) multiple size="6" @endif required>
                        @unless(!empty($field['multiple']))
                            <option value="">Selecciona una opción</option>
                        @endunless
                        @foreach($field['options'] as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                @elseif($field['type'] === 'textarea')
                    <textarea id="{{ $name }}" name="{{ $name }}" maxlength="1028" required></textarea>
                @else
                    <input id="{{ $name }}" name="{{ $name }}" type="{{ $field['type'] }}" @if(isset($field['step'])) step="{{ $field['step'] }}" @endif required>
                @endif
                @error($name)<div class="errors">{{ $message }}</div>@enderror
            @endforeach
            <div class="form-actions">
                <button type="submit">Ejecutar gestor</button>
                <a href="{{ route('dashboard') }}">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
