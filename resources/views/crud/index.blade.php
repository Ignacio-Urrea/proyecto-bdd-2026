@php
    $title     = $title ?? 'Listado';
    $routeName = $routeName ?? '';
    $rows      = $records ?? $rows ?? $items ?? collect();
    $rawCols   = $columns ?? [];

    /* Normaliza $columns tanto si viene como ['COL' => 'Label'] o [['name'/'key' => ..., 'label' => ...]] */
    $normalizedCols = [];
    foreach ($rawCols as $k => $v) {
        if (is_array($v)) {
            $colKey = $v['key'] ?? $v['name'] ?? $k;
            $normalizedCols[$colKey] = $v['label'] ?? $colKey;
        } else {
            $normalizedCols[$k] = $v;
        }
    }

    if (empty($normalizedCols) && count($rows) > 0) {
        $firstItem = is_array($rows) ? reset($rows) : $rows->first();
        $firstArr  = is_object($firstItem) && method_exists($firstItem, 'toArray')
            ? $firstItem->toArray()
            : (array) $firstItem;
        foreach (array_keys($firstArr) as $attrKey) {
            $normalizedCols[$attrKey] = $attrKey;
        }
    }

    $hasCreate  = $routeName && \Illuminate\Support\Facades\Route::has($routeName . '.create');
    $hasShow    = $routeName && \Illuminate\Support\Facades\Route::has($routeName . '.show');
    $hasEdit    = $routeName && \Illuminate\Support\Facades\Route::has($routeName . '.edit');
    $deleteViewRoute = collect(['delete', 'confirm-delete', 'confirmDelete'])
        ->map(fn ($suffix) => $routeName ? $routeName . '.' . $suffix : null)
        ->first(fn ($name) => $name && \Illuminate\Support\Facades\Route::has($name));

    $hasDestroy = $deleteViewRoute !== null
        || ($routeName && \Illuminate\Support\Facades\Route::has($routeName . '.destroy'));
    $hasActions = $hasShow || $hasEdit || $hasDestroy;

    $totalRows = method_exists($rows, 'total') ? $rows->total() : count($rows);
@endphp

@extends('layouts.app')

