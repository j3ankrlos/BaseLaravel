<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Solicitudes</h2>
    </div>

    <!-- Buscador Integrado -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="input-group">
                <span class="input-group-text bg-light"><i class="ph ph-magnifying-glass"></i></span>
                <input type="text" class="form-control" placeholder="Buscar solicitudes por folio o contenido de la solicitud..." wire:model.live.debounce.300ms="search">
            </div>
        </div>
    </div>

    <!-- Tabla Solicitudes Header -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Folio</th>
                            <th>Fecha</th>
                            <th>Solicitante</th>
                            <th class="text-center">Cant. Ítems</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($requests as $req)
                            <tr>
                                <td>
                                    <span class="badge bg-primary fs-6">{{ $req->folio ?? 'N/A' }}</span>
                                </td>
                                <td>{{ $req->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <div class="fw-bold text-dark small"><i class="ph ph-user text-muted"></i> {{ $req->solicitante->name ?? 'Sistema' }}</div>
                                </td>
                                <td class="text-center fw-bold text-secondary fs-6">{{ $req->details->count() }} ítems</td>
                                <td class="text-center">
                                    @if($req->estado === 'pendiente')
                                        <span class="badge bg-warning text-dark"><i class="ph ph-clock"></i> Pendiente</span>
                                    @elseif($req->estado === 'aprobada')
                                        <span class="badge bg-success mb-1"><i class="ph ph-check-circle"></i> Aprobada</span>
                                        <div class="small fw-light text-muted" style="font-size: 0.65rem;">Por: {{ $req->aprobador->name ?? 'Sistema' }}</div>
                                    @else
                                        <span class="badge bg-danger mb-1"><i class="ph ph-x-circle"></i> Rechazada</span>
                                        <div class="small fw-light text-muted" style="font-size: 0.65rem;">Por: {{ $req->aprobador->name ?? 'Sistema' }}</div>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($req->estado === 'pendiente')
                                        <button class="btn btn-sm btn-primary fw-bold" wire:click="manageRequest({{ $req->id }})">
                                            Gestionar <i class="ph ph-arrow-right"></i>
                                        </button>
                                    @else
                                        <button class="btn btn-sm btn-outline-secondary" disabled>
                                            <i class="ph ph-check-square"></i> Procesada
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="ph ph-envelope-simple-open fs-1 d-block mb-2"></i>
                                    No hay solicitudes de traslado actualmente.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($requests->hasPages())
        <div class="card-footer bg-white pt-3 pb-1 border-top-0">
            {{ $requests->links() }}
        </div>
        @endif
    </div>

    <!-- Modal Gestión Aprobación Detalles -->
    <div class="modal fade" id="manageModal" tabindex="-1" wire:ignore.self aria-labelledby="manageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="manageModalLabel"><i class="ph ph-check-square me-2"></i> Gestionar Solicitud: {{ $requestFolio }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 pb-0">

                    @if($requestComentarios)
                        <div class="alert alert-info border-0 bg-info-subtle text-info small mb-4">
                            <i class="ph ph-chat-text me-2 fs-5"></i> <strong>Comentario del Solicitante:</strong> {{ $requestComentarios }}
                        </div>
                    @endif

                    <div class="table-responsive border rounded-3 bg-white mb-3" style="max-height: 50vh; overflow-y: auto;">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light position-sticky top-0 shadow-sm" style="z-index: 1;">
                                <tr>
                                    <th style="width: 5%" class="text-center py-2">#</th>
                                    <th style="width: 15%">Código</th>
                                    <th style="width: 35%">Producto</th>
                                    <th style="width: 15%" class="text-center">Stock A002</th>
                                    <th style="width: 15%" class="text-center">Solicitado</th>
                                    <th style="width: 15%" class="text-center">A Aprobar <span class="text-danger">*</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($requestDetails as $index => $detail)
                                    <tr>
                                        <td class="text-center fw-bold text-muted small">{{ $index + 1 }}</td>
                                        <td><span class="badge bg-light text-dark border">{{ $detail['Codigo'] }}</span></td>
                                        <td class="text-wrap small fw-medium">{{ $detail['Producto'] }}</td>
                                        <td class="text-center">
                                            @php $stock = $approvalData[$detail['id']]['stock_a002'] ?? 0; @endphp
                                            <span class="fw-bold {{ $stock < $detail['cantidad_solicitada'] ? 'text-danger' : 'text-success' }}">
                                                {{ number_format($stock, 3, ',', '.') }}
                                            </span>
                                            <small class="text-muted d-block" style="font-size: 0.6rem;">{{ $detail['UMB'] }}</small>
                                        </td>
                                        <td class="text-center fw-bold text-primary">
                                            {{ number_format($detail['cantidad_solicitada'], 3, ',', '.') }}
                                        </td>
                                        <td>
                                            <div class="input-group input-group-sm">
                                                <input type="number" step="0.01" min="0" max="{{ $stock }}" class="form-control fw-bold border-primary text-end" wire:model="approvalData.{{ $detail['id'] }}.cantidad_aprobada" required {{ $stock <= 0 ? 'readonly disabled' : '' }}>
                                                <span class="input-group-text bg-light small">{{ $detail['UMB'] }}</span>
                                            </div>
                                            @error('approvalData.'.$detail['id'].'.cantidad_aprobada') 
                                                <span class="text-danger fw-bold d-block mt-1" style="font-size: 0.70rem;">{{ $message }}</span> 
                                            @enderror
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer justify-content-between bg-light border-0 py-3">
                    <button type="button" class="btn btn-outline-danger" wire:click="rejectRequest({{ $requestId }})">
                        <i class="ph ph-x-circle me-1"></i> Rechazar Solicitud
                    </button>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-primary px-4 fw-bold shadow-sm" wire:click="approveRequest">
                            <span wire:loading.remove wire:target="approveRequest">
                                <i class="ph ph-check-circle me-1"></i> Procesar y Aprobar
                            </span>
                            <span wire:loading wire:target="approveRequest">
                                <i class="ph ph-spinner ph-spin me-1"></i> Procesando...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
