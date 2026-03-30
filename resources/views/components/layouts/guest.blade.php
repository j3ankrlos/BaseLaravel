<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Login' }} — Granja Pro</title>
    <meta name="description" content="Sistema de Gestión de Granja Porcina. Ingresa tus credenciales para acceder al panel de control.">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>

    <style>
        *, *::before, *::after { box-sizing: border-box; }

        :root {
            --brand-primary:   #6366f1;
            --brand-secondary: #8b5cf6;
            --brand-accent:    #a78bfa;
            --dark-bg:         #0f1117;
            --card-bg:         rgba(255,255,255,0.04);
            --glass-border:    rgba(255,255,255,0.10);
            --text-primary:    #f1f5f9;
            --text-muted:      #94a3b8;
            --input-bg:        rgba(255,255,255,0.06);
            --input-border:    rgba(99,102,241,0.35);
            --input-focus:     rgba(99,102,241,0.65);
            --shadow-glow:     0 0 40px rgba(99,102,241,0.25);
        }

        html, body {
            height: 100%;
            margin: 0;
            font-family: 'Inter', sans-serif;
            background: var(--dark-bg);
            overflow: hidden;
        }

        /* ── LAYOUT ─────────────────────────────── */
        .login-wrapper {
            display: flex;
            height: 100vh;
            width: 100vw;
        }

        /* ── LEFT PANEL ─────────────────────────── */
        .login-panel-left {
            flex: 1;
            position: relative;
            overflow: hidden;
            display: none;
        }
        @media (min-width: 992px) {
            .login-panel-left { display: flex; flex-direction: column; justify-content: flex-end; }
        }

        .login-panel-left .bg-image {
            position: absolute;
            inset: 0;
            background: url('/img/login-bg.png') center/cover no-repeat;
        }
        .login-panel-left .bg-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(
                160deg,
                rgba(15,17,23,0.10) 0%,
                rgba(99,102,241,0.30) 50%,
                rgba(15,17,23,0.85) 100%
            );
        }
        .login-panel-left .left-content {
            position: relative;
            z-index: 2;
            padding: 2.5rem 3rem;
            animation: slideUp 0.8s ease forwards;
        }
        .brand-tag {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            background: rgba(99,102,241,0.20);
            border: 1px solid rgba(99,102,241,0.40);
            backdrop-filter: blur(8px);
            padding: .35rem .9rem;
            border-radius: 50px;
            font-size: .78rem;
            font-weight: 600;
            color: var(--brand-accent);
            letter-spacing: .06em;
            text-transform: uppercase;
            margin-bottom: 1.2rem;
        }
        .left-headline {
            font-size: clamp(1.8rem, 3vw, 2.6rem);
            font-weight: 800;
            color: #fff;
            line-height: 1.2;
            margin-bottom: .8rem;
        }
        .left-headline span {
            background: linear-gradient(90deg, #a78bfa, #6366f1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .left-sub {
            color: rgba(255,255,255,0.65);
            font-size: .9rem;
            font-weight: 400;
            margin-bottom: 0;
        }
        .floating-stats {
            display: flex;
            gap: 1rem;
            margin-top: 1.8rem;
        }
        .stat-chip {
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.12);
            backdrop-filter: blur(12px);
            border-radius: .75rem;
            padding: .7rem 1.1rem;
            flex: 1;
        }
        .stat-chip .stat-value {
            font-size: 1.3rem;
            font-weight: 700;
            color: #fff;
            line-height: 1;
        }
        .stat-chip .stat-label {
            font-size: .7rem;
            color: rgba(255,255,255,0.55);
            margin-top: .25rem;
            font-weight: 500;
        }

        /* ── RIGHT PANEL ─────────────────────────── */
        .login-panel-right {
            width: 100%;
            max-width: 480px;
            background: #0f1117;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2.5rem 2rem;
            position: relative;
            border-left: 1px solid var(--glass-border);
            animation: fadeIn 0.6s ease forwards;
        }
        @media (min-width: 992px) {
            .login-panel-right { padding: 3rem 3.5rem; }
        }

        /* subtle grid bg */
        .login-panel-right::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(99,102,241,0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(99,102,241,0.04) 1px, transparent 1px);
            background-size: 32px 32px;
            pointer-events: none;
        }

        .right-inner {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 360px;
        }

        /* logo */
        .login-logo {
            display: flex;
            align-items: center;
            gap: .75rem;
            margin-bottom: 2.2rem;
        }
        .logo-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            background: linear-gradient(135deg, var(--brand-primary), var(--brand-secondary));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
            color: #fff;
            box-shadow: 0 4px 16px rgba(99,102,241,0.45);
        }
        .logo-text {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-primary);
            line-height: 1.1;
        }
        .logo-sub {
            font-size: .7rem;
            font-weight: 500;
            color: var(--brand-accent);
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        /* headings */
        .login-heading {
            font-size: 1.55rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: .35rem;
            letter-spacing: -.02em;
        }
        .login-subheading {
            font-size: .85rem;
            color: var(--text-muted);
            margin-bottom: 2rem;
            font-weight: 400;
        }

        /* form fields */
        .field-label {
            font-size: .78rem;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: .07em;
            margin-bottom: .5rem;
            display: block;
        }
        .input-wrap {
            position: relative;
            margin-bottom: 1.2rem;
        }
        .input-icon {
            position: absolute;
            left: .95rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 1.1rem;
            pointer-events: none;
            transition: color .25s;
        }
        .input-toggle {
            position: absolute;
            right: .85rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-muted);
            font-size: 1.15rem;
            cursor: pointer;
            padding: .2rem;
            line-height: 1;
            transition: color .25s;
            z-index: 5;
        }
        .input-toggle:hover { color: var(--brand-accent); }

        .input-field {
            width: 100%;
            background: var(--input-bg);
            border: 1.5px solid var(--input-border);
            border-radius: .75rem;
            color: var(--text-primary);
            font-family: 'Inter', sans-serif;
            font-size: .92rem;
            padding: .8rem 1rem .8rem 2.7rem;
            outline: none;
            transition: border-color .25s, box-shadow .25s, background .25s;
        }
        .input-field.has-toggle { padding-right: 2.9rem; }
        .input-field::placeholder { color: #475569; }
        .input-field:focus {
            border-color: var(--brand-primary);
            box-shadow: 0 0 0 3px rgba(99,102,241,0.18);
            background: rgba(255,255,255,0.08);
        }
        .input-field:focus + .input-icon,
        .input-wrap:focus-within .input-icon { color: var(--brand-accent); }
        .input-field.is-invalid { border-color: #f87171; }
        .error-msg {
            font-size: .75rem;
            color: #f87171;
            margin-top: .35rem;
            display: flex;
            align-items: center;
            gap: .3rem;
        }

        /* remember + forgot */
        .login-extras {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.6rem;
        }
        .custom-check {
            display: flex;
            align-items: center;
            gap: .5rem;
            cursor: pointer;
            user-select: none;
        }
        .custom-check input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: var(--brand-primary);
            cursor: pointer;
        }
        .custom-check span {
            font-size: .8rem;
            color: var(--text-muted);
        }
        .forgot-link {
            font-size: .8rem;
            color: var(--brand-accent);
            text-decoration: none;
            font-weight: 500;
            transition: color .2s;
        }
        .forgot-link:hover { color: #c4b5fd; }

        /* submit button */
        .btn-login {
            width: 100%;
            padding: .85rem;
            border: none;
            border-radius: .75rem;
            background: linear-gradient(135deg, var(--brand-primary) 0%, var(--brand-secondary) 100%);
            color: #fff;
            font-family: 'Inter', sans-serif;
            font-size: .95rem;
            font-weight: 600;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            transition: opacity .2s, transform .15s, box-shadow .25s;
            box-shadow: 0 4px 20px rgba(99,102,241,0.40);
            letter-spacing: .01em;
        }
        .btn-login:hover:not(:disabled) {
            opacity: .92;
            transform: translateY(-1px);
            box-shadow: 0 6px 28px rgba(99,102,241,0.55);
        }
        .btn-login:active:not(:disabled) { transform: translateY(0); }
        .btn-login:disabled { opacity: .6; cursor: not-allowed; }
        .btn-login::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(rgba(255,255,255,0.12), transparent);
            pointer-events: none;
        }
        .btn-content { display: flex; align-items: center; justify-content: center; gap: .5rem; }
        .spinner-icon { animation: spin 1s linear infinite; display: inline-block; }

        /* footer */
        .login-footer {
            margin-top: 2.2rem;
            text-align: center;
            font-size: .75rem;
            color: #334155;
        }
        .login-footer a { color: #475569; text-decoration: none; }

        /* animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateX(20px); }
            to   { opacity: 1; transform: translateX(0); }
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes spin {
            from { transform: rotate(0deg); }
            to   { transform: rotate(360deg); }
        }
    </style>

    @livewireStyles
</head>
<body>

<div class="login-wrapper">

    {{-- ── LEFT PANEL ── --}}
    <div class="login-panel-left">
        <div class="bg-image"></div>
        <div class="bg-overlay"></div>
        <div class="left-content">
            <div class="brand-tag">
                <i class="ph-fill ph-plant"></i>
                Sistema de Gestión Pecuaria
            </div>
            <h1 class="left-headline">
                Gestión inteligente<br>de tu <span>Granja Porcina</span>
            </h1>
            <p class="left-sub">Control total de empleados, inventario, incidencias y más desde un solo lugar.</p>
            <div class="floating-stats">
                <div class="stat-chip">
                    <div class="stat-value">360°</div>
                    <div class="stat-label">Vista completa</div>
                </div>
                <div class="stat-chip">
                    <div class="stat-value">Real-time</div>
                    <div class="stat-label">Actualizaciones</div>
                </div>
                <div class="stat-chip">
                    <div class="stat-value">100%</div>
                    <div class="stat-label">Seguro</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── RIGHT PANEL ── --}}
    <div class="login-panel-right">
        <div class="right-inner">

            <!-- Logo -->
            <div class="login-logo">
                <div class="logo-icon">
                    <i class="ph-fill ph-piggy-bank"></i>
                </div>
                <div>
                    <div class="logo-text">Granja Pro</div>
                    <div class="logo-sub">Panel de Control</div>
                </div>
            </div>

            {{ $slot }}

            <div class="login-footer">
                &copy; {{ date('Y') }} Granja Pro — Sistema de Gestión Porcina
            </div>

        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@livewireScripts
</body>
</html>
