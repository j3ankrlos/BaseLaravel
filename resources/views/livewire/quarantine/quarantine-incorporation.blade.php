<div class="container-fluid py-4">
    <div class="row mb-4 animate__animated animate__fadeIn">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white border-0 py-4 px-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="fw-bold mb-1 text-dark">
                                <i class="ph-fill ph-shield-check me-2 text-primary"></i>
                                Incorporación a Producción
                            </h4>
                            <p class="text-muted small mb-0">Promoción de animales de importación al inventario activo (Pubertad / Stud)</p>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fw-bold">
                                {{ now()->format('d/m/Y') }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="card-body p-4">
                    <form wire:submit.prevent="processIncorporation">
                        <div class="row g-4">
                            <!-- ORIGEN: Lote de Importación -->
                            <div class="col-lg-7">
                                <div class="p-4 bg-light rounded-4 border h-100">
                                    <h6 class="fw-bold text-dark mb-4 mt-0">
                                        <i class="ph ph-magnifying-glass me-2"></i> 1. Selección de Lote de Importación
                                    </h6>
                                    
                                    <div class="row g-3 align-items-end mb-4">
                                        <div class="col-md-12">
                                            <label class="form-label fw-bold small">Lote de Importación (Origen)</label>
                                            <select wire:model.live="q_batch_id" class="form-select shadow-none border-primary border-opacity-25 bg-white fw-bold">
                                                <option value="">Seleccione Importación...</option>
                                                @foreach($q_batches as $qb)
                                                    <option value="{{ $qb->id }}">
                                                        {{ $qb->entry_date->format('d/m/Y') }} - {{ $qb->origin }} ({{ $qb->document_number }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('q_batch_id') <span class="text-danger small">{{ $message }}</span> @enderror
                                        </div>
                                    </div>

                                    @if($q_batch_id)
                                        <div class="table-responsive border rounded-3 bg-white" style="max-height: 400px; overflow-y: auto;">
                                            <table class="table table-sm table-hover align-middle mb-0">
                                                <thead class="sticky-top bg-white shadow-sm">
                                                    <tr>
                                                        <th class="text-center py-2" width="40">#</th>
                                                        <th class="py-2">Arete Local</th>
                                                        <th class="py-2">ID Oficial</th>
                                                        <th class="py-2">Lote (PIC)</th>
                                                        <th class="py-2">Raza</th>
                                                        <th class="text-center py-2" width="60">Sel.</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="small text-uppercase">
                                                    @forelse($q_items as $qi)
                                                        <tr class="{{ in_array($qi['id'], $q_selected_items) ? 'table-primary' : '' }}">
                                                            <td class="text-center text-muted">{{ $loop->iteration }}</td>
                                                            <td class="fw-bold">{{ $qi['internal_id'] }}</td>
                                                            <td class="text-muted small">{{ $qi['official_id'] ?? '-' }}</td>
                                                            <td><span class="badge bg-secondary-subtle text-secondary fw-bold">{{ $qi['lote'] ?? '-' }}</span></td>
                                                            <td>{{ $qi['genetic']['name'] ?? '-' }}</td>
                                                            <td class="text-center">
                                                                <input type="checkbox" wire:model.live="q_selected_items" value="{{ $qi['id'] }}" class="form-check-input">
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="6" class="text-center py-5 text-muted">No hay animales pendientes en este lote.</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="d-flex justify-content-between p-2 mt-2 bg-white rounded-3 border">
                                            <div class="text-muted small">Pendientes: <span class="fw-bold">{{ count($q_items) }}</span></div>
                                            <div class="small fw-bold text-primary">Seleccionados: {{ count($q_selected_items) }}</div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- DESTINO Y LOGISTICA -->
                            <div class="col-lg-5">
                                <div class="p-4 bg-white rounded-4 border h-100 shadow-sm">
                                    <h6 class="fw-bold text-dark mb-4 mt-0">
                                        <i class="ph ph-map-pin me-2"></i> 2. Ubicación de Destino
                                    </h6>

                                    <div class="mb-4">
                                        <label class="form-label fw-bold small">Peso Promedio (Kg)</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0"><i class="ph ph-scales"></i></span>
                                            <input type="text" wire:model="i_weight" class="form-control border-start-0 decimal-mask fw-bold text-center" placeholder="0,00">
                                        </div>
                                        @error('i_weight') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-bold small">Nave / Galpón</label>
                                        <select wire:model.live="i_nave_id" class="form-select fw-bold @error('i_nave_id') is-invalid @enderror">
                                            <option value="">Seleccione Nave...</option>
                                            @foreach($barns as $b)
                                                <option value="{{ $b->id }}">{{ $b->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-bold small">Sección</label>
                                        <select wire:model.live="i_seccion_id" class="form-select fw-bold @error('i_seccion_id') is-invalid @enderror" @if(!$i_nave_id) disabled @endif>
                                            <option value="">Seleccione Sección...</option>
                                            @foreach($barnSections as $bs)
                                                <option value="{{ $bs->id }}">{{ $bs->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label fw-bold small">Corral (Número)</label>
                                        <input type="number" wire:model="i_corral" class="form-control fw-bold @error('i_corral') is-invalid @enderror" placeholder="Ej. 1" @if(!$i_seccion_id) disabled @endif>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label fw-bold small">Alimento Sugerido</label>
                                        <select wire:model="i_feed_type" class="form-select fw-bold bg-primary bg-opacity-10 text-primary border-primary border-opacity-25">
                                            <option value="LECHONA II">LECHONA II</option>
                                            <option value="LECHONA I">LECHONA I</option>
                                        </select>
                                    </div>

                                    <hr class="my-4 opacity-50">

                                    <button type="submit" class="btn btn-primary w-100 py-3 rounded-pill fw-bold shadow-sm" @if(empty($q_selected_items)) disabled @endif>
                                        <i class="ph ph-check-circle me-2"></i> Confirmar Incorporación
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
