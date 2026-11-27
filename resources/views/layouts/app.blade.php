<!doctype html>
<html lang="es" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="color-scheme" content="dark light">
    <title>@yield('title', $title ?? 'IMAJ') | Gestión Radiológica</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">

    {{-- Aplica tema, tamaño de texto y ancho del menú guardados antes de pintar la página --}}
    <script>
        (function () {
            try {
                var theme = localStorage.getItem('imaj-theme') || 'dark';
                var resolved = theme === 'auto'
                    ? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')
                    : theme;
                document.documentElement.setAttribute('data-bs-theme', resolved);
                document.documentElement.setAttribute('data-theme-pref', theme);
                document.documentElement.setAttribute('data-text-size', localStorage.getItem('imaj-text-size') || 'lg');

                var savedW = parseInt(localStorage.getItem('imaj-sidebar-width'), 10);
                if (savedW >= 290 && savedW <= 480 && window.innerWidth >= 992) {
                    document.documentElement.style.setProperty('--lte-sidebar-width', savedW + 'px');
                }
            } catch (e) {}
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>

        :root,
        .app-wrapper {
            --lte-sidebar-width: 335px;
        }

        @media (max-width: 991.98px) {
            :root,
            .app-wrapper {
                --lte-sidebar-width: min(335px, 86vw);
            }
        }

        :root,
        [data-bs-theme="dark"] {
            --imaj-bg: #131519;
            --imaj-surface: #1b1d21;
            --imaj-surface-elev: #23262c;
            --imaj-sidebar-bg: #17191d;
            --imaj-border: rgba(255, 255, 255, 0.08);
            --imaj-border-strong: rgba(255, 255, 255, 0.16);
            --imaj-text: #f3f4f6;
            --imaj-muted: #9aa2b1;

            --imaj-cream: #F5E6D3;
            --imaj-navy: #28334F;
            --imaj-navy-deep: #1D263D;
            --imaj-blue: #8297D1;
            --imaj-sage: #9FB2B0;
            --imaj-mint: #6BC9C6;
        }

        [data-bs-theme="light"] {
            --imaj-bg: #f1f5f9;
            --imaj-surface: #ffffff;
            --imaj-surface-elev: #f8fafc;
            --imaj-sidebar-bg: #1D263D;
            --imaj-border: rgba(15, 23, 42, 0.10);
            --imaj-border-strong: rgba(15, 23, 42, 0.20);
            --imaj-text: #0f172a;
            --imaj-muted: #64748b;
        }

        /* Escala tipográfica global ampliada + soporte para botones A / A */
        html {
            font-size: 17px;
        }
        html[data-text-size="sm"] {
            font-size: 15.5px;
        }
        html[data-text-size="md"] {
            font-size: 17px;
        }
        html[data-text-size="lg"] {
            font-size: 18px;
        }
        html[data-text-size="xl"] {
            font-size: 19.5px;
        }

        body {
            background-color: var(--imaj-bg);
            color: var(--imaj-text);
            font-size: 1rem;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            font-variant-numeric: tabular-nums;
            -webkit-font-smoothing: antialiased;
        }

        h1, h2, h3,
        .vv-ci-title,
        .vv-cf-title,
        .vv-cs-title,
        .vv-cd-title,
        .vv-g-title,
        .brand-text {
            font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
            font-weight: 700 !important;
            letter-spacing: -0.02em;
        }

        body.imaj-resizing-sidebar,
        body.imaj-resizing-sidebar * {
            cursor: ew-resize !important;
            user-select: none !important;
        }

        /* ---------- Barra Superior ---------- */
        .app-header.navbar {
            background-color: var(--imaj-surface) !important;
            border-bottom: 1px solid var(--imaj-border) !important;
            min-height: 4.15rem;
            padding: 0.55rem 1.25rem;
            box-shadow: 0 6px 20px -12px rgba(0, 0, 0, 0.65);
        }

        .app-header .nav-link {
            color: var(--imaj-text) !important;
            font-size: 1.05rem;
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            border-radius: 0.65rem;
            padding: 0.45rem 0.75rem;
            transition: background-color 0.15s ease, color 0.15s ease;
        }

        .app-header .nav-link:hover {
            background-color: var(--imaj-surface-elev);
            color: var(--imaj-mint) !important;
        }

        .imaj-topbar-brand-ico {
            width: 2rem;
            height: 2rem;
            border-radius: 0.55rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--imaj-mint);
            color: var(--imaj-navy-deep);
            font-size: 1.1rem;
        }

        .imaj-top-btn {
            background-color: var(--imaj-surface-elev);
            border: 1px solid var(--imaj-border-strong);
            color: var(--imaj-text);
            font-size: 0.92rem;
            font-weight: 600;
            padding: 0.45rem 0.85rem;
            border-radius: 0.68rem;
            transition: border-color 0.15s ease, background-color 0.15s ease;
        }

        .imaj-top-btn:hover,
        .imaj-top-btn:focus {
            background-color: var(--imaj-navy);
            border-color: var(--imaj-blue);
            color: var(--imaj-cream);
        }

        /* Chip de Usuario */
        .app-header .user-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.65rem;
            padding: 0.38rem 0.9rem 0.38rem 0.45rem;
            border-radius: 0.85rem;
            background-color: var(--imaj-navy);
            border: 1px solid rgba(130, 151, 209, 0.45);
            color: var(--imaj-cream);
            font-size: 0.94rem;
            transition: border-color 0.15s ease, background-color 0.15s ease;
        }

        .app-header .user-chip:hover,
        .app-header .user-chip:focus {
            background-color: var(--imaj-navy-deep);
            border-color: var(--imaj-mint);
            color: var(--imaj-cream);
        }

        .app-header .user-chip .avatar {
            width: 2.2rem;
            height: 2.2rem;
            border-radius: 0.62rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.88rem;
            font-weight: 800;
            background: var(--imaj-mint);
            color: var(--imaj-navy-deep);
        }

        .app-header .user-chip small {
            color: var(--imaj-blue) !important;
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.03em;
        }

        .app-header .dropdown-menu {
            background-color: var(--imaj-surface);
            border: 1px solid var(--imaj-border-strong);
            border-radius: 0.85rem;
            box-shadow: 0 14px 32px -10px rgba(0, 0, 0, 0.75);
            font-size: 0.96rem;
        }

        .app-header .dropdown-item {
            color: var(--imaj-text);
            padding: 0.55rem 1rem;
            border-radius: 0.55rem;
        }

        .app-header .dropdown-item:hover {
            background-color: var(--imaj-navy);
            color: var(--imaj-cream);
        }

        /* ---------- Menú Lateral (Sidebar amplio y extensible) ---------- */
        .app-sidebar.imaj-sidebar {
            background-color: var(--imaj-sidebar-bg) !important;
            border-right: 1px solid var(--imaj-border);
            width: var(--lte-sidebar-width) !important;
            min-width: var(--lte-sidebar-width);
        }

        .imaj-sidebar-resizer {
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            width: 7px;
            cursor: ew-resize;
            z-index: 1050;
            transition: background-color 0.15s ease;
        }

        .imaj-sidebar-resizer:hover,
        body.imaj-resizing-sidebar .imaj-sidebar-resizer {
            background-color: rgba(107, 201, 198, 0.55);
        }

        @media (max-width: 991.98px) {
            .imaj-sidebar-resizer {
                display: none;
            }
        }

        .imaj-sidebar .sidebar-brand {
            border-bottom: 1px solid var(--imaj-border);
            padding: 1rem 1.25rem;
            min-height: 4.15rem;
            display: flex;
            align-items: center;
        }

        .imaj-sidebar .brand-link {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            text-decoration: none;
            color: #f3f4f6;
            width: 100%;
        }

        .imaj-sidebar .brand-mark {
            width: 2.6rem;
            height: 2.6rem;
            border-radius: 0.75rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            background: var(--imaj-mint);
            color: var(--imaj-navy-deep);
            box-shadow: 0 8px 18px -8px var(--imaj-mint);
            flex-shrink: 0;
        }

        .imaj-sidebar .brand-text {
            font-size: 1.22rem;
            font-weight: 800;
            letter-spacing: 0.02em;
            line-height: 1.15;
            color: #f3f4f6;
        }

        .imaj-sidebar .brand-text small {
            font-size: 0.82rem;
            color: var(--imaj-sage);
            opacity: 1 !important;
        }

        .imaj-sidebar .sidebar-menu {
            padding: 0.55rem 0.8rem 1.75rem;
            gap: 0.28rem;
        }

        .imaj-sidebar .nav-header {
            font-size: 0.78rem;
            font-weight: 800;
            letter-spacing: 0.09em;
            color: var(--imaj-blue) !important;
            padding: 1.05rem 0.9rem 0.4rem;
            margin-top: 0.25rem;
        }

        .imaj-sidebar .sidebar-menu .nav-link {
            display: flex;
            align-items: center;
            gap: 0.82rem;
            padding: 0.74rem 1rem;
            border-radius: 0.78rem;
            font-size: 1rem;
            font-weight: 600;
            color: #d1d5db;
            border: 1px solid transparent;
            transition: background-color 0.15s ease, color 0.15s ease, border-color 0.15s ease;
        }

        .imaj-sidebar .sidebar-menu .nav-link .nav-icon {
            font-size: 1.18rem;
            color: var(--imaj-sage);
            width: 1.5rem;
            text-align: center;
            flex-shrink: 0;
            transition: color 0.15s ease;
        }

        .imaj-sidebar .sidebar-menu .nav-link p {
            margin: 0;
            line-height: 1.3;
            white-space: normal;
            overflow: visible;
            text-overflow: unset;
            flex: 1;
        }

        .imaj-sidebar .sidebar-menu .nav-link:hover {
            background-color: rgba(130, 151, 209, 0.12);
            color: #ffffff;
        }

        .imaj-sidebar .sidebar-menu .nav-link:hover .nav-icon {
            color: var(--imaj-mint);
        }

        .imaj-sidebar .sidebar-menu .nav-link.active {
            background-color: var(--imaj-navy) !important;
            border-color: rgba(130, 151, 209, 0.55);
            color: var(--imaj-cream) !important;
            font-weight: 700;
            box-shadow: 0 8px 20px -12px rgba(0, 0, 0, 0.8);
        }

        .imaj-sidebar .sidebar-menu .nav-link.active .nav-icon {
            color: var(--imaj-mint) !important;
        }

        /* ---------- Área Principal ---------- */
        .app-main {
            background-color: var(--imaj-bg);
            min-height: calc(100vh - 7.5rem);
        }

        .app-content > .container-fluid {
            padding: 1.5rem 1.75rem 2.25rem;
        }

        /* ---------- Alertas Temáticas ---------- */
        .imaj-alert-success {
            background-color: var(--imaj-navy);
            border: 1px solid var(--imaj-mint);
            color: var(--imaj-cream);
            border-radius: 0.9rem;
            padding: 0.95rem 1.25rem;
            font-size: 1rem;
            font-weight: 600;
        }

        .imaj-alert-success i {
            color: var(--imaj-mint);
            font-size: 1.2rem;
        }

        .imaj-alert-danger {
            background-color: var(--imaj-navy);
            border: 1px solid var(--imaj-cream);
            color: var(--imaj-cream);
            border-radius: 0.9rem;
            padding: 0.95rem 1.25rem;
            font-size: 1rem;
        }

        /* ---------- Footer ---------- */
        .app-footer {
            background-color: var(--imaj-surface) !important;
            border-top: 1px solid var(--imaj-border) !important;
            color: var(--imaj-muted) !important;
            padding: 0.95rem 1.75rem;
            font-size: 0.92rem;
        }

        .app-footer strong {
            color: var(--imaj-text);
        }

        /* ---------- Pantalla de Login ---------- */
        body.login-page {
            background-color: var(--imaj-bg);
            color: var(--imaj-text);
        }

        .login-page .brand-mark-lg {
            width: 3.6rem;
            height: 3.6rem;
            border-radius: 1rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            background: var(--imaj-mint);
            color: var(--imaj-navy-deep);
            box-shadow: 0 10px 24px -10px var(--imaj-mint);
        }
    </style>

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

        $canViewMenuItem = static fn (array $item): bool => isset($item['route'], $item['permission'])
            && \Illuminate\Support\Facades\Route::has($item['route'])
            && in_array($roleKey, config('imaj.permissions.' . $item['permission'], []), true);

        $moduleOrder = collect(config('imaj.modules', []))
            ->pluck('route')
            ->flip();

        $menuGroups = collect(config('imaj.navigation', []))
            ->map(function (array $item) use ($canViewMenuItem): ?array {
                if (isset($item['children'])) {
                    $item['children'] = collect($item['children'])
                        ->filter($canViewMenuItem)
                        ->values()
                        ->all();

                    return $item;
                }

                return $canViewMenuItem($item) ? $item : null;
            })
            ->filter(static fn (?array $item): bool => $item !== null
                && (! isset($item['children']) || $item['children'] !== []))
            ->sortBy(static function (array $item) use ($moduleOrder): int {
                if (isset($item['children'])) {
                    return $moduleOrder->count();
                }

                $moduleRoute = \Illuminate\Support\Str::beforeLast($item['route'], '.');

                return $moduleOrder->get($moduleRoute, $moduleOrder->count());
            })
            ->groupBy(fn ($item) => $item['group'] ?? 'MÓDULOS');

        $isActive = function (array $item): bool {
            $patterns = [$item['route']];
            if (\Illuminate\Support\Str::endsWith($item['route'], ['.index', '.create', '.show', '.edit'])) {
                $patterns[] = \Illuminate\Support\Str::beforeLast($item['route'], '.') . '.*';
            }
            return request()->routeIs(...$patterns);
        };
    @endphp

    <div class="app-wrapper">

        {{-- ============ BARRA SUPERIOR ============ --}}
        <nav class="app-header navbar navbar-expand" aria-label="Barra superior">
            <div class="container-fluid">
                <ul class="navbar-nav align-items-center gap-1">
                    <li class="nav-item">
                        <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button"
                           aria-label="Mostrar u ocultar el menú lateral">
                            <i class="bi bi-list fs-4" aria-hidden="true"></i>
                        </a>
                    </li>
                    <li class="nav-item d-none d-md-block">
                        <a class="nav-link fw-bold" href="{{ route('dashboard') }}">
                            <span class="imaj-topbar-brand-ico" aria-hidden="true">
                                <i class="bi bi-activity"></i>
                            </span>
                            <span>Gestión Radiológica IMAJ</span>
                        </a>
                    </li>
                </ul>

                <ul class="navbar-nav ms-auto align-items-center gap-2">

                    {{-- Accesibilidad: tamaño de texto --}}
                    <li class="nav-item">
                        <div class="btn-group" role="group" aria-label="Tamaño del texto">
                            <button type="button" class="btn imaj-top-btn" data-text-size-step="-1"
                                    aria-label="Reducir tamaño del texto" title="Reducir texto">
                                <span aria-hidden="true" style="font-size:.82em">A</span>
                            </button>
                            <button type="button" class="btn imaj-top-btn" data-text-size-step="1"
                                    aria-label="Aumentar tamaño del texto" title="Aumentar texto">
                                <span aria-hidden="true" style="font-size:1.18em">A</span>
                            </button>
                        </div>
                    </li>

                    {{-- Apariencia: claro / oscuro / automático --}}
                    <li class="nav-item dropdown">
                        <button class="btn imaj-top-btn dropdown-toggle" type="button"
                                data-bs-toggle="dropdown" aria-expanded="false" aria-label="Cambiar apariencia">
                            <i class="bi bi-circle-half" aria-hidden="true"></i>
                            <span class="d-none d-lg-inline ms-1">Apariencia</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end p-1">
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
                                <span class="d-block fw-bold">{{ $user->NOMBRE_USUARIO }}</span>
                                <small>{{ $currentRole }}</small>
                            </span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end p-0 user-menu">
                            <div class="p-3 border-bottom" style="border-color: var(--imaj-border) !important;">
                                <div class="fw-bold">{{ $user->NOMBRE_USUARIO }}</div>
                                <small style="color: var(--imaj-mint);">
                                    <i class="bi bi-shield-check me-1" aria-hidden="true"></i>{{ $currentRole }}
                                </small>
                            </div>
                            <form class="logout-form p-2" method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="dropdown-item rounded fw-semibold" type="submit">
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
                        <small class="d-block fw-normal">Clínica Radiológica</small>
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
                                @if(isset($item['children']))
                                    @php $groupActive = collect($item['children'])->contains(fn (array $child): bool => $isActive($child)); @endphp
                                    <li class="nav-item{{ $groupActive ? ' menu-open' : '' }}">
                                        <a class="nav-link{{ $groupActive ? ' active' : '' }}"
                                           href="#"
                                           aria-expanded="{{ $groupActive ? 'true' : 'false' }}">
                                            <i class="nav-icon bi {{ $iconFor($item) }}" aria-hidden="true"></i>
                                            <p>{{ $item['label'] }}<i class="nav-arrow bi bi-chevron-right" aria-hidden="true"></i></p>
                                        </a>
                                        <ul class="nav nav-treeview">
                                            @foreach($item['children'] as $child)
                                                @php $active = $isActive($child); @endphp
                                                <li class="nav-item">
                                                    <a class="nav-link{{ $active ? ' active' : '' }}"
                                                       href="{{ route($child['route']) }}"
                                                       @if($active) aria-current="page" @endif>
                                                        <i class="nav-icon bi bi-circle" aria-hidden="true"></i>
                                                        <p>{{ $child['label'] }}</p>
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </li>
                                @else
                                    @php $active = $isActive($item); @endphp
                                    <li class="nav-item">
                                        <a class="nav-link{{ $active ? ' active' : '' }}"
                                           href="{{ route($item['route']) }}"
                                           @if($active) aria-current="page" @endif>
                                            <i class="nav-icon bi {{ $iconFor($item) }}" aria-hidden="true"></i>
                                            <p>{{ $item['label'] }}</p>
                                        </a>
                                    </li>
                                @endif
                            @endforeach
                        @endforeach

                    </ul>
                </nav>
            </div>
            <div class="imaj-sidebar-resizer" id="imaj-sidebar-resizer" title="Arrastra para ajustar el ancho del menú"></div>
        </aside>

        {{-- ============ CONTENIDO ============ --}}
        <main class="app-main" id="contenido-principal" tabindex="-1">
            <div class="app-content">
                <div class="container-fluid">

                    @if(session('status'))
                        <div class="alert imaj-alert-success alert-dismissible d-flex align-items-center gap-2 mb-4" role="status">
                            <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
                            <div class="flex-grow-1">{{ session('status') }}</div>
                            <button class="btn-close btn-close-white" type="button" data-bs-dismiss="alert" aria-label="Cerrar mensaje"></button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert imaj-alert-danger d-flex align-items-start gap-2 mb-4" role="alert">
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
        <div class="text-center mb-4">
            <span class="brand-mark brand-mark-lg" aria-hidden="true"><i class="bi bi-activity"></i></span>
            <p class="h3 fw-bold mt-3 mb-0">IMAJ</p>
            <p style="color: var(--imaj-sage);">Gestión Radiológica</p>
        </div>

        @if(session('status'))
            <div class="alert imaj-alert-success d-flex align-items-center gap-2 mb-3" role="status">
                <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
                <div>{{ session('status') }}</div>
            </div>
        @endif
        @if($errors->any())
            <div class="alert imaj-alert-danger d-flex align-items-start gap-2 mb-3" role="alert">
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

