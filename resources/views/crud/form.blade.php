@php
    $record    = $record ?? null;
    $title     = $title ?? 'Registro';
    $action    = $action ?? ($record ? 'Editar' : 'Crear');
    $pageTitle = trim($action . ' ' . $title);
    $backUrl   = \Illuminate\Support\Facades\Route::has($routeName . '.index')
        ? route($routeName . '.index')
        : route('dashboard');
@endphp

@extends('layouts.app')

@section('title', $pageTitle)

@section('content')
    @php
        $user     = Auth::user();
        $roleName = $user?->rol?->NOMBRE_ROL ?? 'OPERADOR';
        $routeKey = \Illuminate\Support\Str::lower(\Illuminate\Support\Str::ascii((string) ($routeName ?? '') . ' ' . (string) $title));
        $isEdit   = (bool) $record;

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
                'icon'    => $isEdit ? 'bi-pencil-square' : 'bi-plus-square',
                'color'   => '#6BC9C6',
                'soft'    => 'rgba(107, 201, 198, 0.22)',
                'btnText' => '#1D263D',
            ],
        };

        $fieldIcon = function (string $name, string $type): string {
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
                str_contains($k, 'estado') || str_contains($k, 'activo')                                  => 'bi-toggles',
                str_contains($k, 'prestacion') || str_contains($k, 'examen')                              => 'bi-activity',
                str_contains($k, 'prevision') || str_contains($k, 'isapre') || str_contains($k, 'fonasa') => 'bi-heart-pulse',
                str_contains($k, 'descripcion') || str_contains($k, 'observacion') || str_contains($k, 'cuerpo') || $type === 'textarea' => 'bi-card-text',
                default                                                                                   => 'bi-input-cursor-text',
            };
        };

        /* ============ 2) CÁLCULO DE GRILLA ÓPTIMA (0 huecos vacíos) ============ */
        $fieldsCollection    = collect($fields);
        $requiredCount       = $fieldsCollection->filter(fn ($f) => !empty($f['required']))->count();
        $compactCount        = $fieldsCollection->filter(fn ($f) => ($f['type'] ?? 'text') !== 'textarea')->count();
        $promoteFirstCompact = ($compactCount % 2) === 1;
        $compactIndex        = 0;
        $today               = \Illuminate\Support\Str::ucfirst(now()->locale('es')->translatedFormat('l j \d\e F'));
    @endphp

    <style>
        /* =====================================================================
           CRUD BENTO · Base oscura mate + colores sólidos de la paleta
           ===================================================================== */
        .vv-crud-form {
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

        [data-bs-theme="light"] .vv-crud-form {
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
        .vv-cf-topbar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 1.4rem;
            margin-bottom: 1.75rem;
        }

        .vv-cf-hero-ico {
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

        .vv-cf-title {
            margin: 0;
            font-size: 2.65rem;
            font-weight: 800;
            letter-spacing: -0.025em;
            line-height: 1.12;
            color: var(--vv-text);
        }

        .vv-cf-controls {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.75rem;
        }

        .vv-cf-pill {
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

        .vv-cf-pill--id {
            background: var(--vv-navy);
            border-color: var(--vv-blue);
            color: var(--vv-cream);
        }

        a.vv-cf-pill:hover {
            background: var(--vv-navy);
            border-color: var(--vv-blue);
            color: var(--vv-cream);
        }

        /* ---------- Alerta de validación temática ---------- */
        .vv-cf-alert {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.95rem 1.25rem;
            margin-bottom: 1.35rem;
            border-radius: 0.9rem;
            background: var(--vv-navy);
            border: 1px solid var(--vv-cream);
            color: var(--vv-cream);
            font-size: 1rem;
            font-weight: 500;
        }

        /* ---------- Contenedor Principal Bento ---------- */
        .vv-cf-card {
            position: relative;
            background: var(--vv-card);
            border: 1px solid var(--vv-border);
            border-radius: var(--vv-radius);
            box-shadow: var(--vv-shadow);
            overflow: hidden;
            width: 100%;
        }

        .vv-cf-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--g-accent);
        }

        .vv-cf-card-head {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 0.85rem;
            padding: 1.15rem 1.85rem;
            border-bottom: 1px solid var(--vv-border);
            background: rgba(255, 255, 255, 0.015);
        }

        .vv-cf-section-title {
            margin: 0;
            font-size: 0.92rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.09em;
            color: var(--vv-muted);
        }

        .vv-cf-body {
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
            transition: border-color 0.16s ease, box-shadow 0.16s ease;
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
            background: rgba(159, 178, 176, 0.16);
            color: var(--vv-sage);
        }

        .vv-req-tag.is-required {
            background: var(--vv-navy);
            color: var(--vv-cream);
            border: 1px solid rgba(130, 151, 209, 0.45);
        }

        /* ---------- Inputs y Selects Notorios ---------- */
        .vv-crud-form .form-control,
        .vv-crud-form .form-select {
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

        [data-bs-theme="light"] .vv-crud-form .form-control,
        [data-bs-theme="light"] .vv-crud-form .form-select {
            color-scheme: light;
        }

        .vv-crud-form .form-control:focus,
        .vv-crud-form .form-select:focus {
            background-color: var(--vv-input-bg);
            border-color: var(--g-accent);
            box-shadow: 0 0 0 0.22rem var(--g-soft);
            color: var(--vv-text);
        }

        .vv-crud-form .form-control.is-invalid,
        .vv-crud-form .form-select.is-invalid {
            border-color: var(--vv-cream);
        }

        .vv-crud-form textarea.form-control {
            min-height: 165px;
            line-height: 1.6;
        }

        .vv-pwd-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }

        .vv-pwd-wrap .form-control {
            padding-right: 3.35rem;
        }

        .vv-pwd-toggle {
            position: absolute;
            right: 0.75rem;
            border: 0;
            background: transparent;
            color: var(--vv-muted);
            width: 2.35rem;
            height: 2.35rem;
            border-radius: 0.55rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            cursor: pointer;
            transition: color 0.15s ease, background-color 0.15s ease;
        }

        .vv-pwd-toggle:hover {
            color: var(--vv-cream);
            background: var(--vv-navy);
        }

        /* ---------- Footer de Acciones ---------- */
        .vv-cf-footer {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 1.1rem;
            padding: 1.4rem 1.85rem;
            border-top: 1px solid var(--vv-border);
            background: rgba(0, 0, 0, 0.16);
        }

        .vv-btn-save {
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

        .vv-btn-save:hover {
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

    <div class="vv-crud-form">

        {{-- ============ CABECERA PRINCIPAL ÚNICA Y PROTAGONISTA ============ --}}
        <div class="vv-cf-topbar">
            <div class="d-flex align-items-center gap-3">
                <span class="vv-cf-hero-ico">
                    <i class="bi {{ $meta['icon'] }}"></i>
                </span>
                <div>
                    <h1 class="vv-cf-title">{{ $action ? $action . ': ' . $title : $title }}</h1>
                </div>
            </div>

            <div class="vv-cf-controls">
                @if($isEdit)
                    <span class="vv-cf-pill vv-cf-pill--id">
                        <i class="bi bi-hash"></i>
                        ID {{ $record->ID }}
                    </span>
                @endif
                <span class="vv-cf-pill">
                    <i class="bi bi-person-badge" style="color: var(--g-accent);"></i>
                    {{ $roleName }}
                </span>
                <span class="vv-cf-pill">
                    <i class="bi bi-calendar3" style="color: var(--g-accent);"></i>
                    {{ $today }}
                </span>
                <a href="{{ $backUrl }}" class="vv-cf-pill text-decoration-none">
                    <i class="bi bi-arrow-left"></i>
                    Volver
                </a>
            </div>
        </div>

        @if($errors->any())
            <div class="vv-cf-alert" role="alert">
                <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                <span>Revisa los campos marcados antes de guardar el registro.</span>
            </div>
        @endif

        {{-- ============ TARJETA DEL FORMULARIO ============ --}}
        <section class="vv-cf-card">
            <div class="vv-cf-card-head">
                <h2 class="vv-cf-section-title">
                    {{ $isEdit ? 'Edición de parámetros' : 'Datos del nuevo registro' }}
                </h2>
                <div class="d-flex align-items-center gap-2">
                    <span class="vv-cf-pill py-1 px-3" style="font-size: 0.9rem;">
                        {{ $fieldsCollection->count() }} {{ $fieldsCollection->count() === 1 ? 'campo' : 'campos' }}
                    </span>
                    @if($requiredCount > 0)
                        <span class="vv-cf-pill vv-cf-pill--id py-1 px-3" style="font-size: 0.9rem;">
                            {{ $requiredCount }} {{ $requiredCount === 1 ? 'obligatorio' : 'obligatorios' }}
                        </span>
                    @endif
                </div>
            </div>

            <form method="POST" action="{{ $record ? route($routeName.'.update', $record->ID) : route($routeName.'.store') }}">
                @csrf
                @if($record)
                    @method('PUT')
                @endif

                <div class="vv-cf-body">
                    <div class="vv-fields-grid">
                        @foreach($fields as $field)
                            @php
                                $name       = $field['name'];
                                $type       = $field['type'] ?? 'text';
                                $isRequired = !empty($field['required']);

                                $value = $type === 'password' ? '' : old($name, $record?->{$name});
                                if ($type === 'date' && $value) {
                                    $value = $value instanceof \DateTimeInterface ? $value->format('Y-m-d') : substr((string) $value, 0, 10);
                                }

                                if ($type === 'textarea') {
                                    $isFull = true;
                                } else {
                                    $isFull = ($compactIndex === 0 && $promoteFirstCompact);
                                    $compactIndex++;
                                }

                                $ico = $fieldIcon($name, $type);
                                $placeholder = $field['placeholder'] ?? ($type === 'password' && $isEdit && !$isRequired ? 'Dejar en blanco para mantener actual' : null);
                            @endphp

                            <div class="vv-field-tile {{ $isFull ? 'is-full' : '' }}">
                                <div class="vv-field-head">
                                    <label class="vv-field-label" for="{{ $name }}">
                                        <span class="vv-field-ico">
                                            <i class="bi {{ $ico }}"></i>
                                        </span>
                                        <span>{{ $field['label'] }}</span>
                                    </label>
                                    <span class="vv-req-tag {{ $isRequired ? 'is-required' : '' }}">
                                        {{ $isRequired ? 'Obligatorio' : 'Opcional' }}
                                    </span>
                                </div>

                                @if($type === 'select')
                                    <select class="form-select @error($name) is-invalid @enderror"
                                            id="{{ $name }}"
                                            name="{{ $name }}"
                                            @if($isRequired) required @endif>
                                        <option value="">Selecciona una opción</option>
                                        @foreach(($field['options'] ?? []) as $optionValue => $optionLabel)
                                            <option value="{{ $optionValue }}" @selected((string) $value === (string) $optionValue)>
                                                {{ $optionLabel }}
                                            </option>
                                        @endforeach
                                    </select>

                                @elseif($type === 'textarea')
                                    <textarea class="form-control @error($name) is-invalid @enderror"
                                              id="{{ $name }}"
                                              name="{{ $name }}"
                                              maxlength="{{ $field['maxlength'] ?? 1028 }}"
                                              @if($placeholder) placeholder="{{ $placeholder }}" @endif
                                              @if($isRequired) required @endif>{{ $value }}</textarea>

                                @elseif($type === 'password')
                                    <div class="vv-pwd-wrap">
                                        <input class="form-control @error($name) is-invalid @enderror"
                                               id="{{ $name }}"
                                               name="{{ $name }}"
                                               type="password"
                                               value=""
                                               autocomplete="new-password"
                                               @if($placeholder) placeholder="{{ $placeholder }}" @endif
                                               @if(isset($field['maxlength'])) maxlength="{{ $field['maxlength'] }}" @endif
                                               @if($isRequired) required @endif>
                                        <button type="button" class="vv-pwd-toggle" data-vv-pwd-toggle="{{ $name }}" title="Mostrar u ocultar">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>

                                @else
                                    <input class="form-control @error($name) is-invalid @enderror"
                                           id="{{ $name }}"
                                           name="{{ $name }}"
                                           type="{{ $type }}"
                                           value="{{ $value }}"
                                           @if($placeholder) placeholder="{{ $placeholder }}" @endif
                                           @if(isset($field['step'])) step="{{ $field['step'] }}" @endif
                                           @if(isset($field['maxlength'])) maxlength="{{ $field['maxlength'] }}" @endif
                                           @if($isRequired) required @endif>
                                @endif

                                @error($name)
                                    <div class="invalid-feedback d-block mt-2 fw-semibold" style="color: var(--vv-cream); font-size: 1rem;">{{ $message }}</div>
                                @enderror
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="vv-cf-footer">
                    <div class="d-flex align-items-center gap-2" style="font-size: 1.02rem; color: var(--vv-muted);">
                        <i class="bi bi-shield-check" style="color: var(--g-accent); font-size: 1.25rem;"></i>
                        <span>Verifica los datos ingresados antes de guardar el registro.</span>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <a class="vv-btn-cancel" href="{{ $backUrl }}">
                            Cancelar
                        </a>
                        <button class="vv-btn-save" type="submit">
                            <i class="bi bi-check-lg"></i>
                            Guardar
                        </button>
                    </div>
                </div>
            </form>
        </section>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-vv-pwd-toggle]').forEach((btn) => {
                btn.addEventListener('click', () => {
                    const input = document.getElementById(btn.dataset.vvPwdToggle);
                    if (!input) return;
                    const show = input.type === 'password';
                    input.type = show ? 'text' : 'password';
                    btn.innerHTML = show ? '<i class="bi bi-eye-slash"></i>' : '<i class="bi bi-eye"></i>';
                });
            });
        });
    </script>
@endsection