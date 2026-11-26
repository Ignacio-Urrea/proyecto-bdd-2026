@extends('layouts.app')

@section('title', 'Inicio')

@section('content')
    @php
        /* ============ 1) ROL ============ */
        $user     = Auth::user();
        $roleName = $user->rol?->NOMBRE_ROL;
        $roleKey  = strtoupper((string) $roleName);
        $plain    = fn ($v) => \Illuminate\Support\Str::lower(\Illuminate\Support\Str::ascii((string) $v));

        // Grupo de interfaz (el controlador puede pasar $roleGroup para forzarlo)
        $group = $roleGroup ?? match ($roleKey) {
            'RECEPCIONISTA' => 'recepcion',
            'TECNICO'       => 'tecnico',
            'RADIOLOGO'     => 'radiologo',
            'FINANZAS'      => 'finanzas',
            default         => 'admin',   // SUPERADMIN, ADMINISTRADOR, CLINICA_ADMIN, SUCURSAL_ADMIN
        };

        /* ============ 2) ENLACES (desde config/imaj.php, con permisos) ============ */
        $safeRoute = function ($name) {
            try { return route($name); } catch (\Throwable $e) { return null; }
        };
        $nav = collect(config('imaj.navigation', []))
            ->filter(fn ($i) => ! empty($i['route'])
                && \Illuminate\Support\Facades\Route::has($i['route'])
                && in_array($roleKey, config('imaj.permissions.' . ($i['permission'] ?? ''), []), true))
            ->map(fn ($i) => [
                'label'  => $i['label'] ?? $i['route'],
                'url'    => $safeRoute($i['route']),
                'needle' => $plain(($i['label'] ?? '') . ' ' . $i['route']),
            ])
            ->filter(fn ($i) => $i['url'])
            ->values();

        $find = function (array $words) use ($nav) {
            foreach ($nav as $i) {
                if (collect($words)->every(fn ($w) => str_contains($i['needle'], $w))) { return $i['url']; }
            }
            return null;
        };
        $go = fn (string $k) => str_starts_with($k, 'rep:') ? $find(['reporte', substr($k, 4)]) : $find(['gestor', $k]);

        /* ============ 3) QUÉ VE CADA ROL ============ */
        $defs = [
            'orden'       => ['Agendar orden',       'bi-calendar-plus',        'Cita del paciente en una sucursal'],
            'presupuesto' => ['Generar presupuesto', 'bi-calculator',           'Cotiza las prestaciones de una orden'],
            'pago'        => ['Registrar pago',      'bi-credit-card',          'Asocia un pago a un presupuesto'],
            'examen'      => ['Actualizar examen',   'bi-clipboard2-pulse',     'Cambia el estado y cierra la orden'],
            'informe'     => ['Publicar informe',    'bi-file-earmark-medical', 'Diagnóstico médico y adendums'],
        ];
        $byRole = [
            'recepcion' => ['orden', 'presupuesto', 'pago'],
            'tecnico'   => ['examen'],
            'radiologo' => ['informe', 'examen'],
            'finanzas'  => ['pago', 'presupuesto'],
            'admin'     => ['orden', 'presupuesto', 'pago', 'examen', 'informe'],
        ];
        $actions = collect($byRole[$group])
            ->map(fn ($k) => ['def' => $defs[$k], 'url' => $go($k)])
            ->filter(fn ($a) => $a['url'])
            ->values();

        $repDefs = [
            'orden'   => ['Órdenes por sucursal y estado',  'bi-building'],
            'ingreso' => ['Ingresos mensuales',             'bi-cash-stack'],
            'examen'  => ['Exámenes por urgencia y estado', 'bi-exclamation-triangle'],
        ];
        $repKeys = ['admin' => ['orden', 'ingreso', 'examen'], 'finanzas' => ['ingreso', 'orden']][$group] ?? [];
        $reports = collect($repKeys)
            ->map(fn ($k) => ['def' => $repDefs[$k], 'url' => $go('rep:' . $k)])
            ->filter(fn ($r) => $r['url'])
            ->values();

        [$queueTitle, $rowKey, $rowLabel] = match ($group) {
            'recepcion' => ['Agenda de hoy',           'presupuesto', 'Presupuesto'],
            'tecnico'   => ['Exámenes por tomar',      'examen',      'Actualizar'],
            'radiologo' => ['Pendientes de informe',   'informe',     'Informar'],
            'finanzas'  => ['Presupuestos por cobrar', 'pago',        'Registrar pago'],
            default     => ['Órdenes en seguimiento',  'orden',       'Ver orden'],
        };
        $rowBase = $go($rowKey);

        /* ============ 4) DATOS (el controlador puede enviar $kpis, $queue, $sucursalNombre) ============ */
        $demo = ! isset($kpis) && ! isset($queue);

        $demoKpis = match ($group) {
            'recepcion' => [
                ['label' => 'Citas de hoy',            'value' => 18, 'tone' => 'primary', 'go' => 'orden'],
                ['label' => 'Órdenes sin presupuesto', 'value' => 5,  'tone' => 'warning', 'go' => 'presupuesto'],
                ['label' => 'Presupuestos sin pago',   'value' => 7,  'tone' => 'danger',  'go' => 'pago'],
            ],
            'tecnico' => [
                ['label' => 'Por tomar',      'value' => 9,  'tone' => 'primary', 'go' => 'examen'],
                ['label' => 'Urgentes',       'value' => 2,  'tone' => 'danger',  'go' => 'examen'],
                ['label' => 'Ejecutados hoy', 'value' => 21, 'tone' => 'success', 'go' => 'examen'],
            ],
            'radiologo' => [
                ['label' => 'Pendientes de informe', 'value' => 14, 'tone' => 'primary', 'go' => 'informe'],
                ['label' => 'Urgentes',              'value' => 3,  'tone' => 'danger',  'go' => 'informe'],
                ['label' => 'Publicados hoy',        'value' => 26, 'tone' => 'success', 'go' => 'informe'],
            ],
            'finanzas' => [
                ['label' => 'Ingresos de hoy',         'value' => '$ 1.250.000',  'tone' => 'success', 'go' => 'pago'],
                ['label' => 'Ingresos del mes',        'value' => '$ 18.430.000', 'tone' => 'primary', 'go' => 'rep:ingreso'],
                ['label' => 'Presupuestos por cobrar', 'value' => 11,             'tone' => 'warning', 'go' => 'presupuesto'],
            ],
            default => [
                ['label' => 'Órdenes de hoy',         'value' => 42,             'tone' => 'primary', 'go' => 'rep:orden'],
                ['label' => 'Pendientes / ejecutadas','value' => '12 / 30',      'tone' => 'warning', 'go' => 'rep:orden'],
                ['label' => 'Ingresos del mes',       'value' => '$ 18.430.000', 'tone' => 'success', 'go' => 'rep:ingreso'],
                ['label' => 'Exámenes urgentes',      'value' => 4,              'tone' => 'danger',  'go' => 'rep:examen'],
            ],
        };
        $demoQueue = [
            ['id' => 1, 'orden' => 'ORD-24818', 'paciente' => 'Paciente de ejemplo B', 'examen' => 'TAC de abdomen',        'hora' => '10:00', 'estado' => 'EN_PROCESO', 'urgente' => true],
            ['id' => 2, 'orden' => 'ORD-24820', 'paciente' => 'Paciente de ejemplo D', 'examen' => 'Ecografía abdominal',  'hora' => '11:15', 'estado' => 'PENDIENTE',  'urgente' => true],
            ['id' => 3, 'orden' => 'ORD-24817', 'paciente' => 'Paciente de ejemplo A', 'examen' => 'Resonancia de rodilla','hora' => '09:30', 'estado' => 'AGENDADA',   'urgente' => false],
            ['id' => 4, 'orden' => 'ORD-24819', 'paciente' => 'Paciente de ejemplo C', 'examen' => 'Radiografía de tórax','hora' => '10:30', 'estado' => 'PENDIENTE',  'urgente' => false],
            ['id' => 5, 'orden' => 'ORD-24821', 'paciente' => 'Paciente de ejemplo E', 'examen' => 'Mamografía bilateral','hora' => '12:00', 'estado' => 'EJECUTADA',  'urgente' => false],
        ];

        $kpis     = collect($kpis ?? $demoKpis);
        $queue    = collect($queue ?? $demoQueue)->sortByDesc('urgente')->values();
        $urgentes = $queue->where('urgente', true);
        $sucursalNombre = $sucursalNombre ?? null;

        // Estado → variante de badge (colores suaves con buen contraste en tema oscuro)
        $badge = ['PENDIENTE' => 'warning', 'AGENDADA' => 'info', 'EN_PROCESO' => 'primary', 'EJECUTADA' => 'success', 'INFORMADA' => 'success', 'CANCELADA' => 'secondary'];
        $toneOf = fn ($t) => in_array($t, ['primary', 'success', 'warning', 'danger', 'info', 'secondary'], true) ? $t : 'secondary';

        /* ============ 5) DISTRIBUCIÓN ADAPTATIVA ============ */
        $n         = $actions->count();
        $cols      = [2 => 2, 3 => 3, 4 => 2, 5 => 3][$n] ?? 3;           // columnas de la grilla de gestores
        $hasSide   = $kpis->isNotEmpty() || $urgentes->isNotEmpty() || $reports->isNotEmpty();
        $mainCol   = $hasSide ? 'col-xl-8' : 'col-12';                     // sin columna lateral → tabla al 100 %
        $indicator = $kpis->first() ?? ['label' => 'En cola', 'value' => $queue->count(), 'tone' => 'primary'];

        $hour      = now()->hour;
        $greeting  = $hour < 12 ? 'Buenos días' : ($hour < 20 ? 'Buenas tardes' : 'Buenas noches');
        $firstName = \Illuminate\Support\Str::of((string) $user->NOMBRE_USUARIO)->before(' ')->title();
        $today     = \Illuminate\Support\Str::ucfirst(now()->locale('es')->translatedFormat('l j \d\e F'));
    @endphp

    <style>
        /* =====================================================================
           HOME BACK-OFFICE · acabado pulido (usa la paleta del tema del sistema)
           ===================================================================== */
        .bo {
            --bo-line: color-mix(in srgb, var(--bs-border-color) 70%, transparent);
            --bo-shadow: 0 1px 2px rgba(15, 23, 42, .06), 0 10px 26px -16px rgba(15, 23, 42, .22);
            --bo-shadow-hover: 0 2px 4px rgba(15, 23, 42, .08), 0 16px 32px -14px rgba(15, 23, 42, .3);
            --bo-radius: .9rem;
            --bo-hover: color-mix(in srgb, var(--bs-primary) 8%, var(--bs-body-bg));
        }
        [data-bs-theme="dark"] .bo {
            --bo-shadow: 0 1px 2px rgba(0, 0, 0, .35), 0 12px 28px -16px rgba(0, 0, 0, .6);
            --bo-shadow-hover: 0 2px 4px rgba(0, 0, 0, .4), 0 18px 34px -14px rgba(0, 0, 0, .7);
        }
        .bo-t-primary   { --t: var(--bs-primary); }
        .bo-t-success   { --t: var(--bs-success); }
        .bo-t-warning   { --t: var(--bs-warning); }
        .bo-t-danger    { --t: var(--bs-danger); }
        .bo-t-info      { --t: var(--bs-info); }
        .bo-t-secondary { --t: var(--bs-secondary); }

        .bo-label { display: flex; align-items: center; gap: .6rem; margin: 0 0 .75rem; font-size: .72rem; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; color: var(--bs-secondary-color); }
        .bo-label::after { content: ""; flex: 1; height: 1px; background: var(--bo-line); }

        /* ---------- Encabezado ---------- */
        .bo-head { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: 1.5rem; }
        .bo-head h1 { margin: 0 0 .55rem; font-size: 1.65rem; font-weight: 600; letter-spacing: -.015em; }
        .bo-chips { display: flex; flex-wrap: wrap; gap: .5rem; }
        .bo-chip { display: inline-flex; align-items: center; gap: .45rem; padding: .28rem .8rem; font-size: .82rem; line-height: 1.3; color: var(--bs-secondary-color); background: var(--bs-secondary-bg); border: 1px solid var(--bo-line); border-radius: 2rem; }
        .bo-chip .bi { font-size: .9rem; line-height: 1; }
        .bo-refresh { display: inline-flex; align-items: center; gap: .45rem; }

        /* ---------- Tarjetas base ---------- */
        .bo-card { overflow: hidden; background: var(--bs-body-bg); border: 1px solid var(--bo-line); border-radius: var(--bo-radius); box-shadow: var(--bo-shadow); }
        .bo-card__head { display: flex; align-items: center; justify-content: space-between; gap: .75rem; padding: 1rem 1.25rem; border-bottom: 1px solid var(--bo-line); }
        .bo-card__head h2 { margin: 0; font-size: 1rem; font-weight: 600; }

        /* ---------- Gestores: grilla que se adapta a la cantidad ---------- */
        .bo-actions-wrap { container-type: inline-size; margin-bottom: 1.75rem; }
        .bo-actions { display: grid; gap: .85rem; grid-template-columns: repeat(var(--cols, 3), minmax(0, 1fr)); }
        .bo-actions--5 { grid-template-columns: repeat(6, minmax(0, 1fr)); }
        .bo-actions--5 .bo-area:nth-child(-n+3) { grid-column: span 2; }
        .bo-actions--5 .bo-area:nth-child(n+4)  { grid-column: span 3; }
        .bo-actions--1 { grid-template-columns: 1fr; }

        .bo-area { position: relative; display: flex; flex-direction: column; min-width: 0; padding: 1.25rem 1.35rem; color: var(--bs-body-color); text-decoration: none; background: var(--bs-body-bg); border: 1px solid var(--bo-line); border-radius: var(--bo-radius); box-shadow: var(--bo-shadow); transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease; }
        .bo-area:hover { color: var(--bs-body-color); transform: translateY(-3px); border-color: color-mix(in srgb, var(--bs-primary) 55%, var(--bo-line)); box-shadow: var(--bo-shadow-hover); }
        .bo-area__top { display: flex; align-items: center; justify-content: space-between; margin-bottom: .9rem; }
        .bo-area__n { font-size: 1.5rem; font-weight: 600; line-height: 1; letter-spacing: -.02em; color: var(--bs-secondary-color); opacity: .5; }
        .bo-area__ico { display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; width: 2.5rem; height: 2.5rem; font-size: 1.2rem; line-height: 1; color: var(--bs-primary); background: var(--bs-primary-bg-subtle); border-radius: .7rem; transition: transform .2s ease; }
        .bo-area:hover .bo-area__ico { transform: scale(1.06); }
        .bo-area strong { display: block; font-size: 1.04rem; font-weight: 600; line-height: 1.25; }
        .bo-area small { display: block; margin: .25rem 0 1rem; line-height: 1.4; color: var(--bs-secondary-color); }
        .bo-area__go { display: inline-flex; align-items: center; gap: .4rem; margin-top: auto; font-size: .88rem; font-weight: 600; color: var(--bs-primary); }
        .bo-area__go .bi { transition: transform .2s ease; }
        .bo-area:hover .bo-area__go .bi { transform: translateX(4px); }

        /* Tarjeta principal destacada: degradado sutil */
        .bo-area--main { color: #fff; background: linear-gradient(135deg, var(--bs-primary) 0%, color-mix(in srgb, var(--bs-primary) 66%, #0b1220) 100%); border-color: transparent; box-shadow: inset 0 1px 0 rgba(255,255,255,.2), 0 14px 28px -14px color-mix(in srgb, var(--bs-primary) 65%, transparent); }
        .bo-area--main:hover { color: #fff; border-color: transparent; box-shadow: inset 0 1px 0 rgba(255,255,255,.25), 0 20px 36px -14px color-mix(in srgb, var(--bs-primary) 75%, transparent); }
        .bo-area--main .bo-area__n { color: #fff; opacity: .6; }
        .bo-area--main small { color: rgba(255,255,255,.86); }
        .bo-area--main .bo-area__go { color: #fff; }
        .bo-area--main .bo-area__ico { color: #fff; background: rgba(255,255,255,.2); }

        /* Variante ancha: un solo gestor + indicador clave */
        .bo-area--wide { flex-direction: row; align-items: center; flex-wrap: wrap; gap: 1.25rem 1.5rem; padding: 1.5rem 1.75rem; }
        .bo-area--wide .bo-area__ico { width: 3.4rem; height: 3.4rem; font-size: 1.6rem; border-radius: 1rem; }
        .bo-area--wide .bo-area__txt { flex: 1 1 14rem; min-width: 0; }
        .bo-area--wide strong { font-size: 1.25rem; }
        .bo-area--wide small { margin: .2rem 0 0; }
        .bo-ind { display: flex; flex-direction: column; align-items: flex-end; padding: 0 1.5rem; border-left: 1px solid rgba(255,255,255,.28); }
        .bo-ind span { font-size: .78rem; opacity: .85; }
        .bo-ind b { font-size: 2.1rem; font-weight: 600; line-height: 1.1; font-variant-numeric: tabular-nums; }
        .bo-btn { display: inline-flex; align-items: center; gap: .45rem; padding: .6rem 1.15rem; font-size: .92rem; font-weight: 600; color: var(--bs-primary); background: #fff; border-radius: .65rem; transition: transform .2s ease, box-shadow .2s ease; }
        .bo-area--wide:hover .bo-btn { transform: translateX(3px); box-shadow: 0 6px 14px -6px rgba(0,0,0,.4); }

        @container (max-width: 34rem) {
            .bo-actions:not(.bo-actions--1) { grid-template-columns: repeat(2, minmax(0, 1fr)) !important; }
            .bo-actions:not(.bo-actions--1) .bo-area { grid-column: auto !important; }
            .bo-actions:not(.bo-actions--1) .bo-area:last-child:nth-child(odd) { grid-column: 1 / -1 !important; }
            .bo-ind { align-items: flex-start; padding: 0; border-left: 0; }
        }
        @container (max-width: 22rem) {
            .bo-actions:not(.bo-actions--1) { grid-template-columns: 1fr !important; }
            .bo-actions:not(.bo-actions--1) .bo-area:last-child:nth-child(odd) { grid-column: auto !important; }
        }

        /* ---------- Tabla ---------- */
        .bo-scroll { overflow-x: auto; }
        .bo-table { min-width: 46rem; margin: 0; font-size: .9rem; --bs-table-bg: transparent; --bs-table-color: inherit; border-color: var(--bo-line); }
        .bo-table > :not(caption) > * > * { padding: .85rem 1.1rem; background-color: var(--bs-body-bg); box-shadow: none; border-bottom-color: var(--bo-line); vertical-align: middle; transition: background-color .15s ease; }
        .bo-table thead > tr > th { font-size: .72rem; font-weight: 700; letter-spacing: .07em; text-transform: uppercase; white-space: nowrap; color: var(--bs-secondary-color); background-color: var(--bs-tertiary-bg); }
        .bo-table tbody > tr:last-child > * { border-bottom: 0; }
        .bo-table tbody > tr:hover > * { background-color: var(--bo-hover); }
        .bo-table > tbody > tr.bo-urgent > * { background-color: color-mix(in srgb, var(--bs-danger) 7%, var(--bs-body-bg)); }
        .bo-table > tbody > tr.bo-urgent:hover > * { background-color: color-mix(in srgb, var(--bs-danger) 13%, var(--bs-body-bg)); }
        .bo-table > tbody > tr.bo-urgent > td:first-child { box-shadow: inset 3px 0 0 var(--bs-danger); }
        .bo-table .bo-code { font-weight: 600; white-space: nowrap; font-variant-numeric: tabular-nums; }
        .bo-table .bo-time { white-space: nowrap; font-variant-numeric: tabular-nums; color: var(--bs-secondary-color); }
        /* Última columna (acciones) siempre visible, aunque la tabla tenga scroll horizontal */
        .bo-table > :not(caption) > * > :last-child { position: sticky; right: 0; z-index: 2; width: 1%; white-space: nowrap; text-align: right; box-shadow: -10px 0 12px -10px rgba(0,0,0,.35); }
        .bo-link { display: inline-flex; align-items: center; gap: .4rem; padding: .38rem .85rem; font-size: .85rem; font-weight: 600; white-space: nowrap; color: var(--bs-primary); text-decoration: none; background: transparent; border: 1px solid color-mix(in srgb, var(--bs-primary) 55%, transparent); border-radius: .55rem; transition: background-color .15s ease, color .15s ease, border-color .15s ease; }
        .bo-link .bi { font-size: .8rem; transition: transform .15s ease; }
        .bo-link:hover { color: #fff; background: var(--bs-primary); border-color: var(--bs-primary); }
        .bo-link:hover .bi { transform: translateX(2px); }

        /* ---------- Badges (alto contraste, tonos suaves) ---------- */
        .bo-badge { display: inline-flex; align-items: center; gap: .35rem; padding: .28rem .7rem; font-size: .76rem; font-weight: 600; line-height: 1.2; white-space: nowrap; border: 1px solid transparent; border-radius: 2rem; }
        .bo-badge .bi { font-size: .78rem; line-height: 1; }
        .bo-badge--primary   { color: var(--bs-primary-text-emphasis);   background: var(--bs-primary-bg-subtle);   border-color: var(--bs-primary-border-subtle); }
        .bo-badge--success   { color: var(--bs-success-text-emphasis);   background: var(--bs-success-bg-subtle);   border-color: var(--bs-success-border-subtle); }
        .bo-badge--warning   { color: var(--bs-warning-text-emphasis);   background: var(--bs-warning-bg-subtle);   border-color: var(--bs-warning-border-subtle); }
        .bo-badge--info      { color: var(--bs-info-text-emphasis);      background: var(--bs-info-bg-subtle);      border-color: var(--bs-info-border-subtle); }
        .bo-badge--secondary { color: var(--bs-secondary-color);         background: var(--bs-secondary-bg);        border-color: var(--bo-line); }
        .bo-badge--urgent    { color: #fff; background: color-mix(in srgb, var(--bs-danger) 88%, #000); }
        .bo-badge--count     { padding: .2rem .65rem; color: var(--bs-secondary-color); background: var(--bs-secondary-bg); border-color: var(--bo-line); font-variant-numeric: tabular-nums; }

        /* ---------- Resumen / urgentes: valores alineados a la derecha ---------- */
        .bo-stat { display: grid; grid-template-columns: auto minmax(0, 1fr) auto; align-items: center; gap: .85rem; padding: .95rem 1.25rem; color: var(--bs-body-color); text-decoration: none; border-bottom: 1px solid var(--bo-line); transition: background-color .15s ease; }
        .bo-stat:last-child { border-bottom: 0; }
        a.bo-stat:hover { color: var(--bs-body-color); background: var(--bo-hover); }
        .bo-stat__dot { width: .6rem; height: .6rem; background: var(--t, var(--bs-secondary)); border-radius: 50%; box-shadow: 0 0 0 4px color-mix(in srgb, var(--t, var(--bs-secondary)) 20%, transparent); }
        .bo-stat__label { min-width: 0; line-height: 1.3; }
        .bo-stat__label small { display: block; color: var(--bs-secondary-color); }
        .bo-stat__v { min-width: 4.5ch; font-size: 1.45rem; font-weight: 600; line-height: 1; text-align: right; font-variant-numeric: tabular-nums; letter-spacing: -.01em; }
        .bo-stat__end { justify-self: end; }

        .bo-rep { display: grid; grid-template-columns: auto minmax(0, 1fr) auto; align-items: center; gap: .85rem; padding: .85rem 1.25rem; color: var(--bs-body-color); text-decoration: none; border-bottom: 1px solid var(--bo-line); transition: background-color .15s ease; }
        .bo-rep:last-child { border-bottom: 0; }
        .bo-rep:hover { color: var(--bs-body-color); background: var(--bo-hover); }
        .bo-rep__ico { display: inline-flex; align-items: center; justify-content: center; width: 2.1rem; height: 2.1rem; font-size: 1rem; line-height: 1; color: var(--bs-primary); background: var(--bs-primary-bg-subtle); border-radius: .6rem; }
        .bo-rep .bi-chevron-right { font-size: .8rem; color: var(--bs-secondary-color); transition: transform .15s ease; }
        .bo-rep:hover .bi-chevron-right { transform: translateX(3px); }

        .bo a:focus-visible, .bo button:focus-visible { outline: 3px solid color-mix(in srgb, var(--bs-primary) 70%, transparent); outline-offset: 2px; }
        @media (prefers-reduced-motion: reduce) { .bo *, .bo *::before, .bo *::after { transition: none !important; } }
    </style>

    <div class="bo">

        @if($demo)
            <div class="alert alert-warning py-2 small mb-3" role="note">
                <i class="bi bi-info-circle me-1" aria-hidden="true"></i>
                Mostrando <strong>datos de ejemplo</strong>. Envía <code>$kpis</code> y <code>$queue</code> desde el controlador para ver datos reales.
            </div>
        @endif

        {{-- ============ ENCABEZADO ============ --}}
        <div class="bo-head">
            <div>
                <h1>{{ $greeting }}, {{ $firstName }}</h1>
                <div class="bo-chips">
                    <span class="bo-chip"><i class="bi bi-person-badge" aria-hidden="true"></i> {{ $roleName }}</span>
                    @if($sucursalNombre)<span class="bo-chip"><i class="bi bi-building" aria-hidden="true"></i> {{ $sucursalNombre }}</span>@endif
                    <span class="bo-chip"><i class="bi bi-calendar3" aria-hidden="true"></i> {{ $today }}</span>
                </div>
            </div>
            <button type="button" class="btn btn-outline-secondary btn-sm bo-refresh" onclick="location.reload()">
                <i class="bi bi-arrow-clockwise" aria-hidden="true"></i> Actualizar
            </button>
        </div>

        <div class="row g-4">

            {{-- ============ COLUMNA PRINCIPAL ============ --}}
            <div class="{{ $mainCol }}">

                {{-- Gestores --}}
                <h2 class="bo-label">Gestores</h2>
                @if($n > 0)
                    <div class="bo-actions-wrap">
                        <div class="bo-actions bo-actions--{{ $n }}" style="--cols: {{ $cols }}">
                            @foreach($actions as $a)
                                @if($n === 1)
                                    {{-- Un solo gestor: tarjeta ancha con indicador clave --}}
                                    <a class="bo-area bo-area--main bo-area--wide" href="{{ $a['url'] }}">
                                        <span class="bo-area__ico" aria-hidden="true"><i class="bi {{ $a['def'][1] }}"></i></span>
                                        <span class="bo-area__txt">
                                            <strong>{{ $a['def'][0] }}</strong>
                                            <small>{{ $a['def'][2] }}</small>
                                        </span>
                                        <span class="bo-ind" aria-label="{{ $indicator['label'] }}: {{ $indicator['value'] }}">
                                            <span>{{ $indicator['label'] }}</span>
                                            <b>{{ $indicator['value'] }}</b>
                                        </span>
                                        <span class="bo-btn">Abrir <i class="bi bi-arrow-right" aria-hidden="true"></i></span>
                                    </a>
                                @else
                                    <a class="bo-area{{ $loop->first ? ' bo-area--main' : '' }}" href="{{ $a['url'] }}">
                                        <span class="bo-area__top">
                                            <span class="bo-area__n" aria-hidden="true">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                            <span class="bo-area__ico" aria-hidden="true"><i class="bi {{ $a['def'][1] }}"></i></span>
                                        </span>
                                        <strong>{{ $a['def'][0] }}</strong>
                                        <small>{{ $a['def'][2] }}</small>
                                        <span class="bo-area__go">Abrir <i class="bi bi-arrow-right" aria-hidden="true"></i></span>
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @else
                    {{-- Respaldo: si los nombres del menú no coinciden, igual se muestran tus módulos --}}
                    <div class="d-flex flex-wrap gap-2 mb-4">
                        @forelse($nav as $m)
                            <a class="btn btn-outline-primary btn-sm" href="{{ $m['url'] }}">{{ $m['label'] }}</a>
                        @empty
                            <span class="text-body-secondary">Tu perfil no tiene módulos habilitados.</span>
                        @endforelse
                    </div>
                @endif

                {{-- Cola de trabajo --}}
                <h2 class="bo-label">Cola de trabajo</h2>
                <section class="bo-card" aria-labelledby="bo-queue">
                    <div class="bo-card__head">
                        <h2 id="bo-queue">{{ $queueTitle }}</h2>
                        <span class="bo-badge bo-badge--count">{{ $queue->count() }}</span>
                    </div>
                    <div class="bo-scroll">
                        <table class="table bo-table">
                            <thead>
                                <tr>
                                    <th scope="col">Orden</th>
                                    <th scope="col">Paciente</th>
                                    <th scope="col">Examen</th>
                                    <th scope="col">Hora</th>
                                    <th scope="col">Estado</th>
                                    <th scope="col"><span class="visually-hidden">Acción</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($queue->take(8) as $row)
                                    @php
                                        $estado = strtoupper((string) ($row['estado'] ?? ''));
                                        $rowUrl = $row['url'] ?? ($rowBase ? $rowBase . '?orden=' . urlencode($row['id'] ?? $row['orden']) : null);
                                    @endphp
                                    <tr class="{{ ! empty($row['urgente']) ? 'bo-urgent' : '' }}">
                                        <td class="bo-code">{{ $row['orden'] }}</td>
                                        <td>{{ $row['paciente'] }}</td>
                                        <td>
                                            <span class="d-inline-flex flex-wrap align-items-center gap-2">
                                                {{ $row['examen'] }}
                                                @if(! empty($row['urgente']))
                                                    <span class="bo-badge bo-badge--urgent"><i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i> Urgente</span>
                                                @endif
                                            </span>
                                        </td>
                                        <td class="bo-time">{{ $row['hora'] ?? '—' }}</td>
                                        <td>
                                            <span class="bo-badge bo-badge--{{ $badge[$estado] ?? 'secondary' }}">
                                                {{ \Illuminate\Support\Str::title(str_replace('_', ' ', strtolower($estado))) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($rowUrl)
                                                <a class="bo-link" href="{{ $rowUrl }}">{{ $rowLabel }} <i class="bi bi-arrow-right" aria-hidden="true"></i><span class="visually-hidden"> orden {{ $row['orden'] }}</span></a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-body-secondary py-5">No hay elementos en la cola.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>

            {{-- ============ COLUMNA LATERAL (solo si hay contenido) ============ --}}
            @if($hasSide)
                <div class="col-xl-4">

                    {{-- Resumen --}}
                    @if($kpis->isNotEmpty())
                        <h2 class="bo-label">Resumen</h2>
                        <section class="bo-card mb-4" aria-label="Resumen">
                            @foreach($kpis as $k)
                                @php
                                    $href = $k['url'] ?? (isset($k['go']) ? $go($k['go']) : null);
                                    if ($href && ! empty($k['query'])) { $href .= (str_contains($href, '?') ? '&' : '?') . http_build_query($k['query']); }
                                @endphp
                                <{{ $href ? 'a' : 'div' }} class="bo-stat bo-t-{{ $toneOf($k['tone'] ?? null) }}" @if($href) href="{{ $href }}" @endif>
                                    <span class="bo-stat__dot" aria-hidden="true"></span>
                                    <span class="bo-stat__label">{{ $k['label'] }}</span>
                                    <span class="bo-stat__v">{{ $k['value'] }}</span>
                                </{{ $href ? 'a' : 'div' }}>
                            @endforeach
                        </section>
                    @endif

                    {{-- Urgentes --}}
                    @if($urgentes->isNotEmpty())
                        <h2 class="bo-label">Urgentes</h2>
                        <section class="bo-card mb-4" aria-label="Exámenes urgentes">
                            @foreach($urgentes->take(4) as $u)
                                <div class="bo-stat bo-t-danger">
                                    <span class="bo-stat__dot" aria-hidden="true"></span>
                                    <span class="bo-stat__label">
                                        <strong class="d-block">{{ $u['orden'] }}</strong>
                                        <small>{{ $u['examen'] }}</small>
                                    </span>
                                    <span class="bo-badge bo-badge--urgent bo-stat__end">Urgente</span>
                                </div>
                            @endforeach
                        </section>
                    @endif

                    {{-- Reportes (administración y finanzas) --}}
                    @if($reports->isNotEmpty())
                        <h2 class="bo-label">Reportes</h2>
                        <section class="bo-card" aria-label="Reportes">
                            @foreach($reports as $r)
                                <a class="bo-rep" href="{{ $r['url'] }}">
                                    <span class="bo-rep__ico" aria-hidden="true"><i class="bi {{ $r['def'][1] }}"></i></span>
                                    <span>{{ $r['def'][0] }}</span>
                                    <i class="bi bi-chevron-right" aria-hidden="true"></i>
                                </a>
                            @endforeach
                        </section>
                    @endif
                </div>
            @endif
        </div>
    </div>
@endsection