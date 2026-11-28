@php
    $title     = $title ?? 'Registro';
    $routeName = $routeName ?? '';
    $statusLabels = config('imaj.status_labels.'.$routeName, []);
    $pageTitle = 'Eliminar ' . $title;
    $backUrl   = $routeName && \Illuminate\Support\Facades\Route::has($routeName . '.index')
        ? route($routeName . '.index')
        : route('dashboard');
@endphp

@extends('layouts.app')

@section('title', $pageTitle)

@section('content')
    @php
        $user     = Auth::user();
        $roleName = $user?->rol?->NOMBRE_ROL ?? 'OPERADOR';

        $fieldIcon = function (string $name, string $type = 'text'): string {
            $k = strtolower($name);
            return match (true) {
                str_contains($k, 'rut') || str_contains($k, 'dni') || str_contains($k, 'run')             => 'bi-person-badge',
                str_contains($k, 'nombre') || str_contains($k, 'apellido') || str_contains($k, 'paciente') => 'bi-person',
                str_contains($k, 'correo') || str_contains($k, 'email') || $type === 'email'              => 'bi-envelope',
                str_contains($k, 'telefono') || str_contains($k, 'celular') || str_contains($k, 'fono')   => 'bi-telephone',
                str_contains($k, 'direccion') || str_contains($k, 'comuna') || str_contains($k, 'ciudad') => 'bi-geo-alt',
                str_contains($k, 'rol') || str_contains($k, 'perfil')                                     => 'bi-shield-check',
                str_contains($k, 'sucursal') || str_contains($k, 'clinica')                               => 'bi-building',
                str_contains($k, 'fecha') || str_contains($k, 'nacimiento') || $type === 'date'           => 'bi-calendar-event',
                str_contains($k, 'hora') || $type === 'time'                                              => 'bi-clock',
                str_contains($k, 'precio') || str_contains($k, 'monto') || str_contains($k, 'valor') || str_contains($k, 'arancel') => 'bi-cash-stack',
                str_contains($k, 'codigo') || str_contains($k, 'folio')                                   => 'bi-upc-scan',
                str_contains($k, 'estado') || str_contains($k, 'activo') || str_contains($k, 'habilitada') || str_contains($k, 'ejecutada') || str_contains($k, 'adendum') => 'bi-toggles',
                str_contains($k, 'prestacion') || str_contains($k, 'examen')                              => 'bi-activity',
                str_contains($k, 'descripcion') || str_contains($k, 'observacion') || str_contains($k, 'cuerpo') || str_contains($k, 'informe') || $type === 'textarea' => 'bi-card-text',
                default                                                                                   => 'bi-info-circle',
            };
        };

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
           CRUD DELETE BENTO · Base oscura mate + alerta cálida de confirmación
           ===================================================================== */
        .vv-crud-delete {
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
            --vv-danger: #f87171;
            --vv-danger-soft: rgba(248, 113, 113, 0.16);

            --vv-radius: 1.15rem;
            --vv-shadow: 0 12px 28px -14px rgba(0, 0, 0, 0.7);
            color: var(--vv-text);
            width: 100%;
        }

        [data-bs-theme="light"] .vv-crud-delete {
            --vv-card: #ffffff;
            --vv-card-elev: #f1f5f9;
            --vv-input-bg: #f8fafc;
            --vv-border: rgba(15, 23, 42, 0.10);
            --vv-border-hover: rgba(15, 23, 42, 0.22);
            --vv-text: #0f172a;
            --vv-muted: #64748b;
            --vv-shadow: 0 10px 26px -16px rgba(15, 23, 42, 0.2);
        }

        /* ---------- Cabecera Principal ---------- */
        .vv-cd-topbar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 1.4rem;
            margin-bottom: 1.75rem;
        }

        .vv-cd-hero-ico {
            width: 4.35rem;
            height: 4.35rem;
            border-radius: 1.1rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 2.1rem;
            background: var(--vv-danger);
            color: #131519;
            box-shadow: 0 10px 24px -10px var(--vv-danger);
            flex-shrink: 0;
        }

        .vv-cd-title {
            margin: 0;
            font-size: 2.65rem;
            font-weight: 800;
            letter-spacing: -0.025em;
            line-height: 1.12;
            color: var(--vv-text);
        }

        .vv-cd-controls {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.75rem;
        }

        .vv-cd-pill {
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

        .vv-cd-pill--id {
            background: var(--vv-navy);
            border-color: var(--vv-blue);
            color: var(--vv-cream);
            font-weight: 800;
        }

        a.vv-cd-pill:hover {
            background: var(--vv-navy);
            border-color: var(--vv-blue);
            color: var(--vv-cream);
        }

        /* ---------- Banner de Advertencia ---------- */
        .vv-cd-warning {
            display: flex;
            align-items: center;
            gap: 0.9rem;
            padding: 1.1rem 1.4rem;
            margin-bottom: 1.45rem;
            border-radius: 0.95rem;
            background: var(--vv-danger-soft);
            border: 1px solid rgba(248, 113, 113, 0.45);
            color: var(--vv-text);
            font-size: 1.12rem;
            font-weight: 600;
        }

        .vv-cd-warning i {
            color: var(--vv-danger);
            font-size: 1.45rem;
            flex-shrink: 0;
        }

        /* ---------- Contenedor Principal Bento ---------- */
        .vv-cd-card {
            position: relative;
            background: var(--vv-card);
            border: 1px solid rgba(248, 113, 113, 0.32);
            border-radius: var(--vv-radius);
            box-shadow: var(--vv-shadow);
            overflow: hidden;
            width: 100%;
        }

        .vv-cd-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--vv-danger);
        }

        .vv-cd-card-head {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 0.85rem;
            padding: 1.15rem 1.85rem;
            border-bottom: 1px solid var(--vv-border);
            background: rgba(255, 255, 255, 0.015);
        }

        .vv-cd-section-title {
            margin: 0;
            font-size: 0.92rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.09em;
            color: var(--vv-muted);
        }

        .vv-cd-body {
            padding: 1.75rem 1.85rem;
        }

        /* ---------- Grilla de Atributos ---------- */
        .vv-del-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1.35rem;
        }

        @media (max-width: 767.98px) {
            .vv-del-grid {
                grid-template-columns: 1fr;
            }
        }

        .vv-del-tile {
            background: var(--vv-card-elev);
            border: 1px solid var(--vv-border);
            border-radius: 1rem;
            padding: 1.45rem 1.55rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .vv-del-tile.is-full {
            grid-column: 1 / -1;
        }

        .vv-del-head {
            display: flex;
            align-items: center;
            gap: 0.72rem;
            margin-bottom: 0.9rem;
        }

        .vv-del-ico {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 0.65rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.22rem;
            background: var(--vv-cream);
            color: var(--vv-navy-deep);
            flex-shrink: 0;
        }

        .vv-del-label {
            font-size: 1.12rem;
            font-weight: 700;
            color: var(--vv-muted);
        }

        .vv-del-value-box {
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
        .vv-cd-footer {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 1.1rem;
            padding: 1.4rem 1.85rem;
            border-top: 1px solid var(--vv-border);
            background: rgba(0, 0, 0, 0.16);
        }

        .vv-btn-confirm-del {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            padding: 0.9rem 1.75rem;
            border-radius: 0.8rem;
            border: 0;
            background: var(--vv-danger);
            color: #131519;
            font-size: 1.12rem;
            font-weight: 800;
            cursor: pointer;
            box-shadow: 0 10px 22px -10px var(--vv-danger);
            transition: transform 0.15s ease, filter 0.15s ease;
        }

        .vv-btn-confirm-del:hover {
            transform: translateY(-2px);
            filter: brightness(1.07);
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

    <div class="vv-crud-delete">

        {{-- ============ CABECERA PRINCIPAL ============ --}}
        <div class="vv-cd-topbar">
            <div class="d-flex align-items-center gap-3">
                <span class="vv-cd-hero-ico">
                    <i class="bi bi-trash3"></i>
                </span>
                <div>
                    <h1 class="vv-cd-title">Eliminar: {{ $title }} #{{ $record->ID }}</h1>
                </div>
            </div>

            <div class="vv-cd-controls">
                <span class="vv-cd-pill vv-cd-pill--id">
                    <i class="bi bi-hash"></i>
                    ID {{ $record->ID }}
                </span>
                <span class="vv-cd-pill">
                    <i class="bi bi-person-badge" style="color: var(--vv-cream);"></i>
                    {{ $roleName }}
                </span>
                <span class="vv-cd-pill">
                    <i class="bi bi-calendar3" style="color: var(--vv-cream);"></i>
                    {{ $today }}
                </span>
                <a href="{{ $backUrl }}" class="vv-cd-pill text-decoration-none">
                    <i class="bi bi-arrow-left"></i>
                    Volver
                </a>
            </div>
        </div>

        {{-- ============ AVISO DE CONFIRMACIÓN ============ --}}
        <div class="vv-cd-warning" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <span>¿Confirmas que deseas eliminar este registro? Esta acción no se puede deshacer.</span>
        </div>

        {{-- ============ TARJETA CON DATOS DEL REGISTRO ============ --}}
        <section class="vv-cd-card">
            <div class="vv-cd-card-head">
                <h2 class="vv-cd-section-title">Registro seleccionado para eliminación</h2>
                <span class="vv-cd-pill py-1 px-3" style="font-size: 0.9rem;">
                    {{ $fieldsCollection->count() }} {{ $fieldsCollection->count() === 1 ? 'atributo' : 'atributos' }}
                </span>
            </div>

            <div class="vv-cd-body">
                <div class="vv-del-grid">
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

                        <div class="vv-del-tile {{ $isFull ? 'is-full' : '' }}">
                            <div class="vv-del-head">
                                <span class="vv-del-ico">
                                    <i class="bi {{ $ico }}"></i>
                                </span>
                                <span class="vv-del-label">{{ $field['label'] }}</span>
                            </div>

                            <div class="vv-del-value-box">
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

            <form method="POST" action="{{ route($routeName . '.destroy', $record->ID) }}">
                @csrf
                @method('DELETE')
                <div class="vv-cd-footer">
                    <div class="d-flex align-items-center gap-2" style="font-size: 1.02rem; color: var(--vv-muted);">
                        <i class="bi bi-shield-exclamation" style="color: var(--vv-danger); font-size: 1.25rem;"></i>
                        <span>Revisa los datos antes de confirmar el borrado definitivo.</span>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <a class="vv-btn-cancel" href="{{ $backUrl }}">
                            Cancelar
                        </a>
                        <button class="vv-btn-confirm-del" type="submit">
                            <i class="bi bi-trash3"></i>
                            Confirmar eliminación
                        </button>
                    </div>
                </div>
            </form>
        </section>
    </div>
@endsection