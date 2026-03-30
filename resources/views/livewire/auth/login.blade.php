<div>
    <h2 class="login-heading">Bienvenido de vuelta</h2>
    <p class="login-subheading">Ingresa tus credenciales para continuar</p>

    <form wire:submit.prevent="login" autocomplete="off">

        {{-- ── USUARIO ── --}}
        <div class="mb-3">
            <label class="field-label" for="username-input">Usuario</label>
            <div class="input-wrap">
                <input
                    wire:model="username"
                    id="username-input"
                    type="text"
                    class="input-field @error('username') is-invalid @enderror"
                    placeholder="Ingresa tu usuario"
                    autocomplete="username"
                >
                <i class="ph ph-user input-icon"></i>
            </div>
            @error('username')
                <p class="error-msg"><i class="ph ph-warning-circle"></i> {{ $message }}</p>
            @enderror
        </div>

        {{-- ── CONTRASEÑA ── --}}
        <div class="mb-1">
            <label class="field-label" for="password-input">Contraseña</label>
            <div class="input-wrap">
                <input
                    wire:model="password"
                    id="password-input"
                    type="password"
                    class="input-field has-toggle @error('password') is-invalid @enderror"
                    placeholder="••••••••"
                    autocomplete="current-password"
                >
                <i class="ph ph-lock input-icon"></i>
                <button
                    type="button"
                    id="toggle-password"
                    class="input-toggle"
                    onclick="togglePassword()"
                    title="Mostrar / Ocultar contraseña"
                    tabindex="-1"
                >
                    <i id="eye-icon" class="ph ph-eye"></i>
                </button>
            </div>
            @error('password')
                <p class="error-msg"><i class="ph ph-warning-circle"></i> {{ $message }}</p>
            @enderror
        </div>

        {{-- ── RECORDAR / OLVIDÉ ── --}}
        <div class="login-extras mt-3">
            <label class="custom-check">
                <input wire:model="remember" type="checkbox" id="remember">
                <span>Recuérdame</span>
            </label>
            <a href="#" class="forgot-link">¿Olvidaste tu contraseña?</a>
        </div>

        {{-- ── BOTÓN ── --}}
        <button type="submit" id="btn-login" class="btn-login" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="login" class="btn-content">
                <i class="ph ph-sign-in"></i>
                Iniciar Sesión
            </span>
            <span wire:loading wire:target="login" class="btn-content">
                <i class="ph ph-spinner spinner-icon"></i>
                Verificando...
            </span>
        </button>

    </form>
</div>

<script>
    function togglePassword() {
        const input = document.getElementById('password-input');
        const icon  = document.getElementById('eye-icon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('ph-eye', 'ph-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('ph-eye-slash', 'ph-eye');
        }
    }
</script>
