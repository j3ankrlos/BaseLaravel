<div>

    {{-- FILA SUPERIOR: TARJETAS DE ESTADÍSTICAS --}}
    <div class="row g-3 mb-4">

        {{-- Inventario A002 --}}
        <div class="col-6 col-xl-3">
            <a href="/warehouse/a002" wire:navigate class="text-decoration-none">
                <div class="stat-card" style="background: linear-gradient(135deg,#1a73e8,#0d47a1);">
                    <div class="stat-card-label">Inventario A002</div>
                    <div class="stat-card-value">{{ $statsA002 }}</div>
                    <div class="stat-card-sub">Productos Totales</div>
                    <i class="ph ph-hard-drive stat-card-icon"></i>
                </div>
            </a>
        </div>

        {{-- Inventario A006 --}}
        <div class="col-6 col-xl-3">
            <a href="/warehouse/a006" wire:navigate class="text-decoration-none">
                <div class="stat-card" style="background: linear-gradient(135deg,#2e7d32,#1b5e20);">
                    <div class="stat-card-label">Inventario A006</div>
                    <div class="stat-card-value">{{ $statsA006 }}</div>
                    <div class="stat-card-sub">Productos Totales</div>
                    <i class="ph ph-stack stat-card-icon"></i>
                </div>
            </a>
        </div>

        {{-- Solicitudes Pendientes --}}
        <div class="col-6 col-xl-3">
            <a href="/warehouse/requests" wire:navigate class="text-decoration-none">
                <div class="stat-card" style="background: linear-gradient(135deg,#f59e0b,#b45309);">
                    <div class="stat-card-label">Solicitudes Pendientes</div>
                    <div class="stat-card-value">{{ $solicitudesPendientes }}</div>
                    <div class="stat-card-sub">Requieren Aprobación</div>
                    <i class="ph ph-paper-plane-right stat-card-icon"></i>
                </div>
            </a>
        </div>

        {{-- Alertas Stock A006 --}}
        <div class="col-6 col-xl-3">
            <a href="/warehouse/a006" wire:navigate class="text-decoration-none">
                <div class="stat-card" style="background: linear-gradient(135deg,#dc2626,#7f1d1d);">
                    <div class="stat-card-label">Alertas Stock A006</div>
                    <div class="stat-card-value">{{ $alertasA006 }}</div>
                    <div class="stat-card-sub">Productos Críticos</div>
                    <i class="ph ph-warning stat-card-icon"></i>
                </div>
            </a>
        </div>

        {{-- Personal Activo --}}
        <div class="col-6 col-xl-3">
            <a href="/employees" wire:navigate class="text-decoration-none">
                <div class="stat-card" style="background: linear-gradient(135deg,#0891b2,#0c4a6e);">
                    <div class="stat-card-label">Personal Activo</div>
                    <div class="stat-card-value">{{ $statsEmployees }}</div>
                    <div class="stat-card-sub">Empleados Registrados</div>
                    <i class="ph ph-users-three stat-card-icon"></i>
                </div>
            </a>
        </div>

    </div>

    {{-- FILA INFERIOR: ACTIVIDAD RECIENTE + ACCESOS DIRECTOS --}}
    <div class="row g-3">

        {{-- Actividad Reciente --}}
        <div class="col-xl-8">
            <div class="card h-100">
                <div class="card-header bg-white border-0 pt-3 pb-2 px-3 d-flex align-items-center justify-content-between">
                    <span class="fw-bold text-dark"><i class="ph ph-clock-counter-clockwise text-primary me-1"></i> Actividad Reciente</span>
                    <a href="/warehouse/requests" wire:navigate class="btn btn-sm btn-outline-primary">Ver todo</a>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @forelse ($recentRequests as $req)
                            <li class="list-group-item px-3 py-2 d-flex align-items-center justify-content-between border-bottom">
                                <div class="d-flex align-items-start gap-2">
                                    <div class="mt-1">
                                        @if($req->estado === 'pendiente')
                                            <span class="badge bg-warning-subtle text-warning"><i class="ph ph-clock"></i></span>
                                        @elseif($req->estado === 'aprobada')
                                            <span class="badge bg-success-subtle text-success"><i class="ph ph-check-circle"></i></span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger"><i class="ph ph-x-circle"></i></span>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="fw-semibold" style="font-size:0.8rem;">
                                            Solicitud <span class="text-primary">{{ $req->folio }}</span>
                                            <span class="badge ms-1
                                                @if($req->estado === 'pendiente') bg-warning text-dark
                                                @elseif($req->estado === 'aprobada') bg-success
                                                @else bg-danger @endif">
                                                {{ ucfirst($req->estado) }}
                                            </span>
                                        </div>
                                        <div class="text-muted" style="font-size:0.73rem;">
                                            <i class="ph ph-user"></i> {{ $req->solicitante->name ?? 'Sistema' }}
                                            &bull; {{ $req->details->count() }} ítem(s)
                                            @if($req->aprobador)
                                                &bull; Aprobado por: {{ $req->aprobador->name }}
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <span class="text-muted small text-nowrap ms-2">{{ $req->created_at->diffForHumans() }}</span>
                            </li>
                        @empty
                            <li class="list-group-item text-center py-5 text-muted">
                                <i class="ph ph-inbox fs-1 d-block mb-2"></i>
                                No hay actividad reciente.
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        {{-- Accesos Directos Dinámicos (Ranking de más usados) --}}
        <div class="col-xl-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-header bg-white border-0 pt-3 pb-0 px-3 d-flex align-items-center justify-content-between">
                    <span class="fw-bold text-dark"><i class="ph ph-lightning text-warning me-1"></i> Accesos Directos</span>
                    <span class="badge bg-light text-muted fw-normal" style="font-size:0.65rem;">Top más usados</span>
                </div>
                <div class="card-body d-flex flex-column gap-2">
                    @foreach ($topModules as $module)
                        <a href="{{ $module->url }}" wire:navigate class="shortcut-btn">
                            <i class="ph {{ $module->icon }} {{ $module->color_class }}"></i>
                            <span>{{ $module->display_name }}</span>
                            <i class="ph ph-arrow-right ms-auto text-muted small"></i>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

    </div>

</div>

<style>
/* Tarjetas de Estadística estilo dashboard moderno */
.stat-card {
    border-radius: 0.75rem;
    padding: 1.1rem 1.2rem;
    color: #fff;
    position: relative;
    overflow: hidden;
    min-height: 110px;
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    transition: transform 0.15s, box-shadow 0.15s;
    box-shadow: 0 4px 15px rgba(0,0,0,0.15);
    cursor: pointer;
}
.stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(0,0,0,0.2); }
.stat-card-label { font-size: 0.72rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; opacity: 0.85; margin-bottom: 0.15rem; }
.stat-card-value { font-size: 2.2rem; font-weight: 800; line-height: 1; }
.stat-card-sub { font-size: 0.7rem; opacity: 0.75; margin-top: 0.2rem; }
.stat-card-icon { position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); font-size: 3.5rem; opacity: 0.15; }

/* Accesos Directos */
.shortcut-btn {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    padding: 0.6rem 0.85rem;
    border-radius: 0.5rem;
    border: 1px solid #e5e7eb;
    background: #fff;
    color: #374151;
    text-decoration: none;
    font-size: 0.82rem;
    font-weight: 500;
    transition: background 0.15s, border-color 0.15s, transform 0.1s;
}
.shortcut-btn:hover { background: #f8faff; border-color: #93c5fd; color: #1d4ed8; transform: translateX(3px); }
.shortcut-btn i:first-child { font-size: 1.1rem; }
</style>
