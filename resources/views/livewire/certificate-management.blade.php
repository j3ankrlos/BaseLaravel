<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>Certificados Médico Veterinarios</h2>
            <div class="text-muted small">Listado histórico de certificados emitidos</div>
        </div>
        <a href="/certificates/create" wire:navigate class="btn btn-primary shadow-sm px-4">
            <i class="ph ph-plus-circle me-1"></i> Crear Nuevo Certificado
        </a>
    </div>

    <!-- Buscador Integrado -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body py-3 px-4">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="input-group input-group-merge">
                        <span class="input-group-text bg-white border-end-0"><i class="ph ph-magnifying-glass text-muted"></i></span>
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0 ps-0" placeholder="Buscar por ID del animal, lote, raza...">
                    </div>
                </div>
                <div class="col-md-6 text-md-end">
                    <span class="small text-muted">Mostrando {{ $certificates->count() }} resultados de {{ $certificates->total() }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Fecha Emisión</th>
                        <th>ID Animal</th>
                        <th>Lote</th>
                        <th>Raza</th>
                        <th>Peso (kg)</th>
                        <th>Causa Muerte</th>
                        <th>Veterinario</th>
                        <th class="text-end pe-4">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($certificates as $cert)
                        <tr>
                            <td class="ps-4">
                                <span class="fw-semibold">{{ \Carbon\Carbon::parse($cert->fecha_registro)->format('d/m/Y') }}</span>
                            </td>
                            <td>
                                <span class="badge bg-secondary-subtle text-secondary px-2 py-1 mb-0">{{ $cert->animal_id }}</span>
                            </td>
                            <td>{{ $cert->lote }}</td>
                            <td>{{ $cert->raza }}</td>
                            <td>{{ number_format($cert->peso, 2, ',', '.') }}</td>
                            <td>
                                <div class="text-truncate" style="max-width: 15rem;" title="{{ $cert->causa_muerte }}">
                                    {{ $cert->causa_muerte }}
                                </div>
                                <small class="text-muted d-block">{{ $cert->tipo_muerte }}</small>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle-sm bg-primary-subtle text-primary me-2">
                                        {{ substr($cert->vet_nombre, 0, 1) }}{{ substr($cert->vet_apellido, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="small fw-bold">{{ $cert->vet_nombre }} {{ $cert->vet_apellido }}</div>
                                        <div class="text-muted" style="font-size: 0.7rem;">Cédula: {{ $cert->vet_cedula }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group shadow-sm">
                                    <button class="btn btn-sm btn-white border" title="Ver detalle">
                                        <i class="ph ph-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-white border" title="Descargar PDF">
                                        <i class="ph ph-file-pdf"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="py-4">
                                    <i class="ph ph-inbox fs-1 d-block mb-3 opacity-25"></i>
                                    <h5 class="text-muted">No se encontraron certificados para "{{ $search }}"</h5>
                                    @if($search)
                                        <button wire:click="$set('search', '')" class="btn btn-link btn-sm">Limpiar búsqueda</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($certificates->hasPages())
            <div class="card-footer bg-white border-top-0 py-3">
                {{ $certificates->links() }}
            </div>
        @endif
    </div>

    <style>
        .avatar-circle-sm {
            width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.8rem;
        }
    </style>
</div>