{{-- Preferencias de accesibilidad (tema, tamaño de texto y ancho del menú) --}}
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
                var current = sizes.indexOf(root.getAttribute('data-text-size') || 'lg');
                var next = Math.min(sizes.length - 1, Math.max(0, current + parseInt(btn.dataset.textSizeStep, 10)));
                root.setAttribute('data-text-size', sizes[next]);
                try { localStorage.setItem('imaj-text-size', sizes[next]); } catch (e) {}
            });
        });

        /* Arrastrar borde derecho del menú lateral */
        var resizer = document.getElementById('imaj-sidebar-resizer');
        if (resizer) {
            var dragging = false;

            resizer.addEventListener('mousedown', function (e) {
                e.preventDefault();
                dragging = true;
                document.body.classList.add('imaj-resizing-sidebar');
            });

            document.addEventListener('mousemove', function (e) {
                if (!dragging) return;
                var newWidth = Math.min(480, Math.max(290, e.clientX));
                root.style.setProperty('--lte-sidebar-width', newWidth + 'px');
            });

            document.addEventListener('mouseup', function () {
                if (!dragging) return;
                dragging = false;
                document.body.classList.remove('imaj-resizing-sidebar');
                var currentW = parseInt(getComputedStyle(root).getPropertyValue('--lte-sidebar-width'), 10);
                if (!isNaN(currentW)) {
                    try { localStorage.setItem('imaj-sidebar-width', currentW); } catch (e) {}
                }
            });
        }
    })();
</script>
@stack('scripts')
</body>
</html>