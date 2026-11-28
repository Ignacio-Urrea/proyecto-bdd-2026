@extends('layouts.app', ['title' => 'Iniciar sesión'])

@section('content')
    <style>
        /* =====================================================================
           LOGIN PROTAGONISTA · Tarjeta amplia centrada y brillo celeste
           ===================================================================== */
        .login-box {
            width: 100% !important;
            max-width: 520px !important;
            margin: 0 auto !important;
        }

        .vv-login-wrap {
            --vv-card: #1b1d21;
            --vv-card-elev: #23262c;
            --vv-input-bg: #131519;
            --vv-border: rgba(107, 201, 198, 0.24);
            --vv-text: #f3f4f6;
            --vv-muted: #9aa2b1;

            --vv-cream: #F5E6D3;
            --vv-navy: #28334F;
            --vv-navy-deep: #1D263D;
            --vv-blue: #8297D1;
            --vv-mint: #6BC9C6;
            --vv-mint-bright: #88E5E2;

            --vv-glow: 0 0 26px rgba(107, 201, 198, 0.55), 0 0 52px rgba(107, 201, 198, 0.22);
            --vv-card-shadow: 0 24px 55px -16px rgba(0, 0, 0, 0.85), 0 0 36px -10px rgba(107, 201, 198, 0.28);

            width: 100%;
            max-width: 520px;
            margin: 1.5rem auto;
            color: var(--vv-text);
        }

        [data-bs-theme="light"] .vv-login-wrap {
            --vv-card: #ffffff;
            --vv-card-elev: #f1f5f9;
            --vv-input-bg: #f8fafc;
            --vv-border: rgba(107, 201, 198, 0.45);
            --vv-text: #0f172a;
            --vv-muted: #64748b;
            --vv-card-shadow: 0 20px 45px -18px rgba(15, 23, 42, 0.22), 0 0 28px -10px rgba(107, 201, 198, 0.35);
        }

        .vv-login-card {
            position: relative;
            background: var(--vv-card);
            border: 1px solid var(--vv-border);
            border-radius: 1.45rem;
            box-shadow: var(--vv-card-shadow);
            overflow: hidden;
        }

        .vv-login-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, var(--vv-mint-bright), var(--vv-blue), var(--vv-mint));
            box-shadow: 0 2px 18px rgba(107, 201, 198, 0.75);
        }

        .vv-login-head {
            padding: 2.6rem 2.5rem 1.8rem;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
            background: radial-gradient(circle at top center, rgba(107, 201, 198, 0.12), transparent 70%);
        }

        .vv-login-logo {
            width: 5rem;
            height: 5rem;
            margin: 0 auto 1.25rem;
            border-radius: 1.25rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 2.45rem;
            background: linear-gradient(135deg, var(--vv-mint-bright) 0%, var(--vv-mint) 100%);
            color: var(--vv-navy-deep);
            box-shadow: var(--vv-glow);
        }

        .vv-login-brand {
            margin: 0 0 0.35rem;
            font-size: 2.05rem;
            font-weight: 800;
            letter-spacing: -0.02em;
            line-height: 1.15;
            color: var(--vv-text);
            text-shadow: 0 0 24px rgba(107, 201, 198, 0.2);
        }

        .vv-login-sub {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.32rem 0.95rem;
            border-radius: 999px;
            font-size: 0.92rem;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            background: var(--vv-navy);
            color: var(--vv-mint-bright);
            border: 1px solid rgba(107, 201, 198, 0.45);
            box-shadow: 0 0 14px rgba(107, 201, 198, 0.22);
        }

        .vv-login-body {
            padding: 2.25rem 2.5rem 2.6rem;
        }

        .vv-login-msg {
            margin: 0 0 1.75rem;
            text-align: center;
            font-size: 1.12rem;
            font-weight: 500;
            color: var(--vv-muted);
        }

        .vv-field-group {
            margin-bottom: 1.4rem;
        }

        .vv-field-label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 0.98rem;
            font-weight: 700;
            color: var(--vv-text);
        }

        .vv-input-box {
            display: flex;
            align-items: stretch;
            background: var(--vv-input-bg);
            border: 1.5px solid var(--vv-border);
            border-radius: 0.95rem;
            overflow: hidden;
            transition: border-color 0.16s ease, box-shadow 0.16s ease;
        }

        .vv-input-box:focus-within {
            border-color: var(--vv-mint);
            box-shadow: 0 0 0 3px rgba(107, 201, 198, 0.2), 0 0 20px rgba(107, 201, 198, 0.25);
        }

        .vv-input-box.is-invalid {
            border-color: #f87171;
        }

        .vv-input-ico {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0 1.1rem;
            font-size: 1.3rem;
            color: var(--vv-mint);
            background: rgba(107, 201, 198, 0.06);
            border-right: 1px solid rgba(107, 201, 198, 0.14);
        }

        .vv-input {
            width: 100%;
            border: 0;
            background: transparent;
            color: var(--vv-text);
            font-size: 1.15rem;
            font-weight: 500;
            padding: 1rem 1.15rem;
            outline: none;
        }

        .vv-input::placeholder {
            color: var(--vv-muted);
            opacity: 0.75;
        }

        .vv-eye-btn {
            border: 0;
            background: transparent;
            color: var(--vv-muted);
            padding: 0 1.1rem;
            font-size: 1.25rem;
            cursor: pointer;
            transition: color 0.15s ease;
        }

        .vv-eye-btn:hover {
            color: var(--vv-mint-bright);
        }

        .vv-submit-btn {
            width: 100%;
            margin-top: 0.6rem;
            padding: 1.05rem 1.5rem;
            border: 0;
            border-radius: 0.95rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.65rem;
            font-size: 1.18rem;
            font-weight: 800;
            letter-spacing: 0.01em;
            background: linear-gradient(135deg, var(--vv-mint-bright) 0%, var(--vv-mint) 100%);
            color: var(--vv-navy-deep);
            box-shadow: 0 0 24px rgba(107, 201, 198, 0.55);
            cursor: pointer;
            transition: transform 0.16s ease, box-shadow 0.16s ease, filter 0.16s ease;
        }

        .vv-submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--vv-glow);
            filter: brightness(1.04);
        }

        .vv-login-foot {
            padding: 1.1rem 2rem;
            text-align: center;
            font-size: 0.92rem;
            color: var(--vv-muted);
            background: rgba(0, 0, 0, 0.18);
            border-top: 1px solid rgba(255, 255, 255, 0.05);
        }
    </style>

    <div class="vv-login-wrap">
        <div class="vv-login-card">
            <div class="vv-login-head">
                <div class="vv-login-logo">
                    <i class="bi bi-lungs"></i>
                </div>
                <h1 class="vv-login-brand">Centro Radiologico</h1>
                <span class="vv-login-sub">
                    <i class="bi bi-shield-check"></i> IMAJ · Portal Clínico
                </span>
            </div>

            <form method="POST" action="{{ route('login.store') }}">
                @csrf
                <div class="vv-login-body">
                    <p class="vv-login-msg">Ingresa tus credenciales para acceder al sistema</p>

                    <div class="vv-field-group">
                        <label for="NOMBRE_USUARIO" class="vv-field-label">Nombre de usuario</label>
                        <div class="vv-input-box @error('NOMBRE_USUARIO') is-invalid @enderror">
                            <span class="vv-input-ico"><i class="bi bi-person-fill"></i></span>
                            <input
                                class="vv-input"
                                id="NOMBRE_USUARIO"
                                name="NOMBRE_USUARIO"
                                type="text"
                                maxlength="16"
                                value="{{ old('NOMBRE_USUARIO') }}"
                                placeholder="Ej: superadmin"
                                autocomplete="username"
                                required
                                autofocus
                            >
                        </div>
                        @error('NOMBRE_USUARIO')
                            <div class="invalid-feedback d-block mt-2" style="font-size: 0.96rem;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="vv-field-group">
                        <label for="password" class="vv-field-label">Contraseña</label>
                        <div class="vv-input-box @error('password') is-invalid @enderror">
                            <span class="vv-input-ico"><i class="bi bi-lock-fill"></i></span>
                            <input
                                class="vv-input"
                                id="password"
                                name="password"
                                type="password"
                                placeholder="Ingresa tu contraseña"
                                autocomplete="current-password"
                                required
                            >
                            <button type="button" class="vv-eye-btn" id="vvTogglePass" title="Mostrar u ocultar contraseña">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="invalid-feedback d-block mt-2" style="font-size: 0.96rem;">{{ $message }}</div>
                        @enderror
                    </div>

                    <button class="vv-submit-btn" type="submit">
                        <span>Ingresar al sistema</span>
                        <i class="bi bi-arrow-right-circle-fill"></i>
                    </button>
                </div>
            </form>

            <div class="vv-login-foot">
                <i class="bi bi-hospital me-1" style="color: var(--vv-mint);"></i>
                Sistema de Gestión Diagnóstica · Acceso autorizado
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const btn   = document.getElementById('vvTogglePass');
            const input = document.getElementById('password');
            if (!btn || !input) return;

            btn.addEventListener('click', function () {
                const isPass = input.type === 'password';
                input.type = isPass ? 'text' : 'password';
                btn.innerHTML = isPass ? '<i class="bi bi-eye-slash"></i>' : '<i class="bi bi-eye"></i>';
            });
        });
    </script>
@endsection