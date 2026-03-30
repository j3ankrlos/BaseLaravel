<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-0">Vista General de Inventario</h2>
            <p class="text-muted small mb-0">Visualización detallada de animales vivos</p>
        </div>
        <div class="d-flex gap-2">
            <button wire:click="clearFilters" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="ph ph-arrow-counter-clockwise me-1"></i> Limpiar Filtros
            </button>
            <div class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-2 border border-primary border-opacity-25">
                Total: {{ $animals->total() }} Registros
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
        <div class="card-body p-4 bg-light bg-opacity-50">
            <div class="row g-3">
                <!-- Search -->
                <div class="col-md-4">
                    <label class="form-label smallest fw-bold text-uppercase text-muted">Buscador Inteligente</label>
                    <div class="input-group shadow-none">
                        <span class="input-group-text bg-white border-end-0 border-primary border-opacity-10 py-0 px-3 text-primary">
                            <i class="ph ph-magnifying-glass fw-bold"></i>
                        </span>
                        <input type="text" wire:model.live.debounce.300ms="search" 
                               class="form-control border-start-0 border-primary border-opacity-10 fw-medium shadow-none" 
                               placeholder="Buscar por ID, Lote o SAP...">
                    </div>
                </div>

                <!-- Barn Filter -->
                <div class="col-md-2">
                    <label class="form-label smallest fw-bold text-uppercase text-muted">Filtrar por Nave</label>
                    <select wire:model.live="f_nave_id" class="form-select border-primary border-opacity-10 shadow-none fw-medium">
                        <option value="">Todas las Naves</option>
                        @foreach($barns as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Genetic Filter -->
                <div class="col-md-2">
                    <label class="form-label smallest fw-bold text-uppercase text-muted">Raza / Genética</label>
                    <select wire:model.live="f_genetic_id" class="form-select border-primary border-opacity-10 shadow-none fw-medium">
                        <option value="">Todas las Razas</option>
                        @foreach($genetics as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Feed Type Filter -->
                <div class="col-md-2">
                    <label class="form-label smallest fw-bold text-uppercase text-muted">Tipo de Alimento</label>
                    <select wire:model.live="f_feed_type" class="form-select border-primary border-opacity-10 shadow-none fw-medium">
                        <option value="">Cualquier Alimento</option>
                        @foreach($feedTypes as $type)
                            <option value="{{ $type }}">{{ $type }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Per Page -->
                <div class="col-md-2">
                    <label class="form-label smallest fw-bold text-uppercase text-muted">Mostrar</label>
                    <select wire:model.live="perPage" class="form-select border-primary border-opacity-10 shadow-none fw-medium">
                        <option value="15">15 registros</option>
                        <option value="30">30 registros</option>
                        <option value="50">50 registros</option>
                        <option value="100">100 registros</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Section -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive" style="overflow-x: auto; width: 100%;">
            <table class="table table-hover align-middle mb-0 custom-inventory-table" style="min-width: 1600px;">
                <thead class="bg-primary bg-opacity-10 text-primary">
                    <tr>
                        <th class="border-0 py-3 text-center">INV</th>
                        <th class="border-0 px-2 py-3 cursor-pointer" wire:click="sort('entry_date')">
                            F. INICIO @if($sortBy === 'entry_date') <i class="ph ph-caret-{{ $sortDir === 'asc' ? 'up' : 'down' }} fw-bold ms-1"></i> @endif
                        </th>
                        <th class="border-0 py-3">ORIGEN</th>
                        <th class="border-0 py-3 text-center">EDAD</th>
                        <th class="border-0 py-3 cursor-pointer" wire:click="sort('management_lot')">
                            LOTE @if($sortBy === 'management_lot') <i class="ph ph-caret-{{ $sortDir === 'asc' ? 'up' : 'down' }} fw-bold ms-1"></i> @endif
                        </th>
                        <th class="border-0 py-3 cursor-pointer" wire:click="sort('internal_id')">
                            I-D @if($sortBy === 'internal_id') <i class="ph ph-caret-{{ $sortDir === 'asc' ? 'up' : 'down' }} fw-bold ms-1"></i> @endif
                        </th>
                        <th class="border-0 py-3">RAZA</th>
                        <th class="border-0 py-3">SEXO</th>
                        <th class="border-0 py-3">LOTE SAP</th>
                        <th class="border-0 py-3 text-center">ACT. CURSO</th>
                        <th class="border-0 py-3 text-center">ORDEN</th>
                        <th class="border-0 py-3 text-center">PESO</th>
                        <th class="border-0 py-3">NAVE</th>
                        <th class="border-0 py-3">SECCIÓN</th>
                        <th class="border-0 py-3">CORRAL</th>
                        <th class="border-0 py-3 text-center">GRANJA</th>
                        <th class="border-0 py-3">ALIMENTO</th>
                    </tr>
                </thead>
                <tbody class="bg-white">
                    @forelse($animals as $animal)
                        <tr class="inventory-row">
                            <td class="text-center inventory-data highlighted-qty">{{ $animal->quantity }}</td>
                            <td class="inventory-data">{{ $animal->entry_date?->format('d/m/y') ?? '---' }}</td>
                            <td class="inventory-data text-nowrap">{{ $animal->source ?? '---' }}</td>
                            <td class="text-center inventory-data">
                                @php
                                    $lotNum = is_numeric($animal->management_lot) ? (int)$animal->management_lot : 0;
                                    $calculatedAge = 0;
                                    if ($lotNum > 0) {
                                        $calculatedAge = (int)$currentPic - $lotNum;
                                        if ($calculatedAge < 0) $calculatedAge += 1000;
                                    }
                                @endphp
                                {{ $calculatedAge }}
                            </td>
                            <td class="text-center inventory-data">{{ $animal->management_lot ?? '---' }}</td>
                            <td class="inventory-data">{{ $animal->internal_id ?? '0' }}</td>
                            <td class="inventory-data fw-bold">{{ $animal->genetic?->name ?? 'F1' }}</td>
                            <td class="inventory-data">{{ $animal->sex ?? 'HEMBRA' }}</td>
                            <td class="inventory-data text-nowrap"><code class="text-dark">{{ $animal->lote_sap ?: ($animal->source . 'EXP' . $calculatedAge . ($animal->internal_id ?: '0')) }}</code></td>
                            <td class="text-center inventory-data">{{ $animal->activo_excel ?? '---' }}</td>
                            <td class="text-center inventory-data">{{ $animal->order_number ?? '---' }}</td>
                            <td class="text-center inventory-data fw-bold">{{ number_format($animal->weight, 2, ',', '.') }}</td>
                            <td class="inventory-data text-uppercase">{{ $animal->nave?->name ?? '---' }}</td>
                            <td class="inventory-data text-uppercase">{{ $animal->seccion?->name ?? '---' }}</td>
                            <td class="inventory-data text-center fw-bold">{{ $animal->corral ?? '---' }}</td>
                            <td class="text-center inventory-data"><span class="badge bg-light text-dark border">{{ $animal->farm ?? 'EXP' }}</span></td>
                            <td class="inventory-data text-uppercase fw-bold text-primary" style="font-size: 0.8rem;">
                                {{ $animal->feed_type ?? '---' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="17" class="text-center py-5">
                                <div class="py-4">
                                    <i class="ph ph-file-search display-1 text-light"></i>
                                    <h5 class="text-muted mt-3">No se encontraron animales activos</h5>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer bg-white border-top-0 p-4">
            {{ $animals->links() }}
        </div>
    </div>

    <style>
        .custom-inventory-table {
            border-collapse: separate;
            border-spacing: 0;
        }
        
        .custom-inventory-table thead th {
            font-size: 0.75rem;
            letter-spacing: 0.05rem;
            text-transform: uppercase;
            font-weight: 700;
            color: #495057;
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6 !important;
            padding: 12px 8px;
            white-space: nowrap;
        }

        .inventory-row {
            transition: background-color 0.2s;
        }

        .inventory-row:hover {
            background-color: #f1f4f9 !important;
        }

        .inventory-data {
            font-size: 0.85rem;
            color: #333;
            padding: 12px 8px !important;
            border-bottom: 1px solid #edf2f7;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }

        .highlighted-qty {
            font-weight: 700;
            color: #0d6efd;
        }

        .cursor-pointer { cursor: pointer; }
        .smallest { font-size: 0.7rem; }
    </style>
</div>
