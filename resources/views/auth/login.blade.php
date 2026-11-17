@extends('layouts.app', ['title' => 'Iniciar sesión'])

@section('content')
    <div class="card card-outline card-primary mb-0">
        <div class="card-header text-center">
            <span class="h3"><b>Gestión Radiológica</b></span>
            <div class="text-body-secondary">IMAJ</div>
        </div>
        <form method="POST" action="{{ route('login.store') }}">
            @csrf
            <div class="card-body login-card-body">
                <p class="login-box-msg">Inicia sesión para comenzar</p>
                <div class="input-group mb-3">
                    <div class="form-floating">
                        <input class="form-control @error('NOMBRE_USUARIO') is-invalid @enderror" id="NOMBRE_USUARIO" name="NOMBRE_USUARIO" type="text" maxlength="16" value="{{ old('NOMBRE_USUARIO') }}" placeholder="Nombre de usuario" autocomplete="username" required autofocus>
                        <label for="NOMBRE_USUARIO">Nombre de usuario</label>
                    </div>
                    <div class="input-group-text"><span class="bi bi-person"></span></div>
                    @error('NOMBRE_USUARIO')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="input-group mb-3">
                    <div class="form-floating">
                        <input class="form-control @error('password') is-invalid @enderror" id="password" name="password" type="password" placeholder="Contraseña" autocomplete="current-password" required>
                        <label for="password">Contraseña</label>
                    </div>
                    <div class="input-group-text"><span class="bi bi-lock"></span></div>
                    @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="d-grid">
                    <button class="btn btn-primary" type="submit">Ingresar</button>
                </div>
            </div>
        </form>
    </div>
@endsection
