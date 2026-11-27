@php
    $title     = $title ?? 'Reporte';
    $columns   = $columns ?? [];
    $rows      = $rows ?? collect();
    $statusLabels = $statusLabels ?? [];
    $totalRows = is_countable($rows) ? count($rows) : (method_exists($rows, 'count') ? $rows->count() : 0);
@endphp

@extends('layouts.app')

@section('title', $title)

@section('content')
    @php
        $user     = Auth::user();
        $roleName = $user?->rol?->NOMBRE_ROL ?? 'OPERADOR';
        $needle   = \Illuminate\Support\Str::lower(\Illuminate\Support\Str::ascii((string) $title));

        /* ============ IDENTIDAD VISUAL SEGÚN EL REPORTE ============ */
        $meta = match (true) {
            str_contains($needle, 'ingreso') || str_contains($needle, 'pago') || str_contains($needle, 'recaud') => [
                'icon'    => 'bi-cash-stack',
                'color'   => '#6BC9C6',
                'soft'    => 'rgba(107, 201, 198, 0.22)',
                'btnText' => '#1D263D',
            ],
            str_contains($needle, 'orden') || str_contains($needle, 'sucursal') => [
                'icon'    => 'bi-building',
                'color'   => '#8297D1',
                'soft'    => 'rgba(130, 151, 209, 0.22)',
                'btnText' => '#1D263D',
            ],
            str_contains($needle, 'examen') || str_contains($needle, 'urgencia') => [
                'icon'    => 'bi-activity',
                'color'   => '#F5E6D3',
                'soft'    => 'rgba(245, 230, 211, 0.20)',
                'btnText' => '#1D263D',
            ],
            default => [
                'icon'    => 'bi-bar-chart-line',
                'color'   => '#6BC9C6',
                'soft'    => 'rgba(107, 201, 198, 0.22)',
                'btnText' => '#1D263D',
            ],
        };

        $today = \Illuminate\Support\Str::ucfirst(now()->locale('es')->translatedFormat('l j \d\e F'));
    @endphp

    <style>
        /* =====================================================================
           REPORTES BENTO · Base oscura mate + brillo celeste + letra grande
           ===================================================================== */
        .vv-report {
            --vv-card: #1b1d21;
            --vv-card-elev: #23262c;
            --vv-input-bg: #131519;
            --vv-border: rgba(107, 201, 198, 0.16);
            --vv-border-hover: rgba(107, 201, 198, 0.45);
            --vv-text: #f3f4f6;
            --vv-muted: #9aa2b1;

            --vv-cream: #F5E6D3;
            --vv-navy: #28334F;
            --vv-navy-deep: #1D263D;
            --vv-blue: #8297D1;
            --vv-sage: #9FB2B0;
            --vv-mint: #6BC9C6;
            --vv-mint-bright: #88E5E2;

            --vv-glow: 0 0 22px rgba(107, 201, 198, 0.55), 0 0 44px rgba(107, 201, 198, 0.22);
            --vv-glow-soft: 0 10px 28px -8px rgba(107, 201, 198, 0.35), 0 0 18px rgba(107, 201, 198, 0.16);

            --vv-radius: 1.15rem;
            --vv-shadow: 0 12px 28px -14px rgba(0, 0, 0, 0.75);
            --g-accent: {{ $meta['color'] }};
            --g-soft: {{ $meta['soft'] }};
            --g-btn-text: {{ $meta['btnText'] }};
            color: var(--vv-text);
            width: 100%;
        }

        [data-bs-theme="light"] .vv-report {
            --vv-card: #ffffff;
            --vv-card-elev: #f1f5f9;
            --vv-input-bg: #f8fafc;
            --vv-border: rgba(15, 23, 42, 0.10);
            --vv-border-hover: rgba(107, 201, 198, 0.6);
            --vv-text: #0f172a;
            --vv-muted: #64748b;
            --vv-shadow: 0 10px 26px -16px rgba(15, 23, 42, 0.2);
        }

        /* ---------- Cabecera Principal Protagonista ---------- */
        .vv-rp-topbar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 1.4rem;
            margin-bottom: 1.75rem;
        }

        .vv-rp-hero-ico {
            width: 4.35rem;
            height: 4.35rem;
            border-radius: 1.1rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 2.1rem;
            background: linear-gradient(135deg, var(--vv-mint-bright) 0%, var(--vv-mint) 100%);
            color: var(--vv-navy-deep);
            box-shadow: var(--vv-glow);
            flex-shrink: 0;
        }

        .vv-rp-title {
            margin: 0;
            font-size: 2.55rem;
            font-weight: 800;
            letter-spacing: -0.025em;
            line-height: 1.12;
            color: var(--vv-text);
            text-shadow: 0 0 28px rgba(107, 201, 198, 0.18);
        }

        .vv-rp-controls {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.75rem;
        }

        .vv-rp-pill {
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
            box-shadow: 0 0 16px rgba(107, 201, 198, 0.1);
            cursor: pointer;
            transition: border-color 0.15s ease, background-color 0.15s ease, box-shadow 0.15s ease;
        }

        a.vv-rp-pill:hover,
        button.vv-rp-pill:hover {
            background: var(--vv-navy);
            border-color: var(--vv-mint);
            box-shadow: var(--vv-glow-soft);
            color: var(--vv-cream);
        }

        /* ---------- Tarjeta Principal Bento ---------- */
        .vv-rp-card {
            position: relative;
            background: var(--vv-card);
            border: 1px solid var(--vv-border);
            border-radius: var(--vv-radius);
            box-shadow: var(--vv-shadow), 0 0 26px -12px rgba(107, 201, 198, 0.22);
            overflow: hidden;
            width: 100%;
        }

        .vv-rp-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--vv-mint-bright), var(--vv-blue), var(--vv-mint));
            box-shadow: 0 2px 16px rgba(107, 201, 198, 0.65);
        }

        .vv-rp-card-head {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 0.9rem;
            padding: 1.25rem 1.65rem;
            border-bottom: 1px solid var(--vv-border);
            background: rgba(255, 255, 255, 0.015);
        }

        .vv-rp-search {
            display: inline-flex;
            align-items: center;
            gap: 0.65rem;
            background: var(--vv-input-bg);
            border: 1px solid var(--vv-border-hover);
            border-radius: 0.78rem;
            padding: 0.68rem 1.05rem;
            min-width: 300px;
            max-width: 440px;
            width: 100%;
            font-size: 1.06rem;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }

        .vv-rp-search:focus-within {
            border-color: var(--vv-mint);
            box-shadow: 0 0 0 3px rgba(107, 201, 198, 0.22), 0 0 18px rgba(107, 201, 198, 0.25);
        }

        .vv-rp-search input {
            width: 100%;
            border: 0;
            background: transparent;
            color: var(--vv-text);
            font-size: 1.06rem;
            outline: none;
        }

        .vv-rp-count-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.55rem 1.1rem;
            border-radius: 0.78rem;
            font-size: 1.02rem;
            font-weight: 700;
            background: var(--vv-navy);
            border: 1px solid rgba(107, 201, 198, 0.55);
            box-shadow: 0 0 16px rgba(107, 201, 198, 0.22);
            color: var(--vv-cream);
        }

        /* ---------- Tabla de Reporte ---------- */
        .vv-rp-table-wrap {
            width: 100%;
            overflow-x: auto;
        }

        .vv-rp-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
            font-size: 19px !important;
            font-variant-numeric: tabular-nums;
        }

        .vv-rp-table thead th {
            background: var(--vv-card-elev);
            color: var(--vv-muted);
            font-size: 15px !important;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            padding: 1.25rem 1.55rem;
            border-bottom: 1px solid var(--vv-border);
            white-space: nowrap;
            text-align: left;
        }

        .vv-rp-table tbody tr {
            border-bottom: 1px solid var(--vv-border);
            transition: background-color 0.14s ease;
        }

        .vv-rp-table tbody tr:last-child {
            border-bottom: 0;
        }

        .vv-rp-table tbody tr:hover {
            background: rgba(107, 201, 198, 0.08);
        }

        .vv-rp-table tbody td {
            font-size: 19px !important;
            font-weight: 500;
            padding: 1.3rem 1.55rem;
            color: var(--vv-text);
            vertical-align: middle;
            white-space: nowrap;
        }

        .vv-id-chip {
            display: inline-flex;
            align-items: center;
            padding: 0.35rem 0.85rem;
            border-radius: 0.62rem;
            font-size: 17px !important;
            font-weight: 800;
            background: var(--vv-navy);
            border: 1px solid rgba(107, 201, 198, 0.5);
            box-shadow: 0 0 12px rgba(107, 201, 198, 0.2);
            color: var(--vv-cream);
        }

        @media print {
            .app-sidebar, .app-header, .app-footer, .vv-rp-controls, .vv-rp-card-head {
                display: none !important;
            }
            .vv-report, .vv-rp-card {
                box-shadow: none !important;
                border: 1px solid #ccc !important;
                background: #fff !important;
                color: #000 !important;
            }
            .vv-rp-table th, .vv-rp-table td {
                color: #000 !important;
                font-size: 13px !important;
                padding: 8px 10px !important;
            }
        }
    </style>

    <div class="vv-report">

        {{-- ============ CABECERA PRINCIPAL PROTAGONISTA ============ --}}
        <div class="vv-rp-topbar">
            <div class="d-flex align-items-center gap-3">
                <span class="vv-rp-hero-ico">
                    <i class="bi {{ $meta['icon'] }}"></i>
                </span>
                <div>
                    <h1 class="vv-rp-title">{{ $title }}</h1>
                </div>
            </div>

            <div class="vv-rp-controls">
                <span class="vv-rp-pill">
                    <i class="bi bi-person-badge" style="color: var(--vv-mint);"></i>
                    {{ $roleName }}
                </span>
                <span class="vv-rp-pill">
                    <i class="bi bi-calendar3" style="color: var(--vv-mint);"></i>
                    {{ $today }}
                </span>
                <button type="button" class="vv-rp-pill" onclick="window.print()">
                    <i class="bi bi-printer" style="color: var(--vv-mint);"></i>
                    Imprimir
                </button>
                <a href="{{ route('dashboard') }}" class="vv-rp-pill text-decoration-none">
                    <i class="bi bi-arrow-left"></i>
                    Inicio
                </a>
            </div>
        </div>

        {{-- ============ TARJETA DE RESULTADOS DEL REPORTE ============ --}}
        <section class="vv-rp-card">
            <div class="vv-rp-card-head">
                <div class="vv-rp-search">
                    <i class="bi bi-search" style="color: var(--vv-mint);"></i>
                    <input type="text" placeholder="Filtrar resultados en pantalla..." data-vv-report-search>
                </div>

                <span class="vv-rp-count-badge">
                    <i class="bi bi-bar-chart-steps" style="color: var(--vv-mint);"></i>
                    <span data-vv-report-count>{{ $totalRows }}</span>
                    {{ $totalRows === 1 ? 'fila' : 'filas' }}
                </span>
            </div>

            <div class="vv-rp-table-wrap">
                <table class="vv-rp-table">
                    <thead>
                        <tr>
                            @foreach($columns as $label)
                                <th>{{ $label }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $row)
                            @php
                                $searchBlob = '';
                                foreach ($columns as $cKey => $cLabel) {
                                    $val = is_object($row) ? ($row->{$cKey} ?? '') : ($row[$cKey] ?? '');
                                    $searchBlob .= ' ' . (string) $val;
                                    if (strtoupper((string) $cKey) === 'ESTADO' && $val !== '') {
                                        $searchBlob .= ' ' . (string) ($statusLabels[(string) $val] ?? '');
                                    }
                                }
                            @endphp
                            <tr data-vv-report-row data-search="{{ mb_strtolower(trim($searchBlob)) }}">
                                @foreach($columns as $key => $label)
                                    @php
                                        $cellVal = is_object($row) ? ($row->{$key} ?? null) : ($row[$key] ?? null);
                                        $isIdCol = strtoupper((string) $key) === 'ID';
                                        $displayVal = strtoupper((string) $key) === 'ESTADO' && $cellVal !== null
                                            ? ($statusLabels[(string) $cellVal] ?? $cellVal)
                                            : $cellVal;
                                    @endphp
                                    <td>
                                        @if($isIdCol && $cellVal !== null && $cellVal !== '')
                                            <span class="vv-id-chip">#{{ $cellVal }}</span>
                                        @else
                                            {{ $displayVal ?? '—' }}
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td class="text-center py-5" style="color: var(--vv-muted); font-size: 18px;" colspan="{{ max(1, count($columns)) }}">
                                    <i class="bi bi-clipboard-data d-block fs-2 mb-2" style="color: var(--vv-mint);"></i>
                                    No hay datos para el reporte.
                                </td>
                            </tr>
                        @endforelse

                        <tr class="d-none" data-vv-report-empty>
                            <td class="text-center py-4" style="color: var(--vv-muted); font-size: 18px;" colspan="{{ max(1, count($columns)) }}">
                                No se encontraron coincidencias para tu búsqueda.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.querySelector('[data-vv-report-search]');
            const rows        = Array.from(document.querySelectorAll('[data-vv-report-row]'));
            const emptyRow    = document.querySelector('[data-vv-report-empty]');
            const countEl     = document.querySelector('[data-vv-report-count]');

            if (!searchInput || rows.length === 0) return;

            searchInput.addEventListener('input', function () {
                const q = this.value.trim().toLowerCase();
                let visible = 0;

                rows.forEach((tr) => {
                    const match = (tr.dataset.search || '').includes(q);
                    tr.classList.toggle('d-none', !match);
                    if (match) visible++;
                });

                if (countEl) countEl.textContent = visible;
                if (emptyRow) emptyRow.classList.toggle('d-none', visible > 0);
            });
        });
    </script>
@endsection