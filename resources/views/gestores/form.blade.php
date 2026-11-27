@php
    $title     = $title ?? 'Gestor';
    $routeName = $routeName ?? '';
@endphp

@extends('layouts.app')

@section('content')
    @php
        $user     = Auth::user();
        $roleName = $user?->rol?->NOMBRE_ROL ?? 'OPERADOR';
        $routeKey = \Illuminate\Support\Str::lower(\Illuminate\Support\Str::ascii((string) $routeName . ' ' . (string) $title));

        /* ============ 1) IDENTIDAD VISUAL SEGÚN EL GESTOR (paleta intensa) ============ */
        $meta = match (true) {
            str_contains($routeKey, 'orden') => [
                'subtitle' => 'Asigna paciente, sucursal y bloque horario para registrar una nueva orden clínica.',
                'icon'     => 'bi-calendar-plus',
                'color'    => '#8297D1',
                'soft'     => 'rgba(130, 151, 209, 0.22)',
                'border'   => 'rgba(130, 151, 209, 0.55)',
                'btnText'  => '#1D263D',
            ],
            str_contains($routeKey, 'presupuesto') => [
                'subtitle' => 'Vincula una orden existente y marca las prestaciones solicitadas para calcular su arancel.',
                'icon'     => 'bi-calculator',
                'color'    => '#9FB2B0',
                'soft'     => 'rgba(159, 178, 176, 0.22)',
                'border'   => 'rgba(159, 178, 176, 0.55)',
                'btnText'  => '#1D263D',
            ],
            str_contains($routeKey, 'pago') => [
                'subtitle' => 'Registra el monto abonado y el medio de pago asociado al presupuesto seleccionado.',
                'icon'     => 'bi-credit-card',
                'color'    => '#6BC9C6',
                'soft'     => 'rgba(107, 201, 198, 0.22)',
                'border'   => 'rgba(107, 201, 198, 0.55)',
                'btnText'  => '#1D263D',
            ],
            str_contains($routeKey, 'examen') => [
                'subtitle' => 'Actualiza el estado operativo de la toma radiológica y controla el cierre de la orden.',
                'icon'     => 'bi-clipboard2-pulse',
                'color'    => '#F5E6D3',
                'soft'     => 'rgba(245, 230, 211, 0.20)',
                'border'   => 'rgba(245, 230, 211, 0.50)',
                'btnText'  => '#1D263D',
            ],
            str_contains($routeKey, 'informe') => [
                'subtitle' => 'Redacta y publica el informe radiológico o adendum vinculado al examen ejecutado.',
                'icon'     => 'bi-file-earmark-medical',
                'color'    => '#8297D1',
                'soft'     => 'rgba(130, 151, 209, 0.22)',
                'border'   => 'rgba(130, 151, 209, 0.55)',
                'btnText'  => '#1D263D',
            ],
            default => [
                'subtitle' => 'Complete todos los campos requeridos antes de confirmar la ejecución.',
                'icon'     => 'bi-sliders2',
                'color'    => '#6BC9C6',
                'soft'     => 'rgba(107, 201, 198, 0.22)',
                'border'   => 'rgba(107, 201, 198, 0.55)',
                'btnText'  => '#1D263D',
            ],
        };

        $fieldIcon = function (string $name, string $type): string {
            $k = strtolower($name);
            return match (true) {
                str_contains($k, 'paciente')                                    => 'bi-person-vcard',
                str_contains($k, 'sucursal') || str_contains($k, 'clinica')     => 'bi-building',
                str_contains($k, 'fecha') || $type === 'date'                   => 'bi-calendar-event',
                str_contains($k, 'hora') || $type === 'time'                    => 'bi-clock',
                str_contains($k, 'orden')                                       => 'bi-file-earmark-text',
                str_contains($k, 'prestacion')                                  => 'bi-list-check',
                str_contains($k, 'presupuesto')                                 => 'bi-receipt',
                str_contains($k, 'monto') || str_contains($k, 'precio')         => 'bi-cash-stack',
                str_contains($k, 'metodo') || str_contains($k, 'pago')          => 'bi-wallet2',
                str_contains($k, 'examen')                                      => 'bi-activity',
                str_contains($k, 'estado')                                      => 'bi-toggles',
                str_contains($k, 'urgencia')                                    => 'bi-exclamation-triangle',
                str_contains($k, 'titulo')                                      => 'bi-type-h1',
                str_contains($k, 'adendum')                                     => 'bi-file-earmark-plus',
                str_contains($k, 'cuerpo') || str_contains($k, 'informe') || $type === 'textarea' => 'bi-card-text',
                default                                                         => 'bi-input-cursor-text',
            };
        };

        /* ============ 2) CÁLCULO DE GRILLA ÓPTIMA (0 huecos vacíos) ============ */
        $fieldsCollection = collect($fields);
        $compactCount = $fieldsCollection->filter(function ($f) {
            $t = $f['type'] ?? 'text';
            $m = !empty($f['multiple']);
            return $t !== 'textarea' && !($t === 'select' && $m);
        })->count();

        $promoteFirstCompact = ($compactCount % 2) === 1;
        $compactIndex = 0;
        $today = \Illuminate\Support\Str::ucfirst(now()->locale('es')->translatedFormat('l j \d\e F'));
    @endphp

    <style>
        /* Oculta cualquier encabezado vacío que renderice el layout padre */
        h1:empty {
            display: none !important;
        }

        /* =====================================================================
           GESTOR BENTO · Base oscura mate + tipografía grande y legible
           ===================================================================== */
        .vv-gestor {
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
            --g-border: {{ $meta['border'] }};
            --g-btn-text: {{ $meta['btnText'] }};
            color: var(--vv-text);
            width: 100%;
        }

        [data-bs-theme="light"] .vv-gestor {
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
        .vv-g-topbar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 1.4rem;
            margin-bottom: 1.75rem;
        }

        .vv-g-hero-ico {
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

        .vv-g-title {
            margin: 0 0 0.35rem;
            font-size: 2.65rem;
            font-weight: 800;
            letter-spacing: -0.025em;
            line-height: 1.12;
            color: var(--vv-text);
        }

        .vv-g-subtitle {
            margin: 0;
            font-size: 1.15rem;
            color: var(--vv-muted);
            line-height: 1.45;
        }

        .vv-g-controls {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.75rem;
        }

        .vv-g-pill {
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

        a.vv-g-pill:hover {
            background: var(--vv-navy);
            border-color: var(--vv-blue);
            color: var(--vv-cream);
        }

        /* ---------- Contenedor Principal Bento ---------- */
        .vv-g-card {
            position: relative;
            background: var(--vv-card);
            border: 1px solid var(--vv-border);
            border-radius: var(--vv-radius);
            box-shadow: var(--vv-shadow);
            overflow: hidden;
            width: 100%;
        }

        .vv-g-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--g-accent);
        }

        .vv-g-body {
            padding: 1.75rem 1.85rem;
        }

        /* ---------- Grilla de Campos Óptima ---------- */
        .vv-fields-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1.35rem;
        }

        @media (max-width: 767.98px) {
            .vv-fields-grid {
                grid-template-columns: 1fr;
            }
        }

        .vv-field-tile {
            background: var(--vv-card-elev);
            border: 1px solid var(--vv-border);
            border-radius: 1rem;
            padding: 1.45rem 1.55rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: border-color 0.16s ease, transform 0.16s ease;
        }

        .vv-field-tile:hover {
            border-color: var(--vv-border-hover);
        }

        .vv-field-tile:focus-within {
            border-color: var(--g-accent);
            box-shadow: 0 0 0 3px var(--g-soft);
        }

        .vv-field-tile.is-full {
            grid-column: 1 / -1;
        }

        .vv-field-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.65rem;
            margin-bottom: 0.95rem;
        }

        .vv-field-label {
            display: inline-flex;
            align-items: center;
            gap: 0.72rem;
            margin: 0;
            font-size: 1.2rem;
            font-weight: 700;
            letter-spacing: 0.01em;
            color: var(--vv-text);
            cursor: pointer;
        }

        .vv-field-ico {
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

        .vv-req-tag {
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 0.25rem 0.72rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.07);
            color: var(--vv-muted);
        }

        /* ---------- Inputs y Selects Grandes y Notorios ---------- */
        .vv-gestor .form-control,
        .vv-gestor .form-select {
            background-color: var(--vv-input-bg);
            border: 1px solid var(--vv-border-hover);
            color: var(--vv-text);
            border-radius: 0.75rem;
            font-size: 1.2rem !important;
            font-weight: 500;
            padding: 0.92rem 1.15rem;
            min-height: 3.65rem;
            color-scheme: dark;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }

        [data-bs-theme="light"] .vv-gestor .form-control,
        [data-bs-theme="light"] .vv-gestor .form-select {
            color-scheme: light;
        }

        .vv-gestor .form-control:focus,
        .vv-gestor .form-select:focus {
            background-color: var(--vv-input-bg);
            border-color: var(--g-accent);
            box-shadow: 0 0 0 0.22rem var(--g-soft);
            color: var(--vv-text);
        }

        .vv-gestor textarea.form-control {
            min-height: 165px;
            line-height: 1.6;
        }

        /* ---------- Selector Múltiple Interactivo (Checkboxes + Buscador + Total) ---------- */
        .vv-multi-wrap {
            background: var(--vv-input-bg);
            border: 1px solid var(--vv-border-hover);
            border-radius: 0.85rem;
            overflow: hidden;
        }

        .vv-multi-bar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 0.85rem;
            padding: 0.9rem 1.15rem;
            border-bottom: 1px solid var(--vv-border);
            background: rgba(255, 255, 255, 0.02);
        }

        .vv-multi-search {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            flex: 1 1 260px;
            font-size: 1.12rem;
        }

        .vv-multi-search input {
            width: 100%;
            border: 0;
            background: transparent;
            color: var(--vv-text);
            font-size: 1.12rem;
            outline: none;
        }

        .vv-multi-bar .btn {
            font-size: 0.96rem;
            font-weight: 600;
            padding: 0.42rem 0.9rem;
        }

        .vv-multi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(345px, 1fr));
            gap: 0.75rem;
            max-height: 380px;
            overflow-y: auto;
            padding: 1rem;
        }

        .vv-check-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.85rem;
            padding: 0.95rem 1.15rem;
            border-radius: 0.78rem;
            background: var(--vv-card);
            border: 1px solid var(--vv-border);
            cursor: pointer;
            margin: 0;
            user-select: none;
            transition: transform 0.12s ease, border-color 0.14s ease, background-color 0.14s ease;
        }

        .vv-check-card:hover {
            border-color: var(--vv-border-hover);
            background: var(--vv-card-elev);
        }

        .vv-check-card.is-checked {
            background: var(--vv-navy);
            border-color: var(--g-accent);
            color: var(--vv-cream);
        }

        .vv-check-card .form-check-input {
            width: 1.4rem;
            height: 1.4rem;
            margin: 0;
            cursor: pointer;
            flex-shrink: 0;
        }

        .vv-check-card .form-check-input:checked {
            background-color: var(--g-accent);
            border-color: var(--g-accent);
        }

        .vv-check-label {
            font-size: 1.12rem;
            font-weight: 600;
            line-height: 1.35;
        }

        .vv-price-tag {
            font-size: 1.05rem;
            font-weight: 800;
            font-variant-numeric: tabular-nums;
            padding: 0.35rem 0.82rem;
            border-radius: 0.6rem;
            background: var(--vv-mint);
            color: var(--vv-navy-deep);
            white-space: nowrap;
        }

        .vv-multi-foot {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 0.85rem;
            padding: 1.05rem 1.25rem;
            border-top: 1px solid var(--vv-border);
            background: rgba(255, 255, 255, 0.025);
            font-size: 1.08rem;
        }

        .vv-total-figure {
            font-size: 1.75rem;
            font-weight: 800;
            color: var(--vv-mint);
            font-variant-numeric: tabular-nums;
            line-height: 1;
        }

        /* ---------- Footer de Acciones ---------- */
        .vv-g-footer {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 1.1rem;
            padding: 1.4rem 1.85rem;
            border-top: 1px solid var(--vv-border);
            background: rgba(0, 0, 0, 0.16);
        }

        .vv-g-footer-note {
            font-size: 1.02rem;
            color: var(--vv-muted);
        }

        .vv-btn-exec {
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
            box-shadow: 0 10px 22px -10px var(--g-accent);
            transition: transform 0.15s ease, filter 0.15s ease;
        }

        .vv-btn-exec:hover {
            transform: translateY(-2px);
            filter: brightness(1.07);
            color: var(--g-btn-text);
        }

        .vv-btn-cancel {
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

        .vv-btn-cancel:hover {
            background: var(--vv-navy-deep);
            border-color: var(--vv-cream);
            color: var(--vv-cream);
        }
    </style>

    <div class="vv-gestor">

        {{-- ============ CABECERA PRINCIPAL ÚNICA Y PROTAGONISTA ============ --}}
        <div class="vv-g-topbar">
            <div class="d-flex align-items-center gap-3">
                <span class="vv-g-hero-ico">
                    <i class="bi {{ $meta['icon'] }}"></i>
                </span>
                <div>
                    <h1 class="vv-g-title">{{ $title }}</h1>
                    <p class="vv-g-subtitle">{{ $meta['subtitle'] }}</p>
                </div>
            </div>

            <div class="vv-g-controls">
                <span class="vv-g-pill">
                    <i class="bi bi-person-badge" style="color: var(--g-accent);"></i>
                    {{ $roleName }}
                </span>
                <span class="vv-g-pill">
                    <i class="bi bi-calendar3" style="color: var(--g-accent);"></i>
                    {{ $today }}
                </span>
                <a href="{{ route('dashboard') }}" class="vv-g-pill text-decoration-none">
                    <i class="bi bi-arrow-left"></i>
                    Inicio
                </a>
            </div>
        </div>

        {{-- ============ TARJETA DEL FORMULARIO ============ --}}
        <section class="vv-g-card">
            <form method="POST" action="{{ route($routeName) }}">
                @csrf

                <div class="vv-g-body">
                    <div class="vv-fields-grid">
                        @foreach($fields as $field)
                            @php
                                $name     = $field['name'];
                                $type     = $field['type'] ?? 'text';
                                $multiple = !empty($field['multiple']);
                                $selected = old($name, $field['value'] ?? ($multiple ? [] : ''));

                                $isNaturalFull = ($type === 'textarea') || ($type === 'select' && $multiple);
                                if ($isNaturalFull) {
                                    $isFull = true;
                                } else {
                                    $isFull = ($compactIndex === 0 && $promoteFirstCompact);
                                    $compactIndex++;
                                }

                                $ico = $fieldIcon($name, $type);
                            @endphp

                            <div class="vv-field-tile {{ $isFull ? 'is-full' : '' }}">
                                <div class="vv-field-head">
                                    <label class="vv-field-label" for="{{ $name }}">
                                        <span class="vv-field-ico">
                                            <i class="bi {{ $ico }}"></i>
                                        </span>
                                        <span>{{ $field['label'] }}</span>
                                    </label>
                                    <span class="vv-req-tag">Obligatorio</span>
                                </div>

                                @if($type === 'select' && $multiple)
                                    @php
                                        $selectedIds = array_map('strval', (array) $selected);
                                        $optionsList = $field['options'] ?? [];
                                        $hasPrices   = false;
                                    @endphp

                                    <div class="vv-multi-wrap @error($name) border-danger @enderror" data-vv-multi>
                                        <div class="vv-multi-bar">
                                            <div class="vv-multi-search">
                                                <i class="bi bi-search" style="color: var(--vv-muted);"></i>
                                                <input type="text" placeholder="Buscar prestación por nombre..." data-vv-search>
                                            </div>
                                            <div class="d-flex align-items-center gap-2">
                                                <button type="button" class="btn btn-outline-secondary" data-vv-select-all>
                                                    Marcar todo
                                                </button>
                                                <button type="button" class="btn btn-outline-secondary d-none" data-vv-clear>
                                                    Limpiar
                                                </button>
                                            </div>
                                        </div>

                                        <div class="vv-multi-grid">
                                            @forelse($optionsList as $value => $label)
                                                @php
                                                    $isChecked = in_array((string) $value, $selectedIds, true);
                                                    $cleanText = (string) $label;
                                                    $priceBadge = null;
                                                    $priceVal = 0;

                                                    if (preg_match('/^(.*?)(?:\s*[—\-|]\s*|\s+)(\$\s*[\d\.]+)\s*$/u', $cleanText, $m)) {
                                                        $cleanText  = trim($m[1]);
                                                        $priceBadge = trim($m[2]);
                                                        $priceVal   = (int) preg_replace('/[^\d]/', '', $priceBadge);
                                                        $hasPrices  = true;
                                                    }
                                                @endphp

                                                <label class="vv-check-card {{ $isChecked ? 'is-checked' : '' }}"
                                                       data-vv-item
                                                       data-search="{{ mb_strtolower((string) $label) }}"
                                                       data-price="{{ $priceVal }}">
                                                    <div class="d-flex align-items-center gap-3 min-w-0">
                                                        <input type="checkbox"
                                                               class="form-check-input"
                                                               name="{{ $name }}[]"
                                                               value="{{ $value }}"
                                                               @checked($isChecked)>
                                                        <span class="vv-check-label">{{ $cleanText }}</span>
                                                    </div>
                                                    @if($priceBadge)
                                                        <span class="vv-price-tag">{{ $priceBadge }}</span>
                                                    @endif
                                                </label>
                                            @empty
                                                <div class="text-center py-4 w-100" style="grid-column: 1 / -1; color: var(--vv-muted); font-size: 1.1rem;">
                                                    <i class="bi bi-inbox d-block fs-2 mb-1"></i>
                                                    {{ $field['emptyMessage'] ?? 'No hay opciones disponibles en el catálogo.' }}
                                                </div>
                                            @endforelse

                                            <div class="text-center py-3 d-none" style="grid-column: 1 / -1; color: var(--vv-muted); font-size: 1.08rem;" data-vv-empty>
                                                No se encontraron coincidencias para tu búsqueda.
                                            </div>
                                        </div>

                                        <div class="vv-multi-foot">
                                            <div style="color: var(--vv-muted);">
                                                Seleccionados: <strong class="text-white fs-5" data-vv-count>0</strong> de {{ count($optionsList) }}
                                            </div>

                                            <div class="d-flex align-items-center gap-2 {{ $hasPrices ? '' : 'd-none' }}">
                                                <span style="color: var(--vv-muted);">Total estimado:</span>
                                                <span class="vv-total-figure" data-vv-total>$ 0</span>
                                            </div>
                                        </div>
                                    </div>

                                @elseif($type === 'select')
                                    <select class="form-select @error($name) is-invalid @enderror"
                                            id="{{ $name }}"
                                            name="{{ $name }}"
                                            @if(!empty($field['reloadQuery'])) data-reload-query="{{ $name }}" @endif
                                            required>
                                        <option value="">Selecciona una opción</option>
                                        @foreach(($field['options'] ?? []) as $value => $label)
                                            <option value="{{ $value }}" @selected((string) $selected === (string) $value)>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>

                                @elseif($type === 'textarea')
                                    <textarea class="form-control @error($name) is-invalid @enderror"
                                              id="{{ $name }}"
                                              name="{{ $name }}"
                                              maxlength="1028"
                                              placeholder="Escribe el detalle aquí..."
                                              required>{{ old($name) }}</textarea>

                                @else
                                    <input class="form-control @error($name) is-invalid @enderror"
                                           id="{{ $name }}"
                                           name="{{ $name }}"
                                           type="{{ $type }}"
                                           value="{{ old($name) }}"
                                           @if(isset($field['step'])) step="{{ $field['step'] }}" @endif
                                           required>
                                @endif

                                @error($name)
                                    <div class="invalid-feedback d-block mt-2 fw-semibold" style="font-size: 1rem;">{{ $message }}</div>
                                @enderror
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="vv-g-footer">
                    <div class="d-flex align-items-center gap-2 vv-g-footer-note">
                        <i class="bi bi-shield-check" style="color: var(--g-accent); font-size: 1.25rem;"></i>
                        <span>Verifica los datos seleccionados antes de confirmar la operación.</span>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <a class="vv-btn-cancel" href="{{ route('dashboard') }}">
                            Cancelar
                        </a>
                        <button class="vv-btn-exec" type="submit">
                            <i class="bi bi-play-fill"></i>
                            Ejecutar gestor
                        </button>
                    </div>
                </div>
            </form>
        </section>
    </div>

    <script>
        document.title = @json($title);

        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-reload-query]').forEach((select) => {
                select.addEventListener('change', function () {
                    const url = new URL(window.location.href);
                    const queryKey = this.dataset.reloadQuery;

                    if (this.value) {
                        url.searchParams.set(queryKey, this.value);
                    } else {
                        url.searchParams.delete(queryKey);
                    }

                    window.location.assign(url.toString());
                });
            });

            const formatCLP = (n) => '$ ' + n.toLocaleString('es-CL');

            document.querySelectorAll('[data-vv-multi]').forEach((box) => {
                const searchInput = box.querySelector('[data-vv-search]');
                const items       = Array.from(box.querySelectorAll('[data-vv-item]'));
                const emptyMsg    = box.querySelector('[data-vv-empty]');
                const countEl     = box.querySelector('[data-vv-count]');
                const totalEl     = box.querySelector('[data-vv-total]');
                const clearBtn    = box.querySelector('[data-vv-clear]');
                const selectAll   = box.querySelector('[data-vv-select-all]');

                const sync = () => {
                    let count = 0;
                    let sum   = 0;

                    items.forEach((item) => {
                        const cb = item.querySelector('input[type="checkbox"]');
                        const on = cb && cb.checked;
                        item.classList.toggle('is-checked', on);
                        if (on) {
                            count++;
                            sum += parseInt(item.dataset.price || '0', 10);
                        }
                    });

                    if (countEl) countEl.textContent = count;
                    if (totalEl) totalEl.textContent = formatCLP(sum);
                    if (clearBtn) clearBtn.classList.toggle('d-none', count === 0);
                };

                items.forEach((item) => {
                    const cb = item.querySelector('input[type="checkbox"]');
                    if (cb) cb.addEventListener('change', sync);
                });

                if (searchInput) {
                    searchInput.addEventListener('input', function () {
                        const q = this.value.trim().toLowerCase();
                        let visible = 0;
                        items.forEach((item) => {
                            const ok = (item.dataset.search || '').includes(q);
                            item.classList.toggle('d-none', !ok);
                            if (ok) visible++;
                        });
                        if (emptyMsg) emptyMsg.classList.toggle('d-none', visible > 0 || items.length === 0);
                    });
                }

                if (selectAll) {
                    selectAll.addEventListener('click', () => {
                        items.forEach((item) => {
                            if (!item.classList.contains('d-none')) {
                                const cb = item.querySelector('input[type="checkbox"]');
                                if (cb) cb.checked = true;
                            }
                        });
                        sync();
                    });
                }

                if (clearBtn) {
                    clearBtn.addEventListener('click', () => {
                        items.forEach((item) => {
                            const cb = item.querySelector('input[type="checkbox"]');
                            if (cb) cb.checked = false;
                        });
                        sync();
                    });
                }

                sync();
            });
        });
    </script>
@endsection