@section('title', $title)

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
                'icon'    => 'bi-table',
                'color'   => '#6BC9C6',
                'soft'    => 'rgba(107, 201, 198, 0.22)',
                'btnText' => '#1D263D',
            ],
        };

        $today = \Illuminate\Support\Str::ucfirst(now()->locale('es')->translatedFormat('l j \d\e F'));
    @endphp

    <style>
        /* =====================================================================
           CRUD INDEX BENTO · Base oscura mate + tipografía grande y legible
           ===================================================================== */
        .vv-crud-index {
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

            --vv-radius: 1.1rem;
            --vv-shadow: 0 12px 28px -14px rgba(0, 0, 0, 0.7);
            --g-accent: {{ $meta['color'] }};
            --g-soft: {{ $meta['soft'] }};
            --g-btn-text: {{ $meta['btnText'] }};
            color: var(--vv-text);
            width: 100%;
        }

        [data-bs-theme="light"] .vv-crud-index {
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
        .vv-ci-topbar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 1.35rem;
            margin-bottom: 1.65rem;
        }

        .vv-ci-hero-ico {
            width: 4.1rem;
            height: 4.1rem;
            border-radius: 1.05rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.95rem;
            background: var(--g-accent);
            color: var(--g-btn-text);
            box-shadow: 0 10px 24px -10px var(--g-accent);
            flex-shrink: 0;
        }

        .vv-ci-title {
            margin: 0;
            font-size: 2.45rem;
            font-weight: 800;
            letter-spacing: -0.025em;
            line-height: 1.12;
            color: var(--vv-text);
        }

        .vv-ci-controls {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.7rem;
        }

        .vv-ci-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.58rem 1.1rem;
            font-size: 0.98rem;
            font-weight: 600;
            color: var(--vv-text);
            background: var(--vv-card);
            border: 1px solid var(--vv-border);
            border-radius: 0.82rem;
            transition: border-color 0.15s ease, background-color 0.15s ease;
        }

        a.vv-ci-pill:hover {
            background: var(--vv-navy);
            border-color: var(--vv-blue);
            color: var(--vv-cream);
        }

        .vv-btn-new {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.68rem 1.4rem;
            border-radius: 0.82rem;
            border: 0;
            background: var(--g-accent);
            color: var(--g-btn-text);
            font-size: 1.05rem;
            font-weight: 800;
            text-decoration: none;
            box-shadow: 0 10px 22px -10px var(--g-accent);
            transition: transform 0.15s ease, filter 0.15s ease;
        }

        .vv-btn-new:hover {
            transform: translateY(-2px);
            filter: brightness(1.07);
            color: var(--g-btn-text);
        }

        /* ---------- Contenedor Principal Bento ---------- */
        .vv-ci-card {
            position: relative;
            background: var(--vv-card);
            border: 1px solid var(--vv-border);
            border-radius: var(--vv-radius);
            box-shadow: var(--vv-shadow);
            overflow: hidden;
            width: 100%;
        }

        .vv-ci-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--g-accent);
        }

        .vv-ci-card-head {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 0.9rem;
            padding: 1.2rem 1.65rem;
            border-bottom: 1px solid var(--vv-border);
            background: rgba(255, 255, 255, 0.015);
        }

        .vv-ci-search {
            display: inline-flex;
            align-items: center;
            gap: 0.65rem;
            background: var(--vv-input-bg);
            border: 1px solid var(--vv-border-hover);
            border-radius: 0.78rem;
            padding: 0.68rem 1.05rem;
            min-width: 300px;
            max-width: 430px;
            width: 100%;
            font-size: 1.06rem;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }

        .vv-ci-search:focus-within {
            border-color: var(--g-accent);
            box-shadow: 0 0 0 3px var(--g-soft);
        }

        .vv-ci-search input {
            width: 100%;
            border: 0;
            background: transparent;
            color: var(--vv-text);
            font-size: 1.06rem;
            outline: none;
        }

        .vv-count-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.52rem 1.05rem;
            border-radius: 0.75rem;
            font-size: 1rem;
            font-weight: 700;
            background: var(--vv-navy);
            border: 1px solid rgba(130, 151, 209, 0.45);
            color: var(--vv-cream);
        }

        /* ---------- Tabla Bento ---------- */
        .vv-table-wrap {
            width: 100%;
            overflow-x: auto;
        }

        .vv-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
            font-size: 19px !important;
        }

        .vv-table thead th {
            background: var(--vv-card-elev);
            color: var(--vv-muted);
            font-size: 15px !important;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--vv-border);
            white-space: nowrap;
            text-align: left;
        }

        .vv-table tbody tr {
            border-bottom: 1px solid var(--vv-border);
            transition: background-color 0.14s ease;
        }

        .vv-table tbody tr:last-child {
            border-bottom: 0;
        }

        .vv-table tbody tr:hover {
            background: rgba(130, 151, 209, 0.07);
        }

        .vv-table tbody td {
            font-size: 19px !important;
            font-weight: 500;
            padding: 1.3rem 1.5rem;
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
            font-variant-numeric: tabular-nums;
            background: var(--vv-navy);
            border: 1px solid rgba(130, 151, 209, 0.45);
            color: var(--vv-cream);
        }

        /* ---------- Botones de Acción por Fila ---------- */
        .vv-row-actions {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
        }

        .vv-act-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.42rem;
            padding: 0.55rem 1rem;
            border-radius: 0.65rem;
            font-size: 16px !important;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            transition: transform 0.12s ease, filter 0.12s ease, background-color 0.14s ease;
        }

        .vv-act-btn:hover {
            transform: translateY(-1px);
            filter: brightness(1.08);
        }

        .vv-act-btn--show {
            background: rgba(107, 201, 198, 0.16);
            border: 1px solid var(--vv-mint);
            color: var(--vv-mint);
        }

        .vv-act-btn--show:hover {
            background: var(--vv-mint);
            color: var(--vv-navy-deep);
        }

        .vv-act-btn--edit {
            background: rgba(130, 151, 209, 0.18);
            border: 1px solid var(--vv-blue);
            color: var(--vv-blue);
        }

        .vv-act-btn--edit:hover {
            background: var(--vv-blue);
            color: var(--vv-navy-deep);
        }

        .vv-act-btn--del {
            background: rgba(245, 230, 211, 0.12);
            border: 1px solid rgba(245, 230, 211, 0.55);
            color: var(--vv-cream);
        }

        .vv-act-btn--del:hover {
            background: var(--vv-cream);
            color: var(--vv-navy-deep);
        }

        /* ---------- Footer / Paginación ---------- */
        .vv-ci-footer {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 1.2rem 1.65rem;
            border-top: 1px solid var(--vv-border);
            background: rgba(0, 0, 0, 0.14);
            font-size: 16px;
            color: var(--vv-muted);
        }
    </style>

    <div class="vv-crud-index">

        {{-- ============ CABECERA PRINCIPAL ÚNICA Y PROTAGONISTA ============ --}}
        <div class="vv-ci-topbar">
            <div class="d-flex align-items-center gap-3">
                <span class="vv-ci-hero-ico">
                    <i class="bi {{ $meta['icon'] }}"></i>
                </span>
                <div>
                    <h1 class="vv-ci-title">{{ $title }}</h1>
                </div>
            </div>

            <div class="vv-ci-controls">
                <span class="vv-ci-pill">
                    <i class="bi bi-person-badge" style="color: var(--g-accent);"></i>
                    {{ $roleName }}
                </span>
                <span class="vv-ci-pill">
                    <i class="bi bi-calendar3" style="color: var(--g-accent);"></i>
                    {{ $today }}
                </span>
                <a href="{{ route('dashboard') }}" class="vv-ci-pill text-decoration-none">
                    <i class="bi bi-arrow-left"></i>
                    Inicio
                </a>
                @if($hasCreate)
                    <a href="{{ route($routeName . '.create') }}" class="vv-btn-new">
                        <i class="bi bi-plus-lg"></i>
                        Nuevo registro
                    </a>
                @endif
            </div>
        </div>

        {{-- ============ TARJETA DE LA TABLA ============ --}}
        <section class="vv-ci-card">
            <div class="vv-ci-card-head">
                <div class="vv-ci-search">
                    <i class="bi bi-search" style="color: var(--vv-muted);"></i>
                    <input type="text" placeholder="Filtrar registros en pantalla..." data-vv-table-search>
                </div>

                <span class="vv-count-badge">
                    <i class="bi bi-layers"></i>
                    <span data-vv-visible-count>{{ $totalRows }}</span>
                    {{ $totalRows === 1 ? 'registro' : 'registros' }}
                </span>
            </div>

            <div class="vv-table-wrap">
                <table class="vv-table">
                    <thead>
                        <tr>
                            @foreach($normalizedCols as $colKey => $colLabel)
                                <th>{{ $colLabel }}</th>
                            @endforeach
                            @if($hasActions)
                                <th class="text-end">Acciones</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody data-vv-table-body>
                        @forelse($rows as $row)
                            @php
                                $rowId = data_get($row, 'ID') ?? data_get($row, 'id');
                                $rowSearchBlob = '';
                                foreach ($normalizedCols as $cKey => $cLabel) {
                                    $rowSearchBlob .= ' ' . (string) data_get($row, $cKey, '');
                                }
                            @endphp
                            <tr data-vv-row data-search="{{ mb_strtolower(trim($rowSearchBlob)) }}">
                                @foreach($normalizedCols as $colKey => $colLabel)
                                    @php
                                        $cellVal = data_get($row, $colKey);
                                        $isIdCol = strtoupper((string) $colKey) === 'ID';
                                    @endphp
                                    <td>
                                        @if($isIdCol)
                                            <span class="vv-id-chip">#{{ $cellVal }}</span>
                                        @else
                                            {{ $cellVal ?? '—' }}
                                        @endif
                                    </td>
                                @endforeach

                                @if($hasActions && $rowId !== null)
                                    <td class="text-end">
                                        <div class="vv-row-actions">
                                            @if($hasShow)
                                                <a href="{{ route($routeName . '.show', $rowId) }}" class="vv-act-btn vv-act-btn--show">
                                                    <i class="bi bi-eye"></i>
                                                    Ver
                                                </a>
                                            @endif

                                            @if($hasEdit)
                                                <a href="{{ route($routeName . '.edit', $rowId) }}" class="vv-act-btn vv-act-btn--edit">
                                                    <i class="bi bi-pencil"></i>
                                                    Editar
                                                </a>
                                            @endif

                                            @if($hasDestroy)
                                                @if($deleteViewRoute)
                                                    <a href="{{ route($deleteViewRoute, $rowId) }}" class="vv-act-btn vv-act-btn--del">
                                                        <i class="bi bi-trash3"></i>
                                                        Eliminar
                                                    </a>
                                                @else
                                                    <form method="POST"
                                                          action="{{ route($routeName . '.destroy', $rowId) }}"
                                                          class="d-inline m-0"
                                                          onsubmit="return confirm('¿Seguro que deseas eliminar el registro #{{ $rowId }}?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="vv-act-btn vv-act-btn--del">
                                                            <i class="bi bi-trash3"></i>
                                                            Eliminar
                                                        </button>
                                                    </form>
                                                @endif
                                            @endif
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($normalizedCols) + ($hasActions ? 1 : 0) }}" class="text-center py-5" style="color: var(--vv-muted); font-size: 18px;">
                                    <i class="bi bi-inbox d-block fs-2 mb-2" style="color: var(--g-accent);"></i>
                                    No hay registros disponibles en {{ $title }}.
                                </td>
                            </tr>
                        @endforelse

                        <tr class="d-none" data-vv-empty-filter>
                            <td colspan="{{ count($normalizedCols) + ($hasActions ? 1 : 0) }}" class="text-center py-4" style="color: var(--vv-muted); font-size: 18px;">
                                No se encontraron coincidencias para tu búsqueda.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            @if(is_object($rows) && method_exists($rows, 'links') && $rows->hasPages())
                <div class="vv-ci-footer">
                    <span>Mostrando página {{ $rows->currentPage() }} de {{ $rows->lastPage() }}</span>
                    <div>{{ $rows->links() }}</div>
                </div>
            @endif
        </section>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.querySelector('[data-vv-table-search]');
            const rows        = Array.from(document.querySelectorAll('[data-vv-row]'));
            const emptyRow    = document.querySelector('[data-vv-empty-filter]');
            const countEl     = document.querySelector('[data-vv-visible-count]');

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