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

        // Busca primero "Gestor: <k>" y, si es un módulo directo (ej. Pacientes o Imágenes), cae al módulo base
        $go = function (string $k) use ($find) {
            if (str_starts_with($k, 'rep:')) {
                return $find(['reporte', substr($k, 4)]);
            }
            return $find(['gestor', $k]) ?? $find([$k]);
        };

        /* ============ 3) FLUJO CLÍNICO Y OPERATIVO POR ROL ============ */
        $defs = [
            'paciente'    => ['Registrar paciente',  'bi-person-plus',          'Crea o busca la ficha clínica'],
            'orden'       => ['Agendar orden',       'bi-calendar-plus',        'Cita del paciente en una sucursal'],
            'presupuesto' => ['Generar presupuesto', 'bi-calculator',           'Cotiza las prestaciones de una orden'],
            'pago'        => ['Registrar pago',      'bi-credit-card',          'Asocia un pago a un presupuesto'],
            'examen'      => ['Actualizar examen',   'bi-clipboard2-pulse',     'Cambia el estado y cierra la orden'],
            'imagen'      => ['Subir imágenes',      'bi-file-earmark-image',   'Adjunta placas y archivos del estudio'],
            'informe'     => ['Publicar informe',    'bi-file-earmark-medical', 'Diagnóstico médico y adendums'],
        ];
        $byRole = [
            'recepcion' => ['paciente', 'orden', 'presupuesto', 'pago'],
            'tecnico'   => ['examen', 'imagen'],
            'radiologo' => ['informe', 'imagen', 'examen'],
            'finanzas'  => ['pago', 'presupuesto'],
            'admin'     => ['paciente', 'orden', 'presupuesto', 'pago', 'examen', 'imagen', 'informe'],
        ];
        $actions = collect($byRole[$group])
            ->map(fn ($k) => ['key' => $k, 'def' => $defs[$k], 'url' => $go($k)])
            ->filter(fn ($a) => $a['url'])
            ->values();

        $quickPacienteUrl = $go('paciente');
        $quickOrdenUrl    = $go('orden');

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
                ['label' => 'Órdenes de hoy',          'value' => 42,             'tone' => 'primary', 'go' => 'rep:orden'],
                ['label' => 'Pendientes / ejecutadas', 'value' => '12 / 30',      'tone' => 'warning', 'go' => 'rep:orden'],
                ['label' => 'Ingresos del mes',        'value' => '$ 18.430.000', 'tone' => 'success', 'go' => 'rep:ingreso'],
                ['label' => 'Exámenes urgentes',       'value' => 4,              'tone' => 'danger',  'go' => 'rep:examen'],
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

        $badge  = ['PENDIENTE' => 'warning', 'AGENDADA' => 'info', 'EN_PROCESO' => 'primary', 'EJECUTADA' => 'success', 'INFORMADA' => 'success', 'CANCELADA' => 'secondary'];
        $toneOf = fn ($t) => in_array($t, ['primary', 'success', 'warning', 'danger', 'info', 'secondary'], true) ? $t : 'secondary';

        $kpiIcons = [
            'primary'   => 'bi-calendar2-check',
            'success'   => 'bi-graph-up-arrow',
            'warning'   => 'bi-hourglass-split',
            'danger'    => 'bi-exclamation-diamond',
            'info'      => 'bi-activity',
            'secondary' => 'bi-bar-chart',
        ];

        $hasSide    = $urgentes->isNotEmpty() || $reports->isNotEmpty();
        $mainCol    = $hasSide ? 'col-xl-8' : 'col-12';
        $actionCnt  = $actions->count();
        $primaryKpi = $kpis->first() ?? ['label' => 'En cola', 'value' => $queue->count()];

        $hour      = now()->hour;
        $greeting  = $hour < 12 ? 'Buenos días' : ($hour < 20 ? 'Buenas tardes' : 'Buenas noches');
        $firstName = \Illuminate\Support\Str::of((string) $user->NOMBRE_USUARIO)->before(' ')->title();
        $today     = \Illuminate\Support\Str::ucfirst(now()->locale('es')->translatedFormat('l j \d\e F'));
    @endphp

    <style>
        /* =====================================================================
           BENTO BACK-OFFICE · Flujo clínico ordenado + brillo celeste
           ===================================================================== */
        .vv-dash {
            --vv-card: #1b1d21;
            --vv-card-elev: #23262c;
            --vv-border: rgba(107, 201, 198, 0.16);
            --vv-border-hover: rgba(107, 201, 198, 0.45);
            --vv-text: #f3f4f6;
            --vv-muted: #9aa2b1;

            /* Paleta sólida y saturada */
            --vv-cream: #F5E6D3;
            --vv-navy: #28334F;
            --vv-navy-deep: #1D263D;
            --vv-blue: #8297D1;
            --vv-sage: #9FB2B0;
            --vv-mint: #6BC9C6;
            --vv-mint-bright: #88E5E2;

            /* Brillos celestes */
            --vv-glow: 0 0 22px rgba(107, 201, 198, 0.55), 0 0 44px rgba(107, 201, 198, 0.22);
            --vv-glow-soft: 0 10px 28px -8px rgba(107, 201, 198, 0.35), 0 0 18px rgba(107, 201, 198, 0.16);

            --vv-radius: 1.15rem;
            --vv-shadow: 0 12px 28px -14px rgba(0, 0, 0, 0.75);
            color: var(--vv-text);
            width: 100%;
        }

        [data-bs-theme="light"] .vv-dash {
            --vv-card: #ffffff;
            --vv-card-elev: #f1f5f9;
            --vv-border: rgba(15, 23, 42, 0.09);
            --vv-border-hover: rgba(107, 201, 198, 0.6);
            --vv-text: #0f172a;
            --vv-muted: #64748b;
            --vv-shadow: 0 10px 26px -16px rgba(15, 23, 42, 0.2);
        }

        /* ---------- Separador de sección ---------- */
        .vv-sec-label {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin: 0 0 0.9rem;
            font-size: 0.95rem;
            font-weight: 800;
            letter-spacing: 0.09em;
            text-transform: uppercase;
            color: var(--vv-muted);
        }

        .vv-sec-label::after {
            content: "";
            flex: 1;
            height: 1px;
            background: linear-gradient(90deg, rgba(107, 201, 198, 0.35), var(--vv-border));
        }

        /* ---------- Cabecera Principal Protagonista ---------- */
        .vv-topbar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 1.25rem;
            margin-bottom: 1.75rem;
        }

        .vv-hero-ico {
            width: 4.35rem;
            height: 4.35rem;
            border-radius: 1.15rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 2.2rem;
            background: linear-gradient(135deg, var(--vv-mint-bright) 0%, var(--vv-mint) 100%);
            color: var(--vv-navy-deep);
            box-shadow: var(--vv-glow);
            flex-shrink: 0;
        }

        .vv-title {
            margin: 0;
            font-size: 2.6rem;
            font-weight: 800;
            letter-spacing: -0.025em;
            line-height: 1.12;
            color: var(--vv-text);
            text-shadow: 0 0 28px rgba(107, 201, 198, 0.18);
        }

        .vv-controls {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.7rem;
        }

        .vv-quick-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            padding: 0.65rem 1.2rem;
            border-radius: 0.85rem;
            font-size: 1.02rem;
            font-weight: 800;
            text-decoration: none;
            transition: transform 0.16s ease, box-shadow 0.16s ease, filter 0.16s ease;
            white-space: nowrap;
        }

        .vv-quick-btn--primary {
            background: linear-gradient(135deg, var(--vv-mint-bright) 0%, var(--vv-mint) 100%);
            color: var(--vv-navy-deep);
            box-shadow: 0 0 20px rgba(107, 201, 198, 0.5);
        }

        .vv-quick-btn--primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--vv-glow);
            color: var(--vv-navy-deep);
        }

        .vv-quick-btn--secondary {
            background: var(--vv-navy);
            color: var(--vv-cream);
            border: 1px solid rgba(107, 201, 198, 0.55);
            box-shadow: 0 0 14px rgba(107, 201, 198, 0.22);
        }

        .vv-quick-btn--secondary:hover {
            transform: translateY(-2px);
            border-color: var(--vv-mint-bright);
            box-shadow: var(--vv-glow-soft);
            color: var(--vv-cream);
        }

        .vv-pill-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            padding: 0.62rem 1.1rem;
            font-size: 1rem;
            font-weight: 600;
            color: var(--vv-text);
            background: var(--vv-card);
            border: 1px solid var(--vv-border);
            border-radius: 0.85rem;
            box-shadow: 0 0 16px rgba(107, 201, 198, 0.1);
        }

        .vv-segmented {
            display: inline-flex;
            align-items: center;
            padding: 0.28rem;
            background: var(--vv-card);
            border: 1px solid var(--vv-border);
            border-radius: 0.9rem;
            gap: 0.25rem;
        }

        .vv-seg-btn {
            border: 0;
            background: transparent;
            color: var(--vv-muted);
            font-size: 0.98rem;
            font-weight: 600;
            padding: 0.48rem 1rem;
            border-radius: 0.7rem;
            transition: all 0.16s ease;
            cursor: pointer;
        }

        .vv-seg-btn:hover {
            color: var(--vv-mint-bright);
        }

        .vv-seg-btn.is-active {
            background: var(--vv-navy);
            color: var(--vv-cream);
            font-weight: 700;
            border: 1px solid rgba(107, 201, 198, 0.45);
            box-shadow: 0 0 16px rgba(107, 201, 198, 0.28);
        }

        /* ---------- Fila 1: KPIs compactos ---------- */
        .vv-kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(255px, 1fr));
            gap: 1.05rem;
            margin-bottom: 1.7rem;
        }

        .vv-kpi-card {
            --k-color: var(--vv-blue);
            --k-ink: var(--vv-navy-deep);
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 0.75rem;
            padding: 1.15rem 1.35rem;
            background: var(--vv-card);
            border: 1px solid var(--vv-border);
            border-radius: var(--vv-radius);
            box-shadow: var(--vv-shadow), 0 0 20px -10px rgba(107, 201, 198, 0.22);
            text-decoration: none;
            color: var(--vv-text);
            overflow: hidden;
            transition: transform 0.18s ease, border-color 0.18s ease, box-shadow 0.18s ease, background-color 0.18s ease;
        }

        .vv-kpi-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3.5px;
            background: var(--k-color);
            box-shadow: 0 2px 16px rgba(107, 201, 198, 0.65);
        }

        a.vv-kpi-card:hover {
            transform: translateY(-2px);
            border-color: var(--vv-mint);
            background: var(--vv-card-elev);
            box-shadow: var(--vv-glow-soft);
            color: var(--vv-text);
        }

        .vv-kpi-card--primary { --k-color: var(--vv-blue);  --k-ink: var(--vv-navy-deep); }
        .vv-kpi-card--success { --k-color: var(--vv-mint);  --k-ink: var(--vv-navy-deep); }
        .vv-kpi-card--warning { --k-color: var(--vv-sage);  --k-ink: var(--vv-navy-deep); }
        .vv-kpi-card--danger  { --k-color: var(--vv-cream); --k-ink: var(--vv-navy-deep); }

        .vv-kpi-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
        }

        .vv-kpi-label {
            font-size: 1.05rem;
            font-weight: 600;
            color: var(--vv-muted);
            line-height: 1.25;
        }

        .vv-kpi-ico {
            width: 2.65rem;
            height: 2.65rem;
            border-radius: 0.78rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            background: var(--k-color);
            color: var(--k-ink);
            box-shadow: 0 0 18px rgba(107, 201, 198, 0.42);
            flex-shrink: 0;
        }

        .vv-kpi-bottom {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 0.65rem;
        }

        .vv-kpi-value {
            font-size: 2rem;
            font-weight: 800;
            line-height: 1.08;
            letter-spacing: -0.02em;
            font-variant-numeric: tabular-nums;
            color: var(--vv-text);
            white-space: nowrap;
        }

        .vv-kpi-go {
            font-size: 0.98rem;
            font-weight: 700;
            color: var(--vv-mint);
            text-shadow: 0 0 12px rgba(107, 201, 198, 0.45);
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            white-space: nowrap;
        }

        /* ---------- Fila 2: Flujo Clínico Cronológico ---------- */
        .vv-gestor-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(255px, 1fr));
            gap: 1.15rem;
            margin-bottom: 1.9rem;
        }

        .vv-gestor-card {
            --g-color: var(--vv-blue);
            --g-ink: var(--vv-navy-deep);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 1.45rem 1.55rem;
            background: var(--vv-card);
            border: 1px solid var(--vv-border);
            border-radius: var(--vv-radius);
            box-shadow: var(--vv-shadow), 0 0 20px -10px rgba(107, 201, 198, 0.2);
            text-decoration: none;
            color: var(--vv-text);
            transition: transform 0.18s ease, border-color 0.18s ease, box-shadow 0.18s ease, background-color 0.18s ease;
        }

        .vv-gestor-card:nth-child(1) { --g-color: var(--vv-mint); }
        .vv-gestor-card:nth-child(2) { --g-color: var(--vv-blue); }
        .vv-gestor-card:nth-child(3) { --g-color: var(--vv-cream); }
        .vv-gestor-card:nth-child(4) { --g-color: var(--vv-sage); }
        .vv-gestor-card:nth-child(5) { --g-color: var(--vv-blue); }
        .vv-gestor-card:nth-child(6) { --g-color: var(--vv-mint); }
        .vv-gestor-card:nth-child(7) { --g-color: var(--vv-cream); }

        .vv-gestor-card:hover {
            transform: translateY(-3px);
            border-color: var(--vv-mint);
            background: var(--vv-card-elev);
            box-shadow: var(--vv-glow-soft);
            color: var(--vv-text);
        }

        .vv-gestor-card.is-featured {
            background: linear-gradient(135deg, var(--vv-navy) 0%, var(--vv-navy-deep) 100%);
            border-color: var(--vv-mint);
            box-shadow: 0 12px 32px -10px rgba(107, 201, 198, 0.45), 0 0 22px rgba(107, 201, 198, 0.22), inset 0 1px 0 rgba(107, 201, 198, 0.45);
            color: var(--vv-cream);
        }

        .vv-gestor-card.is-featured .vv-gestor-title {
            color: var(--vv-cream);
        }

        .vv-gestor-card.is-featured .vv-gestor-desc {
            color: var(--vv-sage);
        }

        .vv-gestor-card.is-featured .vv-gestor-num {
            color: var(--vv-mint-bright);
            opacity: 0.8;
        }

        /* Cuando el rol tiene 1 solo gestor: tarjeta ancha destacada */
        .vv-gestor-banner {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 1.5rem 2rem;
            padding: 1.75rem 2rem;
            background: linear-gradient(135deg, var(--vv-navy) 0%, var(--vv-navy-deep) 100%);
            border: 1.5px solid var(--vv-mint);
            border-radius: var(--vv-radius);
            box-shadow: 0 14px 36px -10px rgba(107, 201, 198, 0.5), 0 0 26px rgba(107, 201, 198, 0.25);
            text-decoration: none;
            color: var(--vv-cream);
            margin-bottom: 1.9rem;
            transition: transform 0.18s ease, box-shadow 0.18s ease, filter 0.18s ease;
        }

        .vv-gestor-banner:hover {
            transform: translateY(-2px);
            box-shadow: 0 16px 42px -8px rgba(107, 201, 198, 0.65), 0 0 32px rgba(107, 201, 198, 0.35);
            filter: brightness(1.06);
            color: var(--vv-cream);
        }

        .vv-gestor-banner__ico {
            width: 3.9rem;
            height: 3.9rem;
            border-radius: 1rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.85rem;
            background: linear-gradient(135deg, var(--vv-mint-bright) 0%, var(--vv-mint) 100%);
            color: var(--vv-navy-deep);
            box-shadow: var(--vv-glow);
            flex-shrink: 0;
        }

        .vv-gestor-banner__stat {
            padding: 0 1.65rem;
            border-left: 1px solid rgba(107, 201, 198, 0.4);
            text-align: right;
        }

        .vv-gestor-banner__btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.8rem 1.45rem;
            border-radius: 0.8rem;
            background: linear-gradient(135deg, var(--vv-mint-bright) 0%, var(--vv-mint) 100%);
            color: var(--vv-navy-deep);
            box-shadow: 0 0 22px rgba(107, 201, 198, 0.6);
            font-weight: 800;
            font-size: 1.05rem;
        }

        .vv-gestor-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .vv-gestor-num {
            font-size: 1.55rem;
            font-weight: 800;
            color: var(--vv-muted);
            opacity: 0.55;
            font-variant-numeric: tabular-nums;
        }

        .vv-gestor-ico {
            width: 3.1rem;
            height: 3.1rem;
            border-radius: 0.85rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.48rem;
            background: var(--g-color);
            color: var(--g-ink);
            box-shadow: 0 0 20px rgba(107, 201, 198, 0.45);
            transition: transform 0.18s ease, box-shadow 0.18s ease;
        }

        .vv-gestor-card:hover .vv-gestor-ico {
            transform: scale(1.08);
            box-shadow: var(--vv-glow);
        }

        .vv-gestor-title {
            font-size: 1.28rem;
            font-weight: 700;
            margin: 0 0 0.35rem;
            color: var(--vv-text);
        }

        .vv-gestor-desc {
            font-size: 1.02rem;
            color: var(--vv-muted);
            line-height: 1.4;
            margin: 0 0 1.15rem;
        }

        .vv-gestor-cta {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            font-size: 1.02rem;
            font-weight: 700;
            color: var(--vv-mint);
            text-shadow: 0 0 12px rgba(107, 201, 198, 0.4);
            margin-top: auto;
        }

        /* ---------- Tarjeta Contenedora Base (Tabla y Lateral) ---------- */
        .vv-card {
            background: var(--vv-card);
            border: 1px solid var(--vv-border);
            border-radius: var(--vv-radius);
            box-shadow: var(--vv-shadow), 0 0 26px -12px rgba(107, 201, 198, 0.22);
            overflow: hidden;
        }

        .vv-card-head {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 0.85rem;
            padding: 1.3rem 1.6rem;
            border-bottom: 1px solid var(--vv-border);
        }

        .vv-card-title {
            margin: 0 0 0.15rem;
            font-size: 1.32rem;
            font-weight: 700;
            color: var(--vv-text);
        }

        /* ---------- Tabla Cola de Trabajo ---------- */
        .vv-table {
            width: 100%;
            margin: 0;
            font-size: 1.12rem;
            color: var(--vv-text);
            border-collapse: collapse;
        }

        .vv-table th {
            font-size: 0.9rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--vv-muted);
            background: var(--vv-card-elev);
            padding: 1.05rem 1.35rem;
            border-bottom: 1px solid var(--vv-border);
            white-space: nowrap;
        }

        .vv-table td {
            font-size: 1.12rem;
            padding: 1.12rem 1.35rem;
            border-bottom: 1px solid var(--vv-border);
            vertical-align: middle;
        }

        .vv-table tbody tr:last-child td {
            border-bottom: 0;
        }

        .vv-table tbody tr:hover {
            background: rgba(107, 201, 198, 0.08);
        }

        .vv-table tbody tr.is-urgent {
            background: rgba(40, 51, 79, 0.55);
        }

        .vv-table tbody tr.is-urgent td:first-child {
            box-shadow: inset 4px 0 0 var(--vv-mint);
        }

        .vv-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.36rem 0.88rem;
            border-radius: 999px;
            font-size: 0.92rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .vv-badge--primary   { background: var(--vv-blue);  color: var(--vv-navy-deep); box-shadow: 0 0 12px rgba(130, 151, 209, 0.35); }
        .vv-badge--success   { background: var(--vv-mint);  color: var(--vv-navy-deep); box-shadow: 0 0 14px rgba(107, 201, 198, 0.5); }
        .vv-badge--warning   { background: var(--vv-cream); color: var(--vv-navy-deep); }
        .vv-badge--info      { background: var(--vv-sage);  color: var(--vv-navy-deep); }
        .vv-badge--secondary { background: var(--vv-navy);  color: var(--vv-cream); border: 1px solid var(--vv-sage); }
        .vv-badge--urgent    { background: var(--vv-cream); color: var(--vv-navy-deep); border: 1.5px solid var(--vv-mint); font-weight: 800; box-shadow: 0 0 12px rgba(107, 201, 198, 0.35); }

        .vv-action-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.52rem 1.08rem;
            border-radius: 0.68rem;
            font-size: 0.98rem;
            font-weight: 700;
            color: var(--vv-cream);
            background: var(--vv-navy);
            border: 1px solid rgba(107, 201, 198, 0.55);
            box-shadow: 0 0 14px rgba(107, 201, 198, 0.2);
            text-decoration: none;
            transition: all 0.15s ease;
            white-space: nowrap;
        }

        .vv-action-btn:hover {
            background: linear-gradient(135deg, var(--vv-mint-bright) 0%, var(--vv-mint) 100%);
            border-color: var(--vv-mint-bright);
            box-shadow: var(--vv-glow);
            color: var(--vv-navy-deep);
        }

        /* ---------- Lateral: Urgentes y Reportes ---------- */
        .vv-side-list {
            padding: 1rem 1.25rem;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .vv-side-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.9rem;
            padding: 0.95rem 1.15rem;
            border-radius: 0.85rem;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--vv-border);
            text-decoration: none;
            color: var(--vv-text);
            transition: background-color 0.15s ease, border-color 0.15s ease, box-shadow 0.15s ease;
        }

        a.vv-side-item:hover {
            background: var(--vv-navy);
            border-color: var(--vv-mint);
            box-shadow: var(--vv-glow-soft);
            color: var(--vv-cream);
        }

        .vv-rep-ico {
            width: 2.75rem;
            height: 2.75rem;
            border-radius: 0.75rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.22rem;
            background: linear-gradient(135deg, var(--vv-mint-bright) 0%, var(--vv-mint) 100%);
            color: var(--vv-navy-deep);
            box-shadow: 0 0 18px rgba(107, 201, 198, 0.55);
            flex-shrink: 0;
        }
    </style>

    <div class="vv-dash">

        {{-- ============ ENCABEZADO PRINCIPAL PROTAGONISTA ============ --}}
        <div class="vv-topbar">
            <div class="d-flex align-items-center gap-3">
                <span class="vv-hero-ico">
                    <i class="bi bi-lungs"></i>
                </span>
                <div>
                    <h1 class="vv-title">{{ $greeting }}, {{ $firstName }}</h1>
                </div>
            </div>

            <div class="vv-controls">
                @if($quickPacienteUrl)
                    <a href="{{ $quickPacienteUrl }}" class="vv-quick-btn vv-quick-btn--primary">
                        <i class="bi bi-person-plus-fill"></i>
                        Nuevo paciente
                    </a>
                @endif

                @if($quickOrdenUrl)
                    <a href="{{ $quickOrdenUrl }}" class="vv-quick-btn vv-quick-btn--secondary">
                        <i class="bi bi-calendar-plus"></i>
                        Nueva orden
                    </a>
                @endif

                <span class="vv-pill-badge">
                    <i class="bi bi-person-badge" style="color: var(--vv-mint);"></i>
                    {{ $roleName }}
                </span>

                @if($sucursalNombre)
                    <span class="vv-pill-badge">
                        <i class="bi bi-building" style="color: var(--vv-blue);"></i>
                        {{ $sucursalNombre }}
                    </span>
                @endif

                <div class="vv-segmented">
                    <span class="vv-seg-btn is-active">{{ $today }}</span>
                    <button type="button" class="vv-seg-btn" onclick="location.reload()" title="Actualizar datos">
                        <i class="bi bi-arrow-clockwise me-1"></i>Actualizar
                    </button>
                </div>
            </div>
        </div>

        {{-- ============ BLOQUE 1: INDICADORES CLAVE COMPACTOS ============ --}}
        @if($kpis->isNotEmpty())
            <h2 class="vv-sec-label">Resumen del turno</h2>
            <div class="vv-kpi-grid">
                @foreach($kpis as $k)
                    @php
                        $tone = $toneOf($k['tone'] ?? null);
                        $href = $k['url'] ?? (isset($k['go']) ? $go($k['go']) : null);
                        if ($href && ! empty($k['query'])) {
                            $href .= (str_contains($href, '?') ? '&' : '?') . http_build_query($k['query']);
                        }
                    @endphp
                    <{{ $href ? 'a' : 'div' }} class="vv-kpi-card vv-kpi-card--{{ $tone }}" @if($href) href="{{ $href }}" @endif>
                        <div class="vv-kpi-top">
                            <span class="vv-kpi-label">{{ $k['label'] }}</span>
                            <span class="vv-kpi-ico">
                                <i class="bi {{ $kpiIcons[$tone] ?? 'bi-bar-chart' }}"></i>
                            </span>
                        </div>
                        <div class="vv-kpi-bottom">
                            <span class="vv-kpi-value">{{ $k['value'] }}</span>
                            @if($href)
                                <span class="vv-kpi-go">Ver <i class="bi bi-arrow-right"></i></span>
                            @endif
                        </div>
                    </{{ $href ? 'a' : 'div' }}>
                @endforeach
            </div>
        @endif

        {{-- ============ BLOQUE 2: FLUJO CLÍNICO Y OPERATIVO ============ --}}
        <h2 class="vv-sec-label">Flujo operativo</h2>
        @if($actionCnt === 1)
            @php $single = $actions->first(); @endphp
            <a href="{{ $single['url'] }}" class="vv-gestor-banner">
                <div class="d-flex align-items-center gap-3">
                    <span class="vv-gestor-banner__ico">
                        <i class="bi {{ $single['def'][1] }}"></i>
                    </span>
                    <div>
                        <div style="font-size: 1.6rem; font-weight: 800; line-height: 1.2;">{{ $single['def'][0] }}</div>
                        <div style="opacity: 0.9; font-size: 1.08rem;">{{ $single['def'][2] }}</div>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-4 ms-auto">
                    <div class="vv-gestor-banner__stat d-none d-sm-block">
                        <div style="font-size: 0.94rem; opacity: 0.88;">{{ $primaryKpi['label'] }}</div>
                        <div style="font-size: 2.15rem; font-weight: 800; line-height: 1.05;">{{ $primaryKpi['value'] }}</div>
                    </div>
                    <span class="vv-gestor-banner__btn">
                        Abrir módulo <i class="bi bi-arrow-right"></i>
                    </span>
                </div>
            </a>
        @elseif($actionCnt > 1)
            <div class="vv-gestor-grid">
                @foreach($actions as $a)
                    <a href="{{ $a['url'] }}" class="vv-gestor-card {{ $loop->first ? 'is-featured' : '' }}">
                        <div>
                            <div class="vv-gestor-top">
                                <span class="vv-gestor-num">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                <span class="vv-gestor-ico">
                                    <i class="bi {{ $a['def'][1] }}"></i>
                                </span>
                            </div>
                            <h3 class="vv-gestor-title">{{ $a['def'][0] }}</h3>
                            <p class="vv-gestor-desc">{{ $a['def'][2] }}</p>
                        </div>
                        <span class="vv-gestor-cta">
                            Abrir módulo <i class="bi bi-arrow-right"></i>
                        </span>
                    </a>
                @endforeach
            </div>
        @else
            <div class="d-flex flex-wrap gap-2 mb-4">
                @forelse($nav as $m)
                    <a class="vv-action-btn" href="{{ $m['url'] }}">{{ $m['label'] }}</a>
                @empty
                    <span style="color: var(--vv-muted); font-size: 1.1rem;">Tu perfil no tiene módulos habilitados.</span>
                @endforelse
            </div>
        @endif

        {{-- ============ BLOQUE 3: COLA DE TRABAJO + URGENTES / REPORTES ============ --}}
        <div class="row g-4">
            <div class="{{ $mainCol }}">
                <h2 class="vv-sec-label">Cola de trabajo</h2>
                <section class="vv-card">
                    <div class="vv-card-head">
                        <div>
                            <h3 class="vv-card-title">{{ $queueTitle }}</h3>
                            <span style="font-size: 0.98rem; color: var(--vv-muted);">Ordenado por prioridad clínica y hora</span>
                        </div>
                        <span class="vv-pill-badge">{{ $queue->count() }} registros</span>
                    </div>

                    <div class="table-responsive">
                        <table class="vv-table">
                            <thead>
                                <tr>
                                    <th>Orden</th>
                                    <th>Paciente</th>
                                    <th>Examen</th>
                                    <th>Hora</th>
                                    <th>Estado</th>
                                    <th class="text-end">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($queue->take(8) as $row)
                                    @php
                                        $estado = strtoupper((string) ($row['estado'] ?? ''));
                                        $rowUrl = $row['url']
                                            ?? (\Illuminate\Support\Facades\Route::has('ordenes.show')
                                                ? route('ordenes.show', $row['id'])
                                                : ($find(['orden']) ? $find(['orden']) . '?orden=' . urlencode($row['id'] ?? $row['orden']) : null));
                                    @endphp
                                    <tr class="{{ ! empty($row['urgente']) ? 'is-urgent' : '' }}">
                                        <td class="fw-bold text-nowrap" style="font-variant-numeric: tabular-nums;">{{ $row['orden'] }}</td>
                                        <td>{{ $row['paciente'] }}</td>
                                        <td>
                                            <div class="d-inline-flex flex-wrap align-items-center gap-2">
                                                <span>{{ $row['examen'] }}</span>
                                                @if(! empty($row['urgente']))
                                                    <span class="vv-badge vv-badge--urgent">
                                                        <i class="bi bi-exclamation-triangle-fill"></i> Urgente
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td style="color: var(--vv-muted); font-variant-numeric: tabular-nums;">{{ $row['hora'] ?? '—' }}</td>
                                        <td>
                                            <span class="vv-badge vv-badge--{{ $badge[$estado] ?? 'secondary' }}">
                                                {{ \Illuminate\Support\Str::title(str_replace('_', ' ', strtolower($estado))) }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            @if($rowUrl)
                                                <a href="{{ $rowUrl }}" class="vv-action-btn">
                                                    {{ $rowLabel }} <i class="bi bi-arrow-right"></i>
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5" style="color: var(--vv-muted); font-size: 1.15rem;">
                                            No hay elementos pendientes en la cola.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>

            @if($hasSide)
                <div class="col-xl-4">
                    <div class="d-flex flex-column gap-4">
                        @if($urgentes->isNotEmpty())
                            <div>
                                <h2 class="vv-sec-label">Prioridad alta</h2>
                                <section class="vv-card">
                                    <div class="vv-card-head">
                                        <h3 class="vv-card-title">Exámenes urgentes</h3>
                                        <span class="vv-badge vv-badge--urgent">{{ $urgentes->count() }}</span>
                                    </div>
                                    <div class="vv-side-list">
                                        @foreach($urgentes->take(4) as $u)
                                            <div class="vv-side-item">
                                                <div class="min-w-0">
                                                    <div class="fw-bold" style="font-size: 1.1rem;">{{ $u['orden'] }}</div>
                                                    <div class="text-truncate" style="font-size: 0.96rem; color: var(--vv-muted);">{{ $u['examen'] }}</div>
                                                </div>
                                                <span class="vv-badge vv-badge--urgent">Urgente</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </section>
                            </div>
                        @endif

                        @if($reports->isNotEmpty())
                            <div>
                                <h2 class="vv-sec-label">Analítica</h2>
                                <section class="vv-card">
                                    <div class="vv-card-head">
                                        <h3 class="vv-card-title">Reportes del sistema</h3>
                                        <i class="bi bi-bar-chart-line fs-5" style="color: var(--vv-muted);"></i>
                                    </div>
                                    <div class="vv-side-list">
                                        @foreach($reports as $r)
                                            <a href="{{ $r['url'] }}" class="vv-side-item">
                                                <span class="d-inline-flex align-items-center gap-3">
                                                    <span class="vv-rep-ico">
                                                        <i class="bi {{ $r['def'][1] }}"></i>
                                                    </span>
                                                    <span class="fw-semibold" style="font-size: 1.08rem;">{{ $r['def'][0] }}</span>
                                                </span>
                                                <i class="bi bi-chevron-right" style="color: var(--vv-muted); font-size: 1.05rem;"></i>
                                            </a>
                                        @endforeach
                                    </div>
                                </section>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection