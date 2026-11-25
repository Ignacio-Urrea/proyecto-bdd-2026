<!doctype html>
<html lang="es" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="color-scheme" content="light dark">
    <title>@yield('title', $title ?? 'IMAJ') | Gestión Radiológica</title>

    {{-- Aplica tema y tamaño de texto guardados antes de pintar la página (evita parpadeos) --}}
    <script>
        (function () {
            try {
                var theme = localStorage.getItem('imaj-theme') || 'auto';
                var resolved = theme === 'auto'
                    ? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')
                    : theme;
                document.documentElement.setAttribute('data-bs-theme', resolved);
                document.documentElement.setAttribute('data-theme-pref', theme);
                document.documentElement.setAttribute('data-text-size', localStorage.getItem('imaj-text-size') || 'md');
            } catch (e) {}
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="{{ auth()->check() ? 'layout-fixed sidebar-expand-lg' : 'login-page' }}">

<a class="skip-link" href="#contenido-principal">Saltar al contenido principal</a>

@auth
    @php
        $user        = Auth::user();
        $currentRole = $user->rol?->NOMBRE_ROL;
        $roleKey     = strtoupper((string) $currentRole);
        $initials    = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr((string) $user->NOMBRE_USUARIO, 0, 2));

        // Icono automático según el nombre del módulo (se puede forzar con 'icon' en config/imaj.php).
        // El orden importa: las palabras más específicas van primero.
        $iconMap = [
            'gestor'      => 'bi-kanban',
            'reporte'     => 'bi-bar-chart-line',
            'detalle'     => 'bi-list-check',
            'presupuesto' => 'bi-calculator',
            'prestac'     => 'bi-clipboard2-pulse',
            'paciente'    => 'bi-people',
            'orden'       => 'bi-file-earmark-medical',
            'examen'      => 'bi-clipboard2-check',
            'informe'     => 'bi-file-earmark-text',
            'imagen'      => 'bi-image',
            'pago'        => 'bi-credit-card',
            'ingreso'     => 'bi-cash-coin',
            'clinica'     => 'bi-hospital',
            'sucursal'    => 'bi-shop',
            'ciudad'      => 'bi-buildings',
            'region'      => 'bi-geo-alt',
            'direccion'   => 'bi-signpost-2',
            'usuario'     => 'bi-person-gear',
            'rol'         => 'bi-shield-lock',
        ];

        $iconFor = function (array $item) use ($iconMap): string {
            if (! empty($item['icon'])) {
                return trim(preg_replace('/^bi\s+/', '', $item['icon']));
            }
            $needle = \Illuminate\Support\Str::lower(\Illuminate\Support\Str::ascii(($item['label'] ?? '') . ' ' . ($item['route'] ?? '')));
            foreach ($iconMap as $word => $icon) {
                if (\Illuminate\Support\Str::contains($needle, $word)) {
                    return $icon;
                }
            }
            return 'bi-circle';
        };

        // Solo módulos con ruta existente y permitidos para el rol actual, agrupados por 'group' (opcional).
        $menuGroups = collect(config('imaj.navigation', []))
            ->filter(fn ($item) => \Illuminate\Support\Facades\Route::has($item['route'])
                && in_array($roleKey, config('imaj.permissions.' . $item['permission'], []), true))
            ->groupBy(fn ($item) => $item['group'] ?? 'MÓDULOS');

        $isActive = function (array $item): bool {
            $patterns = [$item['route']];
            if (\Illuminate\Support\Str::contains($item['route'], '.')) {
                $patterns[] = \Illuminate\Support\Str::beforeLast($item['route'], '.') . '.*';
            }
            return request()->routeIs(...$patterns);
        };
    @endphp

    <div class="app-wrapper">

        {{-- ============ BARRA SUPERIOR ============ --}}
        <nav class="app-header navbar navbar-expand bg-body border-bottom" aria-label="Barra superior">
            <div class="container-fluid">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button"
                           aria-label="Mostrar u ocultar el menú lateral">
                            <i class="bi bi-list fs-4" aria-hidden="true"></i>
                        </a>
                    </li>
                    <li class="nav-item d-none d-md-block">
                        <a class="nav-link fw-semibold" href="{{ route('dashboard') }}">
                            <i class="bi bi-activity me-1 text-primary" aria-hidden="true"></i>Gestión Radiológica IMAJ
                        </a>
                    </li>
                </ul>

                <ul class="navbar-nav ms-auto align-items-center gap-1">

                    {{-- Accesibilidad: tamaño de texto --}}
                    <li class="nav-item">
                        <div class="btn-group btn-group-sm" role="group" aria-label="Tamaño del texto">
                            <button type="button" class="btn btn-outline-secondary" data-text-size-step="-1"
                                    aria-label="Reducir tamaño del texto" title="Reducir texto">
                                <span aria-hidden="true" style="font-size:.8em">A</span>
                            </button>
                            <button type="button" class="btn btn-outline-secondary" data-text-size-step="1"
                                    aria-label="Aumentar tamaño del texto" title="Aumentar texto">
                                <span aria-hidden="true" style="font-size:1.15em">A</span>
                            </button>
                        </div>
                    </li>

                    {{-- Apariencia: claro / oscuro / automático --}}
                    <li class="nav-item dropdown">
                        <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button"
                                data-bs-toggle="dropdown" aria-expanded="false" aria-label="Cambiar apariencia">
                            <i class="bi bi-circle-half" aria-hidden="true"></i>
                            <span class="d-none d-lg-inline ms-1">Apariencia</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><button class="dropdown-item" type="button" data-theme-choice="light">
                                <i class="bi bi-sun me-2" aria-hidden="true"></i>Claro</button></li>
                            <li><button class="dropdown-item" type="button" data-theme-choice="dark">
                                <i class="bi bi-moon-stars me-2" aria-hidden="true"></i>Oscuro</button></li>
                            <li><button class="dropdown-item" type="button" data-theme-choice="auto">
                                <i class="bi bi-circle-half me-2" aria-hidden="true"></i>Automático (sistema)</button></li>
                        </ul>
                    </li>

                    {{-- Menú de usuario --}}
                    <li class="nav-item dropdown">
                        <button class="btn user-chip dropdown-toggle" type="button" data-bs-toggle="dropdown"
                                aria-expanded="false" aria-label="Menú de usuario">
                            <span class="avatar" aria-hidden="true">{{ $initials }}</span>
                            <span class="d-none d-md-inline text-start lh-sm">
                                <span class="d-block fw-semibold">{{ $user->NOMBRE_USUARIO }}</span>
                                <small class="text-body-secondary">{{ $currentRole }}</small>
                            </span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end p-0 user-menu">
                            <div class="p-3 border-bottom">
                                <div class="fw-semibold">{{ $user->NOMBRE_USUARIO }}</div>
                                <small class="text-body-secondary">
                                    <i class="bi bi-shield-check me-1" aria-hidden="true"></i>{{ $currentRole }}
                                </small>
                            </div>
                            <form class="logout-form p-2" method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="dropdown-item rounded" type="submit">
                                    <i class="bi bi-box-arrow-right me-2" aria-hidden="true"></i>Cerrar sesión
                                </button>
                            </form>
                        </div>
                    </li>
                </ul>
            </div>
        </nav>

        {{-- ============ MENÚ LATERAL (dinámico según el rol) ============ --}}
        <aside class="app-sidebar imaj-sidebar shadow" data-bs-theme="dark" aria-label="Menú principal">
            <div class="sidebar-brand">
                <a class="brand-link" href="{{ route('dashboard') }}">
                    <span class="brand-mark" aria-hidden="true"><i class="bi bi-activity"></i></span>
                    <span class="brand-text">IMAJ
                        <small class="d-block fw-normal opacity-75">Clínica Radiológica</small>
                    </span>
                </a>
            </div>
            <div class="sidebar-wrapper">
                <nav class="mt-2" aria-label="Módulos">
                    <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu">

                        <li class="nav-header">MENÚ PRINCIPAL</li>
                        <li class="nav-item">
                            <a class="nav-link{{ request()->routeIs('dashboard') ? ' active' : '' }}"
                               href="{{ route('dashboard') }}"
                               @if(request()->routeIs('dashboard')) aria-current="page" @endif>
                                <i class="nav-icon bi bi-house-door" aria-hidden="true"></i>
                                <p>Inicio</p>
                            </a>
                        </li>

                        @foreach($menuGroups as $groupName => $items)
                            <li class="nav-header">{{ \Illuminate\Support\Str::upper($groupName) }}</li>
                            @foreach($items as $item)
                                @php $active = $isActive($item); @endphp
                                <li class="nav-item">
                                    <a class="nav-link{{ $active ? ' active' : '' }}"
                                       href="{{ route($item['route']) }}"
                                       @if($active) aria-current="page" @endif>
                                        <i class="nav-icon bi {{ $iconFor($item) }}" aria-hidden="true"></i>
                                        <p>{{ $item['label'] }}</p>
                                    </a>
                                </li>
                            @endforeach
                        @endforeach

                    </ul>
                </nav>
            </div>
        </aside>

        {{-- ============ CONTENIDO ============ --}}
        <main class="app-main" id="contenido-principal" tabindex="-1">

            {{-- Encabezado de página: solo aparece si la vista define page_title o pasa $title --}}
            @if($__env->hasSection('page_title') || isset($title))
                <div class="app-content-header">
                    <div class="container-fluid">
                        <div class="row align-items-center">
                            <div class="col-sm-7">
                                <h1 class="mb-0 page-title">@yield('page_title', $title ?? '')</h1>
                                @hasSection('page_subtitle')
                                    <p class="text-body-secondary mb-0">@yield('page_subtitle')</p>
                                @endif
                            </div>
                            <div class="col-sm-5">
                                @hasSection('breadcrumb')
                                    <nav aria-label="Ruta de navegación">
                                        <ol class="breadcrumb float-sm-end mb-0">
                                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                                            @yield('breadcrumb')
                                        </ol>
                                    </nav>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="app-content">
                <div class="container-fluid py-3">

                    @if(session('status'))
                        <div class="alert alert-success alert-dismissible d-flex align-items-start gap-2" role="status">
                            <i class="bi bi-check-circle-fill mt-1" aria-hidden="true"></i>
                            <div class="flex-grow-1">{{ session('status') }}</div>
                            <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Cerrar mensaje"></button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger d-flex align-items-start gap-2" role="alert">
                            <i class="bi bi-exclamation-triangle-fill mt-1" aria-hidden="true"></i>
                            <div>
                                <strong>Revisa los datos ingresados:</strong>
                                <ul class="mb-0 mt-1">
                                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    @yield('content')
                </div>
            </div>
        </main>

        <footer class="app-footer">
            <strong>Gestión Radiológica IMAJ</strong>
            <span class="float-end d-none d-sm-inline">Sistema de gestión clínica</span>
        </footer>
    </div>

@else
    {{-- ============ PÁGINAS SIN SESIÓN (LOGIN) ============ --}}
    <main class="login-box" id="contenido-principal" tabindex="-1">
        <div class="text-center mb-3">
            <span class="brand-mark brand-mark-lg" aria-hidden="true"><i class="bi bi-activity"></i></span>
            <p class="h4 mt-2 mb-0">IMAJ</p>
            <p class="text-body-secondary">Gestión Radiológica</p>
        </div>

        @if(session('status'))
            <div class="alert alert-success d-flex align-items-start gap-2" role="status">
                <i class="bi bi-check-circle-fill mt-1" aria-hidden="true"></i>
                <div>{{ session('status') }}</div>
            </div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger d-flex align-items-start gap-2" role="alert">
                <i class="bi bi-exclamation-triangle-fill mt-1" aria-hidden="true"></i>
                <div>
                    <strong>Revisa los datos ingresados:</strong>
                    <ul class="mb-0 mt-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            </div>
        @endif

        @yield('content')
    </main>
@endauth

{{-- Preferencias de accesibilidad (tema y tamaño de texto) --}}
<script>
    (function () {
        var root = document.documentElement;
        var sizes = ['sm', 'md', 'lg', 'xl'];

        function applyTheme(pref) {
            var resolved = pref === 'auto'
                ? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')
                : pref;
            root.setAttribute('data-bs-theme', resolved);
            root.setAttribute('data-theme-pref', pref);
            try { localStorage.setItem('imaj-theme', pref); } catch (e) {}
        }

        document.querySelectorAll('[data-theme-choice]').forEach(function (btn) {
            btn.addEventListener('click', function () { applyTheme(btn.dataset.themeChoice); });
        });

        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function () {
            if (root.getAttribute('data-theme-pref') === 'auto') applyTheme('auto');
        });

        document.querySelectorAll('[data-text-size-step]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var current = sizes.indexOf(root.getAttribute('data-text-size') || 'md');
                var next = Math.min(sizes.length - 1, Math.max(0, current + parseInt(btn.dataset.textSizeStep, 10)));
                root.setAttribute('data-text-size', sizes[next]);
                try { localStorage.setItem('imaj-text-size', sizes[next]); } catch (e) {}
            });
        });
    })();
</script>
@stack('scripts')
</body>
</html>