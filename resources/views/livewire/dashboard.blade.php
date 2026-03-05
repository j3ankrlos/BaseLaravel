<div>
    <div class="row g-4 mb-4">
        <!-- Tarjetas de Resumen -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card card-body p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="avatar avatar-md bg-primary-subtle text-primary rounded-circle p-2" style="width: 48px; height: 48px; display: flex; align-items:center; justify-content:center;">
                        <i class="ph ph-users-three fs-3"></i>
                    </div>
                    <span class="badge bg-success-subtle text-success">Total</span>
                </div>
                <h6 class="text-muted mb-1 text-uppercase small fw-bold">Usuarios</h6>
                <h2 class="mb-0 fw-bold">{{ \App\Models\User::count() }}</h2>
            </div>
        </div>
        
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card card-body p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="avatar avatar-md bg-warning-subtle text-warning rounded-circle p-2" style="width: 48px; height: 48px; display: flex; align-items:center; justify-content:center;">
                        <i class="ph ph-piggy-bank fs-3"></i>
                    </div>
                    <span class="badge bg-warning-subtle text-warning">Próximamente</span>
                </div>
                <h6 class="text-muted mb-1 text-uppercase small fw-bold">Cerdos</h6>
                <h2 class="mb-0 fw-bold">0</h2>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card card-body p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="avatar avatar-md bg-danger-subtle text-danger rounded-circle p-2" style="width: 48px; height: 48px; display: flex; align-items:center; justify-content:center;">
                        <i class="ph ph-thermometer-hot fs-3"></i>
                    </div>
                    <span class="badge bg-danger-subtle text-danger">Alertas</span>
                </div>
                <h6 class="text-muted mb-1 text-uppercase small fw-bold">Salud</h6>
                <h2 class="mb-0 fw-bold">0</h2>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card card-body p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="avatar avatar-md bg-info-subtle text-info rounded-circle p-2" style="width: 48px; height: 48px; display: flex; align-items:center; justify-content:center;">
                        <i class="ph ph-currency-dollar fs-3"></i>
                    </div>
                    <span class="badge bg-info-subtle text-info">Ventas</span>
                </div>
                <h6 class="text-muted mb-1 text-uppercase small fw-bold">Ingresos</h6>
                <h2 class="mb-0 fw-bold">$0.00</h2>
            </div>
        </div>
    </div>

    <!-- Sección de Bienvenida -->
    <div class="card p-5 border-0 bg-white shadow-sm overflow-hidden position-relative">
        <div class="row align-items-center">
            <div class="col-md-7 position-relative">
                <h1 class="fw-bold text-dark display-5 mb-3">¡Hola, {{ Auth::user()->name }}!</h1>
                <p class="lead text-muted mb-4">Bienvenido al panel de control de la Granja Porcina. Aquí podrás gestionar tu producción, salud animal y personal de manera eficiente.</p>
                <a href="/users" class="btn btn-primary px-4 py-2 fw-semibold shadow-sm" wire:navigate>Gestionar Personal</a>
            </div>
            <div class="col-md-5 d-none d-md-block text-end">
                <i class="ph ph-buildings text-primary-subtle" style="font-size: 10rem; opacity: 0.2;"></i>
            </div>
        </div>
    </div>
</div>
