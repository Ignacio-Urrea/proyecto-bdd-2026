@extends('layouts.app')

@section('title', 'Inicio')

@push('styles')
<style>
    /* =====================================================================
       HOME IMAJ · paleta clínica (azul petróleo + verde agua + ámbar)
       Todo está dentro de .hp, así que no afecta al resto del sistema.
       ===================================================================== */
    .hp {
        --hp-navy: #0b3a4c;
        --hp-blue: #0f6e8c;
        --hp-teal: #0e7c66;
        --hp-mint: #14a38b;
        --hp-amber: #f2a93b;
        --hp-soft: #e8f4f7;
        --hp-radius: 1rem;
    }
    [data-bs-theme="dark"] .hp { --hp-soft: #15262d; }
    .hp section[id] { scroll-margin-top: 5rem; }
    html { scroll-behavior: smooth; }

    /* ---------- Títulos de sección ---------- */
    .hp-kicker { margin: 0 0 .25rem; font-size: .75rem; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; color: var(--hp-blue); }
    [data-bs-theme="dark"] .hp-kicker { color: #6cc7e0; }
    .hp-h2 { position: relative; margin: 0 0 1.4rem; padding-bottom: .7rem; font-size: 1.4rem; font-weight: 600; }
    .hp-h2::after { content: ""; position: absolute; left: 0; bottom: 0; width: 2.8rem; height: 4px; border-radius: 3px; background: linear-gradient(90deg, var(--hp-blue), var(--hp-mint)); }
    .hp-lead { max-width: 46rem; color: var(--bs-secondary-color); }

    /* ---------- Banner de bienvenida ---------- */
    .hp-hero {
        position: relative; overflow: hidden; display: grid; align-items: center;
        grid-template-columns: minmax(0, 1.5fr) minmax(0, 1fr); min-height: 16rem;
        color: #fff; border-radius: var(--hp-radius);
        background: linear-gradient(120deg, #0b3a4c 0%, #0f6e8c 58%, #0e9a85 120%);
        box-shadow: 0 .8rem 2rem rgba(11, 58, 76, .25);
    }
    .hp-hero::before {
        content: ""; position: absolute; inset: 0; pointer-events: none; opacity: .5;
        background-image:
            linear-gradient(rgba(255,255,255,.06) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255,255,255,.06) 1px, transparent 1px);
        background-size: 2.2rem 2.2rem;
        mask-image: linear-gradient(90deg, #000 20%, transparent 90%);
        -webkit-mask-image: linear-gradient(90deg, #000 20%, transparent 90%);
    }
    .hp-hero__body { position: relative; z-index: 1; padding: 2rem 2.25rem; }
    .hp-eyebrow { display: inline-flex; align-items: center; gap: .5rem; margin: 0 0 .8rem; padding: .3rem .8rem; font-size: .82rem; background: rgba(255,255,255,.14); border-radius: 2rem; }
    .hp-title { margin: 0; font-size: clamp(1.7rem, 3.2vw, 2.6rem); font-weight: 700; line-height: 1.12; }
    .hp-sub { max-width: 34rem; margin: .8rem 0 1.4rem; opacity: .9; }
    .hp-sub strong { color: #ffd596; }
    .hp-actions { display: flex; flex-wrap: wrap; gap: .75rem; }
    .hp-btn, .hp-btn-ghost {
        display: inline-flex; align-items: center; gap: .5rem; padding: .65rem 1.2rem;
        font-weight: 600; text-decoration: none; border-radius: .7rem; border: 1px solid transparent;
        transition: transform .15s ease, background .15s ease;
    }
    .hp-btn { background: #fff; color: var(--hp-navy); }
    .hp-btn:hover { color: var(--hp-navy); background: #ffe3b3; transform: translateY(-2px); }
    .hp-btn-ghost { color: #fff; border-color: rgba(255,255,255,.6); }
    .hp-btn-ghost:hover { color: #fff; background: rgba(255,255,255,.16); transform: translateY(-2px); }

    /* Escáner decorativo (anillo giratorio) */
    .hp-hero__art { position: relative; z-index: 1; display: grid; place-items: center; padding: 1.5rem; }
    .hp-scanner {
        position: relative; display: grid; place-items: center; width: min(14rem, 100%); aspect-ratio: 1;
        border-radius: 50%; border: 2px solid rgba(255,255,255,.3); font-size: 3.4rem; color: #fff;
        background: radial-gradient(circle, rgba(255,255,255,.14), rgba(255,255,255,.02) 70%);
    }
    .hp-scanner::before {
        content: ""; position: absolute; inset: -2px; border-radius: 50%;
        background: conic-gradient(from 0deg, transparent 0 70%, rgba(255,255,255,.55) 100%);
        -webkit-mask: radial-gradient(circle, transparent 62%, #000 63%);
                mask: radial-gradient(circle, transparent 62%, #000 63%);
        animation: hp-spin 5s linear infinite;
    }
    .hp-scanner::after {
        content: ""; position: absolute; inset: 1.1rem; border-radius: 50%;
        border: 2px dashed rgba(255,255,255,.3); animation: hp-spin 28s linear infinite reverse;
    }
    .hp-scanner .bi { animation: hp-pulse 2.4s ease-in-out infinite; }
    @keyframes hp-spin { to { transform: rotate(360deg); } }
    @keyframes hp-pulse { 0%, 100% { transform: scale(1); opacity: .95; } 50% { transform: scale(1.12); opacity: 1; } }

    /* ---------- Info boxes (AdminLTE) ---------- */
    .hp .info-box { margin: 0; border-radius: var(--hp-radius); box-shadow: 0 .25rem .8rem rgba(0,0,0,.08); transition: transform .18s ease, box-shadow .18s ease; }
    .hp .info-box:hover { transform: translateY(-3px); box-shadow: 0 .6rem 1.2rem rgba(0,0,0,.14); }
    .hp .info-box .info-box-icon { border-radius: var(--hp-radius) 0 0 var(--hp-radius); color: #fff; font-size: 1.8rem; }
    .hp-ib--1 { background: linear-gradient(135deg, #0b3a4c, #0f6e8c) !important; }
    .hp-ib--2 { background: linear-gradient(135deg, #0f6e8c, #14a38b) !important; }
    .hp-ib--3 { background: linear-gradient(135deg, #0e7c66, #4cc9a4) !important; }
    .hp-ib--4 { background: linear-gradient(135deg, #f2a93b, #f6c66a) !important; color: #3b2a00 !important; }
    .hp .info-box-number { font-size: 1.5rem; }

    /* ---------- Accesos rápidos ---------- */
    .hp-quick { overflow: hidden; border-radius: var(--hp-radius); box-shadow: 0 .4rem 1.2rem rgba(0,0,0,.12); }
    .hp-q { display: flex; align-items: center; gap: 1rem; min-height: 6.5rem; padding: 1.4rem 1.5rem; color: #fff; text-decoration: none; transition: filter .15s ease; }
    .hp-q:hover { color: #fff; filter: brightness(1.1); }
    .hp-q--1 { background: linear-gradient(120deg, #0b3a4c, #0d5670); }
    .hp-q--2 { background: linear-gradient(120deg, #0f6e8c, #0e8aa0); }
    .hp-q--3 { background: linear-gradient(120deg, #0e7c66, #14a38b); }
    .hp-q__icon { font-size: 2.4rem; opacity: .92; }
    .hp-q small { display: block; font-size: .72rem; letter-spacing: .06em; text-transform: uppercase; opacity: .85; }
    .hp-q strong { font-size: 1.15rem; font-weight: 600; }
    .hp-q__go { margin-left: auto; font-size: 1.25rem; transition: transform .15s ease; }
    .hp-q:hover .hp-q__go { transform: translateX(4px); }

    /* ---------- Buscador y categorías ---------- */
    .hp-side { position: sticky; top: 4.5rem; }
    .hp-cat {
        display: flex; align-items: center; gap: .65rem; width: 100%; padding: .7rem 0; text-align: left;
        font-size: .95rem; cursor: pointer; color: var(--bs-secondary-color); background: transparent;
        border: 0; border-bottom: 1px solid var(--bs-border-color); transition: color .15s ease, padding-left .15s ease;
    }
    .hp-cat:hover { color: var(--bs-body-color); padding-left: .3rem; }
    .hp-cat.is-active { color: var(--bs-body-color); font-weight: 600; padding-left: .75rem; box-shadow: inset 3px 0 0 var(--hp-blue); }
    .hp-cat__dot { width: .7rem; height: .7rem; border-radius: 50%; flex-shrink: 0; }
    .hp-cat__name { flex: 1; }
    .hp-cat__count { min-width: 1.7rem; padding: .05rem .5rem; text-align: center; font-size: .75rem; font-weight: 600; color: var(--bs-secondary-color); background: var(--bs-secondary-bg); border-radius: 2rem; }

    /* ---------- Tarjetas de módulos (color por categoría) ---------- */
    .hp-svc {
        position: relative; display: flex; flex-direction: column; height: 100%; min-height: 14rem;
        padding: 1.4rem; overflow: hidden; color: #fff; border-radius: var(--hp-radius);
        background: linear-gradient(150deg, var(--tile), color-mix(in srgb, var(--tile) 62%, #000));
        transition: transform .18s ease, box-shadow .18s ease;
    }
    .hp-svc:hover { transform: translateY(-4px); box-shadow: 0 .9rem 1.8rem rgba(0,0,0,.22); }
    .hp-svc:focus-within { outline: 3px solid var(--hp-amber); outline-offset: 3px; }
    .hp-svc__eyebrow { margin-bottom: .45rem; font-size: .7rem; font-weight: 700; letter-spacing: .09em; text-transform: uppercase; opacity: .75; }
    .hp-svc__title { margin: 0 0 .6rem; padding-right: 2.2rem; font-size: 1.3rem; font-weight: 600; }
    .hp-svc__text { margin: 0 0 1.2rem; max-width: 82%; font-size: .93rem; opacity: .9; }
    .hp-svc__btn {
        align-self: flex-start; display: inline-flex; align-items: center; gap: .5rem; margin-top: auto;
        padding: .5rem 1.1rem; font-size: .9rem; color: #fff; text-decoration: none;
        border: 1px solid rgba(255,255,255,.75); border-radius: .5rem; transition: background .15s ease;
    }
    .hp-svc__btn:hover { color: #fff; background: rgba(255,255,255,.18); }
    .hp-svc__btn .bi { transition: transform .15s ease; }
    .hp-svc:hover .hp-svc__btn .bi { transform: translateX(4px); }
    .hp-svc__icon { position: absolute; right: 1.1rem; bottom: .8rem; font-size: 4.4rem; line-height: 1; opacity: .2; pointer-events: none; transition: opacity .18s ease, transform .18s ease; }
    .hp-svc:hover .hp-svc__icon { opacity: .36; transform: scale(1.07) rotate(-5deg); }
    .hp-pin {
        position: absolute; top: .8rem; right: .8rem; z-index: 2; display: grid; place-items: center;
        width: 2.2rem; height: 2.2rem; color: #fff; cursor: pointer; background: rgba(255,255,255,.16);
        border: 0; border-radius: 50%; transition: background .15s ease, transform .15s ease;
    }
    .hp-pin:hover { background: rgba(255,255,255,.3); transform: scale(1.08); }
    .hp-pin.is-on { color: #3b2a00; background: var(--hp-amber); }

    /* ---------- Bloques de radiología (cards AdminLTE) ---------- */
    .hp-card { margin-bottom: 1.5rem; overflow: hidden; border: 0; border-top: 4px solid var(--accent, var(--hp-blue)); border-radius: var(--hp-radius); box-shadow: 0 .4rem 1.4rem rgba(0,0,0,.09); }
    .hp-card .card-header { display: flex; align-items: center; gap: .75rem; padding: 1.1rem 1.5rem .4rem; background: transparent; border: 0; }
    .hp-card .card-title { float: none; margin: 0; font-size: 1.25rem; font-weight: 600; }
    .hp-card .card-tools { display: flex; gap: .25rem; margin-left: auto; float: none; }
    .hp-card .card-body { padding: 1rem 1.5rem 1.5rem; }
    .hp-badge-ico { display: inline-grid; place-items: center; width: 2.4rem; height: 2.4rem; color: #fff; font-size: 1.2rem; border-radius: .7rem; background: var(--accent, var(--hp-blue)); }

    .hp-photo { position: relative; overflow: hidden; aspect-ratio: 4 / 3; border-radius: var(--hp-radius); background: linear-gradient(135deg, #0b3a4c, #0f6e8c); }
    .hp-photo img { width: 100%; height: 100%; object-fit: cover; transition: transform .7s ease; }
    .hp-card:hover .hp-photo img { transform: scale(1.05); }
    .hp-photo::after { content: ""; position: absolute; inset: 0; pointer-events: none; background: linear-gradient(180deg, transparent 55%, rgba(5, 25, 35, .55)); }
    .hp-photo__tag { position: absolute; left: .9rem; bottom: .9rem; z-index: 1; display: inline-flex; align-items: center; gap: .45rem; padding: .35rem .8rem; font-size: .8rem; font-weight: 600; color: #fff; background: rgba(255,255,255,.18); border: 1px solid rgba(255,255,255,.3); border-radius: 2rem; backdrop-filter: blur(6px); }
    .hp-photo--empty { display: grid; place-content: center; gap: .4rem; padding: 1rem; text-align: center; color: rgba(255,255,255,.85); font-size: .85rem; }
    .hp-photo--empty::after { display: none; }
    .hp-photo--empty .bi { font-size: 2.4rem; }
    .hp-photo--empty code { color: #ffd596; }

    /* Pestañas / pasos */
    .hp-pills { gap: .5rem; padding-bottom: .25rem; }
    .hp-pills .nav-link { display: inline-flex; align-items: center; gap: .45rem; white-space: nowrap; font-size: .9rem; font-weight: 500; color: var(--bs-body-color); background: var(--bs-secondary-bg); border-radius: 2rem; }
    .hp-pills .nav-link:hover { background: var(--hp-soft); }
    .hp-pills .nav-link.active { color: #fff; background: var(--accent, var(--hp-blue)); }
    .hp-step-n { display: inline-grid; place-items: center; width: 1.4rem; height: 1.4rem; font-size: .75rem; font-weight: 700; color: #fff; background: rgba(0,0,0,.22); border-radius: 50%; }
    .hp-pane { display: flex; gap: 1rem; align-items: flex-start; padding: 1.1rem 1.2rem; background: var(--hp-soft); border-radius: .9rem; }
    .hp-pane__ico { display: grid; place-items: center; flex-shrink: 0; width: 3rem; height: 3rem; font-size: 1.5rem; color: #fff; background: var(--accent, var(--hp-blue)); border-radius: .85rem; }
    .hp-pane h4 { margin: 0 0 .3rem; font-size: 1.05rem; font-weight: 600; }
    .hp-pane p { margin: 0; color: var(--bs-secondary-color); }
    .hp .progress { height: .55rem; border-radius: 1rem; }
    .hp .progress-bar { background: linear-gradient(90deg, var(--hp-blue), var(--hp-mint)); transition: width .4s ease; }
    .hp-btn-solid { color: #fff; background: var(--accent, var(--hp-blue)); border: 0; border-radius: .6rem; }
    .hp-btn-solid:hover { color: #fff; filter: brightness(1.12); }

    /* Valores ("qué buscamos") */
    .hp-val { height: 100%; padding: 1.1rem; background: var(--hp-soft); border-radius: .9rem; border: 1px solid transparent; transition: transform .18s ease, border-color .18s ease, box-shadow .18s ease; }
    .hp-val:hover { transform: translateY(-3px); border-color: var(--accent); box-shadow: 0 .6rem 1.2rem rgba(0,0,0,.1); }
    .hp-val__ico { display: inline-grid; place-items: center; width: 2.6rem; height: 2.6rem; margin-bottom: .6rem; font-size: 1.3rem; color: #fff; background: var(--accent); border-radius: .75rem; }
    .hp-val h4 { margin: 0 0 .3rem; font-size: 1rem; font-weight: 600; }
    .hp-val p { margin: 0; font-size: .88rem; color: var(--bs-secondary-color); }
    .hp-note { display: flex; gap: .7rem; align-items: flex-start; margin-top: 1rem; padding: .9rem 1.1rem; font-size: .9rem; background: color-mix(in srgb, var(--hp-amber) 18%, transparent); border-left: 4px solid var(--hp-amber); border-radius: .6rem; }
    .hp-note .bi { margin-top: .15rem; color: #b8741a; }

    /* ---------- Animación de aparición ---------- */
    .hp.hp-js .hp-reveal { opacity: 0; transform: translateY(18px); transition: opacity .6s ease, transform .6s ease; }
    .hp.hp-js .hp-reveal.is-visible { opacity: 1; transform: none; }

    /* ---------- Responsive ---------- */
    @media (max-width: 991.98px) {
        .hp-side { position: static; }
        .hp-cats { display: flex; flex-wrap: wrap; gap: .5rem; }
        .hp-cat { width: auto; padding: .4rem .9rem; border: 1px solid var(--bs-border-color); border-radius: 2rem; }
        .hp-cat:hover, .hp-cat.is-active { padding-left: .9rem; box-shadow: none; }
        .hp-cat.is-active { color: var(--bs-body-bg); background: var(--bs-body-color); }
        .hp-cat.is-active .hp-cat__count { color: var(--bs-body-color); background: var(--bs-body-bg); }
    }
    @media (max-width: 767.98px) {
        .hp-hero { grid-template-columns: 1fr; }
        .hp-hero__art { display: none; }
        .hp-hero__body { padding: 1.5rem; }
        .hp-pane { flex-direction: column; }
    }
    @media (prefers-reduced-motion: reduce) {
        html { scroll-behavior: auto; }
        .hp *, .hp *::before, .hp *::after { animation: none !important; transition: none !important; }
        .hp.hp-js .hp-reveal { opacity: 1; transform: none; }
    }
</style>
@endpush

@section('content')
    @php
        $user     = Auth::user();
        $roleName = $user->rol?->NOMBRE_ROL;
        $roleKey  = strtoupper((string) $roleName);
        $plain    = fn ($v) => \Illuminate\Support\Str::lower(\Illuminate\Support\Str::ascii((string) $v));

        // ---------- Módulos (mismo origen que el menú lateral) ----------
        // palabra clave => [icono, categoría, descripción]. Lo más específico va primero.
        // Cualquier ítem de config/imaj.php puede sobrescribir 'icon', 'group' y 'description'.
        $meta = [
            'gestor'      => ['bi-kanban',               'Gestores',        'Panel de seguimiento y gestión del flujo de trabajo.'],
            'reporte'     => ['bi-bar-chart-line',       'Reportes',        'Consulta indicadores y revisa la información consolidada.'],
            'detalle'     => ['bi-list-check',           'Gestión clínica', 'Revisa el detalle de las prestaciones de cada orden.'],
            'presupuesto' => ['bi-calculator',           'Finanzas',        'Crea y consulta los presupuestos de atención.'],
            'prestac'     => ['bi-clipboard2-pulse',     'Gestión clínica', 'Catálogo de prestaciones disponibles.'],
            'paciente'    => ['bi-people',               'Gestión clínica', 'Registro y datos de los pacientes.'],
            'orden'       => ['bi-file-earmark-medical', 'Gestión clínica', 'Órdenes de exámenes y su estado.'],
            'examen'      => ['bi-clipboard2-check',     'Gestión clínica', 'Exámenes realizados y por realizar.'],
            'informe'     => ['bi-file-earmark-text',    'Gestión clínica', 'Informes radiológicos de cada examen.'],
            'imagen'      => ['bi-image',                'Gestión clínica', 'Imágenes asociadas a los exámenes.'],
            'pago'        => ['bi-credit-card',          'Finanzas',        'Registro de pagos y su estado.'],
            'clinica'     => ['bi-hospital',             'Administración',  'Datos de las clínicas.'],
            'sucursal'    => ['bi-shop',                 'Administración',  'Sucursales y su información.'],
            'ciudad'      => ['bi-buildings',            'Administración',  'Ciudades disponibles en el sistema.'],
            'region'      => ['bi-geo-alt',              'Administración',  'Regiones del país.'],
            'direccion'   => ['bi-signpost-2',           'Administración',  'Direcciones registradas.'],
            'usuario'     => ['bi-person-gear',          'Administración',  'Cuentas de acceso al sistema.'],
            'rol'         => ['bi-shield-lock',          'Administración',  'Perfiles y permisos de acceso.'],
        ];
        $groupOrder  = ['Gestión clínica', 'Finanzas', 'Gestores', 'Reportes', 'Administración'];
        $groupColors = [
            'Gestión clínica' => '#0f6e8c',
            'Finanzas'        => '#0e7c66',
            'Gestores'        => '#3f51b5',
            'Reportes'        => '#6d4aa8',
            'Administración'  => '#0b3a4c',
        ];
        $colorOf = fn ($g) => $groupColors[$g] ?? '#475569';

        $modules = collect(config('imaj.navigation', []))
            ->filter(fn ($item) => \Illuminate\Support\Facades\Route::has($item['route'])
                && in_array($roleKey, config('imaj.permissions.' . $item['permission'], []), true))
            ->map(function ($item) use ($meta, $plain) {
                $needle = $plain(($item['label'] ?? '') . ' ' . ($item['route'] ?? ''));
                $key    = null;
                $found  = ['bi-circle', 'Otros', 'Accede a este módulo.'];
                foreach ($meta as $word => $data) {
                    if (str_contains($needle, $word)) { $key = $word; $found = $data; break; }
                }
                return [
                    'key'         => $key,
                    'label'       => $item['label'],
                    'url'         => route($item['route']),
                    'icon'        => ! empty($item['icon']) ? trim(preg_replace('/^bi\s+/', '', $item['icon'])) : $found[0],
                    'group'       => $item['group'] ?? $found[1],
                    'description' => $item['description'] ?? $found[2],
                ];
            })
            ->sortBy(function ($m) use ($groupOrder) {
                $pos = array_search($m['group'], $groupOrder, true);
                return $pos === false ? 99 : $pos;
            })
            ->values();

        $groups = $modules->groupBy('group')->map(fn ($g) => $g->count());

        // Accesos rápidos: órdenes, pacientes e informes si existen; si no, los primeros módulos.
        $quick = collect(['orden', 'paciente', 'informe'])
            ->map(fn ($k) => $modules->firstWhere('key', $k))
            ->filter()
            ->values();
        if ($quick->count() < 3) {
            $quick = $quick->concat(
                $modules->reject(fn ($m) => $quick->contains('url', $m['url']))->take(3 - $quick->count())
            )->values();
        }

        $hour      = now()->hour;
        $greeting  = $hour < 12 ? 'Buenos días' : ($hour < 20 ? 'Buenas tardes' : 'Buenas noches');
        $today     = \Illuminate\Support\Str::ucfirst(now()->locale('es')->translatedFormat('l j \d\e F'));
        $firstName = \Illuminate\Support\Str::of((string) $user->NOMBRE_USUARIO)->before(' ')->title();

        // ---------- Fotos (guárdalas en public/img/radiologia/) ----------
        $photo = function (string $base) {
            foreach (['jpg', 'jpeg', 'png', 'webp'] as $ext) {
                if (file_exists(public_path("img/radiologia/{$base}.{$ext}"))) {
                    return asset("img/radiologia/{$base}.{$ext}");
                }
            }
            return null;
        };

        // ---------- Contenido educativo ----------
        $modalities = [
            ['Radiografía',         'bi-person-standing',   'Usa rayos X para ver huesos, tórax y otras estructuras. Es rápida y muy utilizada en urgencias y controles.'],
            ['Tomografía (TAC)',    'bi-circle-square',     'Combina rayos X tomados desde distintos ángulos y los procesa en un computador para generar cortes detallados del cuerpo.'],
            ['Resonancia',          'bi-magnet',            'Usa un campo magnético y ondas de radio, sin radiación ionizante, para obtener imágenes muy detalladas de tejidos blandos como cerebro, músculos y articulaciones.'],
            ['Ecografía',           'bi-soundwave',         'Usa ultrasonido (ondas de sonido), sin radiación ionizante. Permite observar órganos y tejidos blandos en tiempo real.'],
            ['Mamografía',          'bi-heart-pulse',       'Radiografía de la mama con dosis baja, usada para la detección precoz y el estudio de alteraciones.'],
        ];
        $steps = [
            ['Orden médica',   'bi-file-earmark-medical', 'El médico tratante solicita el examen. En el sistema se registra la orden con el paciente y las prestaciones indicadas.'],
            ['Preparación',    'bi-clipboard2-heart',     'Se confirma la identidad del paciente, se revisan las indicaciones y antecedentes, y se le explica el procedimiento. Algunos exámenes requieren ayuno o medio de contraste.'],
            ['Imágenes',       'bi-camera',               'El equipo (rayos X, tomógrafo, resonador o ecógrafo) adquiere las imágenes mientras el paciente se mantiene en la posición indicada.'],
            ['Informe',        'bi-file-earmark-check',   'Un médico radiólogo analiza las imágenes y emite un informe, que se entrega al paciente y a su médico tratante.'],
        ];
        $values = [
            ['bi-bullseye',      'Diagnóstico oportuno',        'Imágenes de calidad, entregadas a tiempo, para que el médico decida con información confiable.', '#0f6e8c'],
            ['bi-shield-check',  'Seguridad del paciente',      'Aplicamos el principio ALARA: la dosis de radiación más baja que sea razonablemente posible para obtener una imagen útil.', '#0e7c66'],
            ['bi-heart',         'Trato humano',                'Explicamos cada paso y acompañamos al paciente, especialmente a niños y personas mayores.', '#c2410c'],
            ['bi-journal-check', 'Informes claros y trazables', 'Cada orden, examen, imagen e informe queda registrado y es fácil de encontrar.', '#3f51b5'],
        ];
    @endphp

    <div class="hp" id="hp">

        {{-- ================= BANNER ================= --}}
        <section class="hp-hero hp-reveal" aria-labelledby="hp-title">
            <div class="hp-hero__body">
                <p class="hp-eyebrow">
                    <i class="bi bi-calendar3" aria-hidden="true"></i>
                    <span id="hp-date">{{ $today }}</span> · <span id="hp-clock">{{ now()->format('H:i') }}</span>
                </p>
                <h1 class="hp-title" id="hp-title"><span id="hp-greet">{{ $greeting }}</span>, {{ $firstName }}</h1>
                <p class="hp-sub">
                    Perfil <strong>{{ $roleName }}</strong>. Gestiona órdenes, exámenes, informes y presupuestos de la
                    clínica radiológica desde un solo lugar.
                </p>
                <div class="hp-actions">
                    @if($quick->isNotEmpty())
                        <a class="hp-btn" href="{{ $quick->first()['url'] }}">
                            Ir a {{ $quick->first()['label'] }} <i class="bi bi-arrow-right" aria-hidden="true"></i>
                        </a>
                    @endif
                    <a class="hp-btn-ghost" href="#radiologia">
                        <i class="bi bi-activity" aria-hidden="true"></i> Conoce la radiología
                    </a>
                </div>
            </div>
            <div class="hp-hero__art" aria-hidden="true">
                <div class="hp-scanner"><i class="bi bi-activity"></i></div>
            </div>
        </section>

        {{-- ================= INFO BOXES (AdminLTE) ================= --}}
        <div class="row g-3 mt-1">
            <div class="col-6 col-xl-3 hp-reveal">
                <div class="info-box">
                    <span class="info-box-icon hp-ib--1" aria-hidden="true"><i class="bi bi-grid-1x2"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Módulos disponibles</span>
                        <span class="info-box-number" data-countup="{{ $modules->count() }}">{{ $modules->count() }}</span>
                    </div>
                </div>
            </div>
            <div class="col-6 col-xl-3 hp-reveal">
                <div class="info-box">
                    <span class="info-box-icon hp-ib--2" aria-hidden="true"><i class="bi bi-collection"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Categorías</span>
                        <span class="info-box-number" data-countup="{{ $groups->count() }}">{{ $groups->count() }}</span>
                    </div>
                </div>
            </div>
            <div class="col-6 col-xl-3 hp-reveal">
                <div class="info-box">
                    <span class="info-box-icon hp-ib--3" aria-hidden="true"><i class="bi bi-person-badge"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Tu perfil</span>
                        <span class="info-box-number fs-6">{{ $roleName }}</span>
                    </div>
                </div>
            </div>
            <div class="col-6 col-xl-3 hp-reveal">
                <div class="info-box">
                    <span class="info-box-icon hp-ib--4" aria-hidden="true"><i class="bi bi-clock"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Hora actual</span>
                        <span class="info-box-number" id="hp-clock-box">{{ now()->format('H:i') }}</span>
                    </div>
                </div>
            </div>
        </div>

        @if($modules->isEmpty())
            <div class="alert alert-info d-flex align-items-start gap-2 mt-3" role="status">
                <i class="bi bi-info-circle-fill mt-1" aria-hidden="true"></i>
                <div>Tu perfil aún no tiene módulos habilitados. Comunícate con el administrador del sistema.</div>
            </div>
        @else

            {{-- ================= ACCESOS RÁPIDOS ================= --}}
            @if($quick->isNotEmpty())
                <nav class="row g-0 hp-quick mt-4 hp-reveal" aria-label="Accesos rápidos">
                    @foreach($quick as $q)
                        <div class="col-12 col-md">
                            <a class="hp-q hp-q--{{ $loop->iteration }}" href="{{ $q['url'] }}">
                                <i class="bi {{ $q['icon'] }} hp-q__icon" aria-hidden="true"></i>
                                <span>
                                    <small>Acceso rápido</small>
                                    <strong>{{ $q['label'] }}</strong>
                                </span>
                                <i class="bi bi-arrow-right hp-q__go" aria-hidden="true"></i>
                            </a>
                        </div>
                    @endforeach
                </nav>
            @endif

            {{-- ================= MÓDULOS ================= --}}
            <div class="row g-4 mt-3" id="modulos">
                <aside class="col-lg-3" aria-label="Filtros de módulos">
                    <div class="hp-side">
                        <p class="hp-kicker">Explorar</p>
                        <h2 class="hp-h2">Buscar módulo</h2>
                        <div class="input-group mb-4">
                            <span class="input-group-text" aria-hidden="true"><i class="bi bi-search"></i></span>
                            <input id="hp-search" class="form-control" type="search" autocomplete="off"
                                   placeholder="Ej: pacientes, pagos…" aria-label="Buscar módulo por nombre">
                        </div>

                        <h2 class="hp-h2">Categorías</h2>
                        <div class="hp-cats">
                            <button type="button" class="hp-cat is-active" data-filter="*" aria-pressed="true">
                                <span class="hp-cat__dot" style="background:#64748b"></span>
                                <span class="hp-cat__name">Todos</span>
                                <span class="hp-cat__count">{{ $modules->count() }}</span>
                            </button>
                            <button type="button" class="hp-cat" data-filter="__fav" aria-pressed="false">
                                <span class="hp-cat__dot" style="background:#f2a93b"></span>
                                <span class="hp-cat__name">Favoritos</span>
                                <span class="hp-cat__count" id="hp-fav-count">0</span>
                            </button>
                            @foreach($groups as $groupName => $count)
                                <button type="button" class="hp-cat" data-filter="{{ $groupName }}" aria-pressed="false">
                                    <span class="hp-cat__dot" style="background:{{ $colorOf($groupName) }}"></span>
                                    <span class="hp-cat__name">{{ $groupName }}</span>
                                    <span class="hp-cat__count">{{ $count }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                </aside>

                <section class="col-lg-9" aria-labelledby="hp-mod-title">
                    <div class="d-flex justify-content-between align-items-end flex-wrap gap-2">
                        <div>
                            <p class="hp-kicker">Tu espacio de trabajo</p>
                            <h2 class="hp-h2" id="hp-mod-title">Módulos disponibles</h2>
                        </div>
                        <span class="text-body-secondary small mb-3" id="hp-count" aria-live="polite"></span>
                    </div>

                    <div class="row row-cols-1 row-cols-md-2 row-cols-xxl-3 g-3" id="hp-grid">
                        @foreach($modules as $m)
                            <div class="col hp-col"
                                 data-group="{{ $m['group'] }}"
                                 data-fav-key="{{ $m['url'] }}"
                                 data-name="{{ $plain($m['label'] . ' ' . $m['description'] . ' ' . $m['group']) }}">
                                <article class="hp-svc" style="--tile: {{ $colorOf($m['group']) }}">
                                    <span class="hp-svc__eyebrow">{{ $m['group'] }}</span>
                                    <h3 class="hp-svc__title">{{ $m['label'] }}</h3>
                                    <p class="hp-svc__text">{{ $m['description'] }}</p>
                                    <a class="hp-svc__btn stretched-link" href="{{ $m['url'] }}">
                                        Abrir <i class="bi bi-arrow-right" aria-hidden="true"></i>
                                        <span class="visually-hidden">{{ $m['label'] }}</span>
                                    </a>
                                    <button type="button" class="hp-pin" aria-pressed="false"
                                            aria-label="Marcar {{ $m['label'] }} como favorito" title="Marcar como favorito">
                                        <i class="bi bi-star" aria-hidden="true"></i>
                                    </button>
                                    <i class="bi {{ $m['icon'] }} hp-svc__icon" aria-hidden="true"></i>
                                </article>
                            </div>
                        @endforeach
                    </div>

                    <div class="text-center text-body-secondary py-5" id="hp-empty" hidden>
                        <i class="bi bi-search fs-1 d-block mb-2" aria-hidden="true"></i>
                        <span id="hp-empty-text">No encontramos módulos con ese criterio.</span>
                    </div>
                </section>
            </div>
        @endif

        {{-- ================= CONOCE LA RADIOLOGÍA ================= --}}
        <section class="mt-5" id="radiologia" aria-labelledby="hp-rad-title">
            <div class="hp-reveal">
                <p class="hp-kicker">Conoce la radiología</p>
                <h2 class="hp-h2" id="hp-rad-title">Qué hacemos y cómo lo hacemos</h2>
                <p class="hp-lead mb-4">
                    Tres ideas para entender el trabajo de una clínica radiológica: en qué consiste, cómo funciona
                    un examen de principio a fin y qué buscamos con cada atención.
                </p>
            </div>

            {{-- ---- Bloque 1: ¿En qué consiste? ---- --}}
            <div class="card hp-card hp-reveal" style="--accent:#0f6e8c">
                <div class="card-header">
                    <span class="hp-badge-ico" aria-hidden="true"><i class="bi bi-search-heart"></i></span>
                    <h3 class="card-title">¿En qué consiste la radiología?</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-lte-toggle="card-collapse" aria-label="Contraer o expandir bloque">
                            <i data-lte-icon="expand" class="bi bi-plus-lg"></i>
                            <i data-lte-icon="collapse" class="bi bi-dash-lg"></i>
                        </button>
                        <button type="button" class="btn btn-tool" data-lte-toggle="card-maximize" aria-label="Maximizar bloque">
                            <i data-lte-icon="maximize" class="bi bi-fullscreen"></i>
                            <i data-lte-icon="minimize" class="bi bi-fullscreen-exit"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-4 align-items-center">
                        <div class="col-lg-5">
                            @if($src = $photo('radiografia'))
                                <figure class="hp-photo mb-0">
                                    <img src="{{ $src }}" loading="lazy"
                                         alt="Médica sosteniendo una radiografía de tórax frente a una ventana">
                                    <figcaption class="hp-photo__tag"><i class="bi bi-person-standing" aria-hidden="true"></i> Rayos X</figcaption>
                                </figure>
                            @else
                                <div class="hp-photo hp-photo--empty" role="img" aria-label="Imagen pendiente">
                                    <i class="bi bi-image" aria-hidden="true"></i>
                                    <span>Guarda la foto como<br><code>public/img/radiologia/radiografia.jpg</code></span>
                                </div>
                            @endif
                        </div>
                        <div class="col-lg-7">
                            <p>
                                La radiología es la especialidad médica que usa imágenes del interior del cuerpo para
                                <strong>detectar, diagnosticar y hacer seguimiento</strong> de enfermedades y lesiones, sin
                                necesidad de cirugía. Los profesionales de imagenología realizan el examen y un médico
                                radiólogo interpreta las imágenes.
                            </p>
                            <p class="small text-body-secondary mb-2">Conoce las principales modalidades:</p>

                            <ul class="nav nav-pills hp-pills flex-nowrap overflow-auto mb-3" role="tablist" aria-label="Modalidades de imagen">
                                @foreach($modalities as $i => $mo)
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link{{ $i === 0 ? ' active' : '' }}" type="button" role="tab"
                                                id="mod-tab-{{ $i }}" data-bs-toggle="pill" data-bs-target="#mod-pane-{{ $i }}"
                                                aria-controls="mod-pane-{{ $i }}" aria-selected="{{ $i === 0 ? 'true' : 'false' }}">
                                            <i class="bi {{ $mo[1] }}" aria-hidden="true"></i> {{ $mo[0] }}
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                            <div class="tab-content">
                                @foreach($modalities as $i => $mo)
                                    <div class="tab-pane fade{{ $i === 0 ? ' show active' : '' }}" id="mod-pane-{{ $i }}"
                                         role="tabpanel" aria-labelledby="mod-tab-{{ $i }}" tabindex="0">
                                        <div class="hp-pane">
                                            <span class="hp-pane__ico" aria-hidden="true"><i class="bi {{ $mo[1] }}"></i></span>
                                            <div>
                                                <h4>{{ $mo[0] }}</h4>
                                                <p>{{ $mo[2] }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <p class="small text-body-secondary mt-3 mb-0">
                                <i class="bi bi-info-circle me-1" aria-hidden="true"></i>
                                El examen más adecuado en cada caso lo define el médico tratante.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ---- Bloque 2: ¿Cómo funciona? ---- --}}
            <div class="card hp-card hp-reveal" style="--accent:#0e7c66">
                <div class="card-header">
                    <span class="hp-badge-ico" aria-hidden="true"><i class="bi bi-diagram-3"></i></span>
                    <h3 class="card-title">¿Cómo funciona un examen?</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-lte-toggle="card-collapse" aria-label="Contraer o expandir bloque">
                            <i data-lte-icon="expand" class="bi bi-plus-lg"></i>
                            <i data-lte-icon="collapse" class="bi bi-dash-lg"></i>
                        </button>
                        <button type="button" class="btn btn-tool" data-lte-toggle="card-maximize" aria-label="Maximizar bloque">
                            <i data-lte-icon="maximize" class="bi bi-fullscreen"></i>
                            <i data-lte-icon="minimize" class="bi bi-fullscreen-exit"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-4 align-items-center flex-lg-row-reverse">
                        <div class="col-lg-5">
                            @if($src = $photo('diagnostico'))
                                <figure class="hp-photo mb-0">
                                    <img src="{{ $src }}" loading="lazy"
                                         alt="Dos profesionales revisando imágenes de resonancia cerebral en monitores">
                                    <figcaption class="hp-photo__tag"><i class="bi bi-display" aria-hidden="true"></i> Diagnóstico por imagen</figcaption>
                                </figure>
                            @else
                                <div class="hp-photo hp-photo--empty" role="img" aria-label="Imagen pendiente">
                                    <i class="bi bi-image" aria-hidden="true"></i>
                                    <span>Guarda la foto como<br><code>public/img/radiologia/diagnostico.jpg</code></span>
                                </div>
                            @endif
                        </div>
                        <div class="col-lg-7">
                            <p>
                                Desde que se pide el examen hasta que llega el informe hay cuatro etapas. Avanza paso a
                                paso para verlas; son las mismas que registras en el sistema.
                            </p>

                            <div id="hp-stepper" data-total="{{ count($steps) }}">
                                <div class="progress mb-3" role="progressbar" aria-label="Avance del proceso"
                                     aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ round(100 / count($steps)) }}">
                                    <div class="progress-bar" id="hp-step-bar" style="width: {{ 100 / count($steps) }}%"></div>
                                </div>

                                <ul class="nav nav-pills hp-pills flex-nowrap overflow-auto mb-3" role="tablist" aria-label="Etapas del examen">
                                    @foreach($steps as $i => $st)
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link{{ $i === 0 ? ' active' : '' }}" type="button" role="tab"
                                                    id="step-tab-{{ $i }}" data-bs-toggle="pill" data-bs-target="#step-pane-{{ $i }}"
                                                    aria-controls="step-pane-{{ $i }}" aria-selected="{{ $i === 0 ? 'true' : 'false' }}">
                                                <span class="hp-step-n">{{ $i + 1 }}</span> {{ $st[0] }}
                                            </button>
                                        </li>
                                    @endforeach
                                </ul>
                                <div class="tab-content">
                                    @foreach($steps as $i => $st)
                                        <div class="tab-pane fade{{ $i === 0 ? ' show active' : '' }}" id="step-pane-{{ $i }}"
                                             role="tabpanel" aria-labelledby="step-tab-{{ $i }}" tabindex="0">
                                            <div class="hp-pane">
                                                <span class="hp-pane__ico" aria-hidden="true"><i class="bi {{ $st[1] }}"></i></span>
                                                <div>
                                                    <h4>{{ $i + 1 }}. {{ $st[0] }}</h4>
                                                    <p>{{ $st[2] }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="d-flex justify-content-between mt-3">
                                    <button type="button" class="btn btn-outline-secondary btn-sm" id="hp-step-prev">
                                        <i class="bi bi-arrow-left" aria-hidden="true"></i> Anterior
                                    </button>
                                    <button type="button" class="btn btn-sm hp-btn-solid" id="hp-step-next">
                                        Siguiente <i class="bi bi-arrow-right" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ---- Bloque 3: ¿Qué buscamos? ---- --}}
            <div class="card hp-card hp-reveal" style="--accent:#c2410c">
                <div class="card-header">
                    <span class="hp-badge-ico" aria-hidden="true"><i class="bi bi-bullseye"></i></span>
                    <h3 class="card-title">¿Qué buscamos?</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-lte-toggle="card-collapse" aria-label="Contraer o expandir bloque">
                            <i data-lte-icon="expand" class="bi bi-plus-lg"></i>
                            <i data-lte-icon="collapse" class="bi bi-dash-lg"></i>
                        </button>
                        <button type="button" class="btn btn-tool" data-lte-toggle="card-maximize" aria-label="Maximizar bloque">
                            <i data-lte-icon="maximize" class="bi bi-fullscreen"></i>
                            <i data-lte-icon="minimize" class="bi bi-fullscreen-exit"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-4 align-items-center">
                        <div class="col-lg-5">
                            @if($src = $photo('paciente'))
                                <figure class="hp-photo mb-0">
                                    <img src="{{ $src }}" loading="lazy"
                                         alt="Profesional de salud sonriendo a un niño recostado en un equipo de tomografía">
                                    <figcaption class="hp-photo__tag"><i class="bi bi-heart-fill" aria-hidden="true"></i> Atención humana</figcaption>
                                </figure>
                            @else
                                <div class="hp-photo hp-photo--empty" role="img" aria-label="Imagen pendiente">
                                    <i class="bi bi-image" aria-hidden="true"></i>
                                    <span>Guarda la foto como<br><code>public/img/radiologia/paciente.jpg</code></span>
                                </div>
                            @endif
                        </div>
                        <div class="col-lg-7">
                            <p>
                                Queremos que cada examen sea una experiencia <strong>segura, clara y cercana</strong>,
                                y que la información llegue completa a quien la necesita.
                            </p>
                            <div class="row g-3">
                                @foreach($values as $v)
                                    <div class="col-sm-6">
                                        <div class="hp-val" style="--accent: {{ $v[3] }}">
                                            <span class="hp-val__ico" aria-hidden="true"><i class="bi {{ $v[0] }}"></i></span>
                                            <h4>{{ $v[1] }}</h4>
                                            <p>{{ $v[2] }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="hp-note">
                                <i class="bi bi-lightbulb" aria-hidden="true"></i>
                                <span><strong>ALARA</strong> significa «tan bajo como sea razonablemente posible»: es el criterio que guía el uso de radiación en cada examen.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
<script>
    (function () {
        var root = document.getElementById('hp');
        if (!root) return;
        root.classList.add('hp-js');
        var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        /* ---------- Reloj y saludo según la hora del navegador ---------- */
        function tick() {
            var d = new Date();
            var hhmm = d.toLocaleTimeString('es-CL', { hour: '2-digit', minute: '2-digit' });
            var h = d.getHours();
            var greet = h < 12 ? 'Buenos días' : (h < 20 ? 'Buenas tardes' : 'Buenas noches');
            var date = d.toLocaleDateString('es-CL', { weekday: 'long', day: 'numeric', month: 'long' });
            var set = function (id, v) { var el = document.getElementById(id); if (el) el.textContent = v; };
            set('hp-clock', hhmm);
            set('hp-clock-box', hhmm);
            set('hp-greet', greet);
            set('hp-date', date.charAt(0).toUpperCase() + date.slice(1));
        }
        tick();
        setInterval(tick, 30000);

        /* ---------- Contadores animados ---------- */
        document.querySelectorAll('[data-countup]').forEach(function (el) {
            var target = parseInt(el.dataset.countup, 10) || 0;
            if (reduce || target === 0) return;
            var start = null;
            el.textContent = '0';
            function step(ts) {
                if (start === null) start = ts;
                var p = Math.min((ts - start) / 900, 1);
                el.textContent = Math.round(target * (1 - Math.pow(1 - p, 3)));
                if (p < 1) requestAnimationFrame(step);
            }
            requestAnimationFrame(step);
        });

        /* ---------- Aparición al hacer scroll ---------- */
        var reveals = [].slice.call(root.querySelectorAll('.hp-reveal'));
        if ('IntersectionObserver' in window && !reduce) {
            var io = new IntersectionObserver(function (entries) {
                entries.forEach(function (e) {
                    if (e.isIntersecting) { e.target.classList.add('is-visible'); io.unobserve(e.target); }
                });
            }, { threshold: 0.12 });
            reveals.forEach(function (el) { io.observe(el); });
        } else {
            reveals.forEach(function (el) { el.classList.add('is-visible'); });
        }

        /* ---------- Módulos: búsqueda, categorías y favoritos ---------- */
        var cols = [].slice.call(root.querySelectorAll('.hp-col'));
        if (cols.length) {
            var input = document.getElementById('hp-search');
            var cats  = [].slice.call(root.querySelectorAll('.hp-cat'));
            var count = document.getElementById('hp-count');
            var empty = document.getElementById('hp-empty');
            var emptyText = document.getElementById('hp-empty-text');
            var favCount = document.getElementById('hp-fav-count');
            var current = '*';
            var favs = [];
            try { favs = JSON.parse(localStorage.getItem('imaj-favs') || '[]'); } catch (e) {}

            var norm = function (s) { return s.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, ''); };

            function paintPins() {
                var n = 0;
                cols.forEach(function (col) {
                    var on = favs.indexOf(col.dataset.favKey) !== -1;
                    col.dataset.fav = on ? '1' : '0';
                    if (on) n++;
                    var pin = col.querySelector('.hp-pin');
                    pin.classList.toggle('is-on', on);
                    pin.setAttribute('aria-pressed', on ? 'true' : 'false');
                    pin.querySelector('i').className = 'bi ' + (on ? 'bi-star-fill' : 'bi-star');
                });
                favCount.textContent = n;
            }

            function apply() {
                var q = input ? norm(input.value.trim()) : '';
                var shown = 0;
                cols.forEach(function (col) {
                    var okGroup = current === '*' || (current === '__fav' ? col.dataset.fav === '1' : col.dataset.group === current);
                    var okText = !q || col.dataset.name.indexOf(q) !== -1;
                    col.hidden = !(okGroup && okText);
                    if (!col.hidden) shown++;
                });
                count.textContent = shown + (shown === 1 ? ' módulo' : ' módulos');
                empty.hidden = shown !== 0;
                emptyText.textContent = (current === '__fav' && !q)
                    ? 'Aún no tienes favoritos. Toca la estrella de un módulo para fijarlo aquí.'
                    : 'No encontramos módulos con ese criterio.';
            }

            cols.forEach(function (col) {
                col.querySelector('.hp-pin').addEventListener('click', function (ev) {
                    ev.preventDefault();
                    ev.stopPropagation();
                    var key = col.dataset.favKey;
                    var i = favs.indexOf(key);
                    if (i === -1) favs.push(key); else favs.splice(i, 1);
                    try { localStorage.setItem('imaj-favs', JSON.stringify(favs)); } catch (e) {}
                    paintPins();
                    apply();
                });
            });

            cats.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    current = btn.dataset.filter;
                    cats.forEach(function (b) {
                        var on = b === btn;
                        b.classList.toggle('is-active', on);
                        b.setAttribute('aria-pressed', on ? 'true' : 'false');
                    });
                    apply();
                });
            });

            if (input) input.addEventListener('input', apply);
            paintPins();
            apply();
        }

        /* ---------- Pasos del examen: barra de progreso y botones ---------- */
        var stepper = document.getElementById('hp-stepper');
        if (stepper) {
            var pills = [].slice.call(stepper.querySelectorAll('[data-bs-toggle="pill"]'));
            var bar = document.getElementById('hp-step-bar');
            var prev = document.getElementById('hp-step-prev');
            var next = document.getElementById('hp-step-next');
            var total = pills.length;
            var idx = 0;

            function paintStep(i) {
                idx = i;
                var pct = Math.round(((i + 1) / total) * 100);
                bar.style.width = pct + '%';
                bar.parentNode.setAttribute('aria-valuenow', pct);
                prev.disabled = i === 0;
                next.disabled = i === total - 1;
            }
            pills.forEach(function (p, i) { p.addEventListener('click', function () { paintStep(i); }); });
            prev.addEventListener('click', function () { if (idx > 0) pills[idx - 1].click(); });
            next.addEventListener('click', function () { if (idx < total - 1) pills[idx + 1].click(); });
            paintStep(0);
        }
    })();
</script>
@endpush