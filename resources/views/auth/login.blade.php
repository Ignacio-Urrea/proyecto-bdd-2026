@extends('layouts.app', ['title' => 'Iniciar sesión'])

@section('content')
    <div class="panel" style="max-width: 520px; margin: 3rem auto;">
        <h1>Iniciar sesión</h1>
        <form method="POST" action="{{ route('login.store') }}">
            @csrf
            <label for="NOMBRE_USUARIO">Nombre de usuario</label>
            <input id="NOMBRE_USUARIO" name="NOMBRE_USUARIO" type="text" maxlength="16" value="{{ old('NOMBRE_USUARIO') }}" autocomplete="username" required autofocus>
            @error('NOMBRE_USUARIO')<div class="errors">{{ $message }}</div>@enderror

            <label for="password">Contraseña</label>
            <input id="password" name="password" type="password" autocomplete="current-password" required>
            @error('NOMBRE_USUARIO')<div class="errors">{{ $message }}</div>@enderror

            <div class="form-actions">
                <button type="submit">Ingresar</button>
            </div>
        </form>
    </div>
@endsection
