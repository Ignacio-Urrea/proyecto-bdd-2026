<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'IMAJ' }} | Gestión Radiológica</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="{{ auth()->check() ? 'layout-fixed sidebar-expand-lg bg-body-tertiary' : 'login-page bg-body-secondary' }}">
@auth
    @php($currentRole = Auth::user()->rol?->NOMBRE_ROL)
    <div class="app-wrapper">
        <nav class="app-header navbar navbar-expand bg-body">
            <div class="container-fluid">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button" aria-label="Mostrar u ocultar menú">
                            <i class="bi bi-list"></i>
                        </a>
                    </li>
                    <li class="nav-item d-none d-md-block">
                        <a class="nav-link" href="{{ route('dashboard') }}">Gestión Radiológica IMAJ</a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <span class="nav-link">{{ Auth::user()->NOMBRE_USUARIO }} · {{ $currentRole }}</span>
                    </li>
                    <li class="nav-item">
                        <form class="logout-form" method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="btn btn-outline-secondary btn-sm" type="submit">Cerrar sesión</button>
                        </form>
                    </li>
                </ul>
            </div>
        </nav>

        <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
            <div class="sidebar-brand">
                <a class="brand-link" href="{{ route('dashboard') }}">
                    <span class="brand-text fw-light">IMAJ · Clínica Radiológica</span>
                </a>
            </div>
            <div class="sidebar-wrapper">
                <nav class="mt-2">
                    <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu">
                        <li class="nav-header">MENÚ PRINCIPAL</li>
                        <li class="nav-item">
                            <a class="nav-link{{ request()->routeIs('dashboard') ? ' active' : '' }}" href="{{ route('dashboard') }}">
                                <i class="nav-icon bi bi-speedometer2"></i>
                                <p>Inicio</p>
                            </a>
                        </li>
                        @foreach(config('imaj.navigation') as $item)
                            @if(in_array(strtoupper((string) $currentRole), config('imaj.permissions.'.$item['permission'], []), true))
                                <li class="nav-item">
                                    <a class="nav-link{{ request()->routeIs($item['route']) ? ' active' : '' }}" href="{{ route($item['route']) }}">
                                        <i class="nav-icon bi bi-circle"></i>
                                        <p>{{ $item['label'] }}</p>
                                    </a>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                </nav>
            </div>
        </aside>

        <main class="app-main">
            <div class="app-content">
                <div class="container-fluid py-3">
                    @if(session('status'))
                        <div class="alert alert-success alert-dismissible" role="alert">
                            {{ session('status') }}
                            <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                        </div>
                    @endif
                    @if($errors->any())
                        <div class="alert alert-danger" role="alert">
                            <strong>Revisa los datos ingresados:</strong>
                            <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                        </div>
                    @endif
                    @yield('content')
                </div>
            </div>
            <footer class="app-footer">
                <strong>Gestión Radiológica IMAJ</strong>
                <span class="float-end d-none d-sm-inline">Sistema de gestión clínica</span>
            </footer>
        </main>
    </div>
@else
    <div class="login-box">
        @if(session('status'))
            <div class="alert alert-success" role="alert">{{ session('status') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger" role="alert">
                <strong>Revisa los datos ingresados:</strong>
                <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif
        @yield('content')
    </div>
@endauth
</body>
</html>
