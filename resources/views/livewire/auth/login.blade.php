<div>
    <div class="text-center mb-4">
        <h3 class="fw-bold text-dark">Bienvenido</h3>
        <p class="text-muted">Ingresa tus credenciales para continuar</p>
    </div>

    <form wire:submit.prevent="login">
        <div class="mb-3">
            <label class="form-label fw-semibold">Correo Electrónico</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="ph ph-envelope"></i></span>
                <input wire:model="email" type="email" class="form-control border-start-0 bg-light @error('email') is-invalid @enderror" placeholder="ejemplo@correo.com">
                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Contraseña</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="ph ph-lock"></i></span>
                <input wire:model="password" type="password" class="form-control border-start-0 bg-light @error('password') is-invalid @enderror" placeholder="••••••••">
                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="mb-4 d-flex justify-content-between align-items-center">
            <div class="form-check">
                <input wire:model="remember" class="form-check-input" type="checkbox" id="remember">
                <label class="form-check-label small text-muted" for="remember">Recuérdame</label>
            </div>
            <a href="#" class="small text-decoration-none">¿Olvidaste tu contraseña?</a>
        </div>

        <div class="d-grid">
            <button type="submit" class="btn btn-primary py-2 fw-bold shadow-sm" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="login">Iniciar Sesión</span>
                <span wire:loading wire:target="login"><i class="ph ph-spinner fa-spin me-2"></i> Procesando...</span>
            </button>
        </div>
    </form>
</div>
