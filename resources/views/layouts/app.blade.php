<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'IMAJ' }} | Gestión Radiológica</title>
    <style>
        :root { color-scheme: light; font-family: system-ui, sans-serif; color: #172033; background: #f3f6fa; }
        * { box-sizing: border-box; }
        body { margin: 0; }
        header { background: #12304a; color: white; padding: 1rem max(1rem, calc((100% - 1180px) / 2)); }
        header a { color: #d7efff; text-decoration: none; margin-right: 1rem; white-space: nowrap; }
        header nav { display: flex; flex-wrap: wrap; gap: .55rem 0; margin-top: .8rem; }
        main { max-width: 1180px; margin: 2rem auto; padding: 0 1rem; }
        h1 { margin-top: 0; }
        a.button, button { display: inline-block; border: 0; border-radius: .4rem; padding: .6rem .9rem; background: #1769aa; color: white; text-decoration: none; cursor: pointer; font: inherit; }
        button.danger { background: #a33434; }
        .toolbar { display: flex; justify-content: space-between; align-items: center; gap: 1rem; margin-bottom: 1rem; }
        .table-wrap { overflow-x: auto; background: white; border-radius: .5rem; box-shadow: 0 2px 10px #17203312; }
        table { border-collapse: collapse; width: 100%; }
        th, td { padding: .75rem; border-bottom: 1px solid #e3e8ef; text-align: left; vertical-align: top; }
        th { background: #eaf0f6; white-space: nowrap; }
        td.actions { white-space: nowrap; }
        .card-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 1rem; }
        .card, .panel { background: white; border-radius: .5rem; padding: 1rem; box-shadow: 0 2px 10px #17203312; }
        .card a { color: #1769aa; text-decoration: none; font-weight: 650; }
        form label { display: block; font-weight: 600; margin: 1rem 0 .35rem; }
        input, select, textarea { width: 100%; max-width: 680px; padding: .6rem; border: 1px solid #b9c5d3; border-radius: .35rem; font: inherit; }
        textarea { min-height: 9rem; }
        .form-actions { display: flex; gap: .6rem; margin-top: 1.2rem; }
        .form-actions a { padding: .6rem .9rem; color: #26384a; }
        .notice { padding: .8rem 1rem; margin-bottom: 1rem; border-radius: .35rem; background: #e4f5e9; color: #17572b; }
        .errors { padding: .8rem 1rem; margin-bottom: 1rem; border-radius: .35rem; background: #ffebeb; color: #842626; }
        .pagination { display: flex; gap: .5rem; list-style: none; padding: 1rem; margin: 0; }
        .pagination a, .pagination span { color: #1769aa; }
    </style>
</head>
<body>
<header>
    <strong><a href="{{ route('home') }}">Gestión Radiológica IMAJ</a></strong>
    @auth
        @php($currentRole = Auth::user()->rol?->NOMBRE_ROL)
        <nav>
            <a href="{{ route('dashboard') }}">Inicio</a>
            @foreach(config('imaj.navigation') as $item)
                @if(in_array(strtoupper((string) $currentRole), config('imaj.permissions.'.$item['permission'], []), true))
                    <a href="{{ route($item['route']) }}">{{ $item['label'] }}</a>
                @endif
            @endforeach
            <span>{{ Auth::user()->NOMBRE_USUARIO }} ({{ $currentRole }})</span>
            <form method="POST" action="{{ route('logout') }}" style="display:inline">
                @csrf
                <button type="submit">Cerrar sesión</button>
            </form>
        </nav>
    @else
        <nav><a href="{{ route('login') }}">Iniciar sesión</a></nav>
    @endauth
</header>
<main>
    @if (session('status'))
        <div class="notice">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="errors">
            <strong>Revisa los datos ingresados:</strong>
            <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif
    @yield('content')
</main>
</body>
</html>
