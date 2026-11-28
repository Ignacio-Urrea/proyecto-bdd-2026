@php
    $title     = $title ?? 'Registro';
    $routeName = $routeName ?? '';
    $statusLabels = config('imaj.status_labels.'.$routeName, []);
    $pageTitle = 'Ver ' . $title;
    $backUrl   = $routeName && \Illuminate\Support\Facades\Route::has($routeName . '.index')
        ? route($routeName . '.index')
        : route('dashboard');
    $editUrl   = $routeName && \Illuminate\Support\Facades\Route::has($routeName . '.edit')
        ? route($routeName . '.edit', $record->ID)
        : null;
@endphp

@extends('layouts.app')

@section('title', $pageTitle)

@section('content')
    @php
        $user     = Auth::user();
        $roleName = $user?->rol?->NOMBRE_ROL ?? 'OPERADOR';
        $routeKey = \Illuminate\Support\Str::lower(\Illuminate\Support\Str::ascii((string) $routeName . ' ' . (string) $title));

        /* ============ 1) IDENTIDAD VISUAL SEGÚN EL MÓDULO CRUD ============ */
        $meta = match (true) {
            str_contains($routeKey, 'paciente') => [
                'icon'    => 'bi-person-vcard',
                'color'   => '#8297D1',
                'soft'    => 'rgba(130, 151, 209, 0.22)',
                'btnText' => '#1D263D',
            ],
            str_contains($routeKey, 'usuario') || str_contains($routeKey, 'rol') => [
                'icon'    => 'bi-shield-lock',
                'color'   => '#F5E6D3',
                'soft'    => 'rgba(245, 230, 211, 0.20)',
                'btnText' => '#1D263D',
            ],
            str_contains($routeKey, 'sucursal') || str_contains($routeKey, 'clinica') => [
                'icon'    => 'bi-building',
                'color'   => '#9FB2B0',
                'soft'    => 'rgba(159, 178, 176, 0.22)',
                'btnText' => '#1D263D',
            ],
            str_contains($routeKey, 'prestacion') || str_contains($routeKey, 'arancel') || str_contains($routeKey, 'metodo') || str_contains($routeKey, 'pago') => [
                'icon'    => 'bi-cash-coin',
                'color'   => '#6BC9C6',
                'soft'    => 'rgba(107, 201, 198, 0.22)',
                'btnText' => '#1D263D',
            ],
            str_contains($routeKey, 'examen') || str_contains($routeKey, 'orden') || str_contains($routeKey, 'informe') => [
                'icon'    => 'bi-clipboard2-pulse',
                'color'   => '#8297D1',
                'soft'    => 'rgba(130, 151, 209, 0.22)',
                'btnText' => '#1D263D',
            ],
            default => [
                'icon'    => 'bi-file-earmark-text',
                'color'   => '#6BC9C6',
                'soft'    => 'rgba(107, 201, 198, 0.22)',
                'btnText' => '#1D263D',
            ],
        };

        $fieldIcon = function (string $name, string $type = 'text'): string {
            $k = strtolower($name);
            return match (true) {
                str_contains($k, 'rut') || str_contains($k, 'dni') || str_contains($k, 'run')             => 'bi-person-badge',
                str_contains($k, 'nombre') || str_contains($k, 'apellido') || str_contains($k, 'paciente') => 'bi-person',
                str_contains($k, 'correo') || str_contains($k, 'email') || $type === 'email'              => 'bi-envelope',
                str_contains($k, 'telefono') || str_contains($k, 'celular') || str_contains($k, 'fono')   => 'bi-telephone',
                str_contains($k, 'direccion') || str_contains($k, 'comuna') || str_contains($k, 'ciudad') => 'bi-geo-alt',
                str_contains($k, 'password') || str_contains($k, 'clave') || $type === 'password'         => 'bi-key',
                str_contains($k, 'rol') || str_contains($k, 'perfil')                                     => 'bi-shield-check',
                str_contains($k, 'sucursal') || str_contains($k, 'clinica')                               => 'bi-building',
                str_contains($k, 'fecha') || str_contains($k, 'nacimiento') || $type === 'date'           => 'bi-calendar-event',
                str_contains($k, 'hora') || $type === 'time'                                              => 'bi-clock',
                str_contains($k, 'precio') || str_contains($k, 'monto') || str_contains($k, 'valor') || str_contains($k, 'arancel') => 'bi-cash-stack',
                str_contains($k, 'codigo') || str_contains($k, 'folio')                                   => 'bi-upc-scan',
                str_contains($k, 'estado') || str_contains($k, 'activo') || str_contains($k, 'habilitada') || str_contains($k, 'ejecutada') || str_contains($k, 'adendum') => 'bi-toggles',
                str_contains($k, 'prestacion') || str_contains($k, 'examen')                              => 'bi-activity',
                str_contains($k, 'prevision') || str_contains($k, 'isapre') || str_contains($k, 'fonasa') => 'bi-heart-pulse',
                str_contains($k, 'descripcion') || str_contains($k, 'observacion') || str_contains($k, 'cuerpo') || str_contains($k, 'informe') || $type === 'textarea' => 'bi-card-text',
                default                                                                                   => 'bi-info-circle',
            };
        };

        /* ============ 2) CÁLCULO DE GRILLA ÓPTIMA (0 huecos vacíos) ============ */
        $fieldsCollection = collect($fields);
        $isLongField = function (array $f) use ($record): bool {
            $t = $f['type'] ?? 'text';
            $k = strtolower((string) ($f['name'] ?? ''));
            $v = (string) (data_get($record, $f['name']) ?? '');
            return $t === 'textarea'
                || str_contains($k, 'cuerpo')
                || str_contains($k, 'descripcion')
                || str_contains($k, 'observacion')
                || mb_strlen($v) > 85;
        };

        $compactCount        = $fieldsCollection->filter(fn ($f) => !$isLongField($f))->count();
        $promoteFirstCompact = ($compactCount % 2) === 1;
        $compactIndex        = 0;
        $today               = \Illuminate\Support\Str::ucfirst(now()->locale('es')->translatedFormat('l j \d\e F'));
    @endphp

    <style>
        /* =====================================================================
           CRUD SHOW BENTO · Base oscura mate + tipografía grande y legible
           ===================================================================== */
        .vv-crud-show {
            --vv-card: #1b1d21;
            --vv-card-elev: #23262c;
            --vv-input-bg: #131519;
            --vv-border: rgba(255, 255, 255, 0.08);
            --vv-border-hover: rgba(255, 255, 255, 0.18);
            --vv-text: #f3f4f6;
            --vv-muted: #9aa2b1;

            --vv-cream: #F5E6D3;
            --vv-navy: #28334F;
            --vv-navy-deep: #1D263D;
            --vv-blue: #8297D1;
            --vv-sage: #9FB2B0;
            --vv-mint: #6BC9C6;

            --vv-radius: 1.15rem;
            --vv-shadow: 0 12px 28px -14px rgba(0, 0, 0, 0.7);
            --g-accent: {{ $meta['color'] }};
            --g-soft: {{ $meta['soft'] }};
            --g-btn-text: {{ $meta['btnText'] }};
            color: var(--vv-text);
            width: 100%;
        }

        [data-bs-theme="light"] .vv-crud-show {
            --vv-card: #ffffff;
            --vv-card-elev: #f1f5f9;
            --vv-input-bg: #f8fafc;
            --vv-border: rgba(15, 23, 42, 0.10);
            --vv-border-hover: rgba(15, 23, 42, 0.22);
            --vv-text: #0f172a;
            --vv-muted: #64748b;
            --vv-shadow: 0 10px 26px -16px rgba(15, 23, 42, 0.2);
        }

        /* ---------- Cabecera Principal Protagonista ---------- */
        .vv-cs-topbar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 1.4rem;
            margin-bottom: 1.75rem;
        }

        .vv-cs-hero-ico {
            width: 4.35rem;
            height: 4.35rem;
            border-radius: 1.1rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 2.1rem;
            background: var(--g-accent);
            color: var(--g-btn-text);
            box-shadow: 0 10px 24px -10px var(--g-accent);
            flex-shrink: 0;
        }

        .vv-cs-title {
            margin: 0;
            font-size: 2.65rem;
            font-weight: 800;
            letter-spacing: -0.025em;
            line-height: 1.12;
            color: var(--vv-text);
        }

        .vv-cs-controls {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.75rem;
        }

        .vv-cs-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            padding: 0.62rem 1.15rem;
            font-size: 1.02rem;
            font-weight: 600;
            color: var(--vv-text);
            background: var(--vv-card);
            border: 1px solid var(--vv-border);
            border-radius: 0.85rem;
            transition: border-color 0.15s ease, background-color 0.15s ease;
        }

        .vv-cs-pill--id {
            background: var(--vv-navy);
            border-color: var(--vv-blue);
            color: var(--vv-cream);
            font-weight: 800;
        }

        a.vv-cs-pill:hover {
            background: var(--vv-navy);
            border-color: var(--vv-blue);
            color: var(--vv-cream);
        }

        /* ---------- Contenedor Principal Bento ---------- */
        .vv-cs-card {
            position: relative;
            background: var(--vv-card);
            border: 1px solid var(--vv-border);
            border-radius: var(--vv-radius);
            box-shadow: var(--vv-shadow);
            overflow: hidden;
            width: 100%;
        }

        .vv-cs-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--g-accent);
        }

        .vv-cs-card-head {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 0.85rem;
            padding: 1.15rem 1.85rem;
            border-bottom: 1px solid var(--vv-border);
            background: rgba(255, 255, 255, 0.015);
        }

        .vv-cs-section-title {
            margin: 0;
            font-size: 0.92rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.09em;
            color: var(--vv-muted);
        }

        .vv-cs-body {
            padding: 1.75rem 1.85rem;
        }

        /* ---------- Grilla de Detalles Óptima ---------- */
        .vv-detail-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1.35rem;
        }

        @media (max-width: 767.98px) {
            .vv-detail-grid {
                grid-template-columns: 1fr;
            }
        }

        .vv-detail-tile {
            background: var(--vv-card-elev);
            border: 1px solid var(--vv-border);
            border-radius: 1rem;
            padding: 1.45rem 1.55rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: border-color 0.16s ease;
        }

        .vv-detail-tile:hover {
            border-color: var(--vv-border-hover);
        }

        .vv-detail-tile.is-full {
            grid-column: 1 / -1;
        }

        .vv-detail-head {
            display: flex;
            align-items: center;
            gap: 0.72rem;
            margin-bottom: 0.9rem;
        }

        .vv-detail-ico {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 0.65rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.22rem;
            background: var(--g-accent);
            color: var(--g-btn-text);
            flex-shrink: 0;
        }

        .vv-detail-label {
            font-size: 1.12rem;
            font-weight: 700;
            color: var(--vv-muted);
            letter-spacing: 0.01em;
        }

        .vv-detail-value-box {
            background-color: var(--vv-input-bg);
            border: 1px solid var(--vv-border);
            border-radius: 0.75rem;
            padding: 0.95rem 1.15rem;
            min-height: 3.65rem;
            display: flex;
            align-items: center;
            font-size: 1.24rem;
            font-weight: 600;
            color: var(--vv-text);
            line-height: 1.5;
            white-space: pre-wrap;
            word-break: break-word;
        }

        .vv-bool-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.35rem 0.95rem;
            border-radius: 0.62rem;
            font-size: 1.05rem;
            font-weight: 800;
        }

        .vv-bool-badge--yes {
            background: rgba(107, 201, 198, 0.18);
            border: 1px solid var(--vv-mint);
            color: var(--vv-mint);
        }

        .vv-bool-badge--no {
            background: rgba(245, 230, 211, 0.12);
            border: 1px solid rgba(245, 230, 211, 0.45);
            color: var(--vv-cream);
        }

        /* ---------- Footer de Acciones ---------- */
        .vv-cs-footer {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 1.1rem;
            padding: 1.4rem 1.85rem;
            border-top: 1px solid var(--vv-border);
            background: rgba(0, 0, 0, 0.16);
        }

        .vv-btn-edit {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            padding: 0.9rem 1.75rem;
            border-radius: 0.8rem;
            border: 0;
            background: var(--g-accent);
            color: var(--g-btn-text);
            font-size: 1.12rem;
            font-weight: 800;
            text-decoration: none;
            box-shadow: 0 10px 22px -10px var(--g-accent);
            transition: transform 0.15s ease, filter 0.15s ease;
        }

        .vv-btn-edit:hover {
            transform: translateY(-2px);
            filter: brightness(1.07);
            color: var(--g-btn-text);
        }

        .vv-btn-back {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.9rem 1.45rem;
            border-radius: 0.8rem;
            border: 1px solid var(--vv-blue);
            background: var(--vv-navy);
            color: var(--vv-cream);
            font-size: 1.08rem;
            font-weight: 700;
            text-decoration: none;
            transition: background-color 0.15s ease, border-color 0.15s ease;
        }

        .vv-btn-back:hover {
            background: var(--vv-navy-deep);
            border-color: var(--vv-cream);
            color: var(--vv-cream);
        }
    </style>

    <div class="vv-crud-show">

        {{-- ============ CABECERA PRINCIPAL ÚNICA Y PROTAGONISTA ============ --}}
        <div class="vv-cs-topbar">
            <div class="d-flex align-items-center gap-3">
                <span class="vv-cs-hero-ico">
                    <i class="bi {{ $meta['icon'] }}"></i>
                </span>
                <div>
                    <h1 class="vv-cs-title">{{ $title }} #{{ $record->ID }}</h1>
                </div>
            </div>

            <div class="vv-cs-controls">
                <span class="vv-cs-pill vv-cs-pill--id">
                    <i class="bi bi-hash"></i>
                    ID {{ $record->ID }}
                </span>
                <span class="vv-cs-pill">
                    <i class="bi bi-person-badge" style="color: var(--g-accent);"></i>
                    {{ $roleName }}
                </span>
                <span class="vv-cs-pill">
                    <i class="bi bi-calendar3" style="color: var(--g-accent);"></i>
                    {{ $today }}
                </span>
                <a href="{{ $backUrl }}" class="vv-cs-pill text-decoration-none">
                    <i class="bi bi-arrow-left"></i>
                    Volver
                </a>
            </div>
        </div>

        {{-- ============ TARJETA DE DETALLE ============ --}}
        <section class="vv-cs-card">
            <div class="vv-cs-card-head">
                <h2 class="vv-cs-section-title">Información detallada del registro</h2>
                <span class="vv-cs-pill py-1 px-3" style="font-size: 0.9rem;">
                    {{ $fieldsCollection->count() }} {{ $fieldsCollection->count() === 1 ? 'atributo' : 'atributos' }}
                </span>
            </div>

            <div class="vv-cs-body">
                <div class="vv-detail-grid">
                    @foreach($fields as $field)
                        @php
                            $name   = $field['name'];
                            $type   = $field['type'] ?? 'text';
                            $value  = data_get($record, $name);
                            $displayValue = $name === 'ESTADO' && $value !== null
                                ? ($statusLabels[(string) $value] ?? $value)
                                : $value;
                            $isBool = in_array($name, ['HABILITADA', 'EJECUTADA', 'ADENDUM'], true);

                            if ($isLongField($field)) {
                                $isFull = true;
                            } else {
                                $isFull = ($compactIndex === 0 && $promoteFirstCompact);
                                $compactIndex++;
                            }

                            $ico = $fieldIcon($name, $type);
                        @endphp

                        <div class="vv-detail-tile {{ $isFull ? 'is-full' : '' }}">
                            <div class="vv-detail-head">
                                <span class="vv-detail-ico">
                                    <i class="bi {{ $ico }}"></i>
                                </span>
                                <span class="vv-detail-label">{{ $field['label'] }}</span>
                            </div>

                            <div class="vv-detail-value-box">
                                @if($isBool)
                                    @if((int) $value === 1)
                                        <span class="vv-bool-badge vv-bool-badge--yes">
                                            <i class="bi bi-check-circle-fill"></i>
                                            Sí
                                        </span>
                                    @else
                                        <span class="vv-bool-badge vv-bool-badge--no">
                                            <i class="bi bi-x-circle-fill"></i>
                                            No
                                        </span>
                                    @endif
                                @elseif($value !== null && $value !== '')
                                    {{ $displayValue }}
                                @else
                                    <span style="color: var(--vv-muted);">—</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="vv-cs-footer">
                <div class="d-flex align-items-center gap-2" style="font-size: 1.02rem; color: var(--vv-muted);">
                    <i class="bi bi-eye" style="color: var(--g-accent); font-size: 1.25rem;"></i>
                    <span>Vista de solo lectura del registro seleccionado.</span>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <a class="vv-btn-back" href="{{ $backUrl }}">
                        <i class="bi bi-arrow-left"></i>
                        Volver al listado
                    </a>
                    @if($editUrl)
                        <a class="vv-btn-edit" href="{{ $editUrl }}">
                            <i class="bi bi-pencil"></i>
                            Editar
                        </a>
                    @endif
                </div>
            </div>
        </section>
    </div>
@endsection