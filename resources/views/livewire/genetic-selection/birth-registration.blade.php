<div>
    {{-- Header Title --}}
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h4 class="mb-0 fw-bold text-dark"><i class="ph ph-hand-heart me-2 text-primary"></i> Registro de Partos del Día</h4>
            <p class="text-muted small mt-1 mb-0">Puedes agregar múltiples hembras antes de guardar.</p>
        </div>
        <div class="col-md-6 text-end">
            <a href="/genetic-selection/list" wire:navigate class="btn btn-light border fw-medium px-4 py-2 me-2">
                <i class="ph ph-list-bullets me-1"></i> Ver Listado
            </a>
        </div>
    </div>


    {{-- INPUT FORM CARD --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3 border-bottom">
            <h6 class="mb-0 fw-bold text-dark"><i class="ph ph-baby me-2 text-primary"></i> Datos del Parto</h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                {{-- FECHAS PIC --}}
                <div class="col-md-3">
                    <label class="form-label fw-bold">Fecha Calendario</label>
                    <input type="date" wire:model.live="calendar_date" class="form-control">
                    @error('calendar_date') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
                <div class="col-md-1">
                    <label class="form-label fw-bold">Vuelta</label>
                    <input type="number" wire:model.live="pic_cycle" class="form-control text-center fw-bold">
                    @error('pic_cycle') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold">Fecha PIC</label>
                    <input type="number" wire:model.live="pic_day" class="form-control text-center fw-bold" min="0" max="999">
                    @error('pic_day') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                {{-- SALA / JAULA --}}
                <div class="col-md-3">
                    <label class="form-label fw-bold">Sala</label>
                    <input type="text" wire:model="sala" class="form-control" placeholder="Ej. Sala 01">
                    @error('sala') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Jaula</label>
                    <input type="text" wire:model="jaula" class="form-control" placeholder="Ej. J-10">
                    @error('jaula') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                {{-- GENEALOGIA --}}
                <div class="col-md-3">
                    <label class="form-label fw-bold">Madre (ID/Tag)</label>
                    <input type="text" wire:model="madre" class="form-control" placeholder="Tag de la Madre">
                    @error('madre') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold">Paridad</label>
                    <input type="number" wire:model="paridad" class="form-control" min="1">
                    @error('paridad') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Padre (ID/Tag)</label>
                    <input type="text" wire:model="padre" class="form-control" placeholder="Tag del Padre">
                    @error('padre') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold">LNV</label>
                    <input type="number" wire:model="lnv" class="form-control" min="0">
                    @error('lnv') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                {{-- CANTIDAD antes que RAZA --}}
                <div class="col-md-2">
                    <label class="form-label fw-bold">Cantidad</label>
                    <input type="number" wire:model.live="cantidad" class="form-control" min="1" max="50">
                    @error('cantidad') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Genética (Raza)</label>
                    <select wire:model.live="genetica_id" class="form-select">
                        <option value="">Seleccione Raza...</option>
                        @foreach ($genetics as $g)
                            <option value="{{ $g->id }}">{{ $g->name }}</option>
                        @endforeach
                    </select>
                    @error('genetica_id') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                {{-- ULTIMO ID PREVIEW --}}
                @if($genetica_id && $ultimo_id !== null)
                <div class="col-md-2 d-flex align-items-end">
                    <div class="alert alert-light border py-2 mb-0 w-100 text-center">
                        <div class="small text-muted">Último ID</div>
                        <div class="fw-bold font-monospace text-primary">{{ $ultimo_id }}</div>
                    </div>
                </div>
                @endif

                {{-- RESPONSABLE --}}
                <div class="col-md-4">
                    <label class="form-label fw-bold">Responsable</label>
                    <select wire:model="responsable_id" class="form-select">
                        <option value="">Seleccione Responsable...</option>
                        @foreach ($employees as $e)
                            <option value="{{ $e->id }}">{{ $e->name }}</option>
                        @endforeach
                    </select>
                    @error('responsable_id') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
            </div>

            {{-- ADD TO PENDING BUTTON --}}
            <div class="mt-4 d-flex justify-content-end">
                <button type="button" wire:click="addToPending" class="btn btn-success fw-bold px-5 py-2 shadow-sm">
                    <i class="ph ph-plus-circle me-2"></i> Agregar Parto a la Planilla
                </button>
            </div>
        </div>
    </div>

    {{-- PENDING BIRTHS TABLE --}}
    @if(!empty($pending_births))
    <div class="card shadow-sm mb-4" style="border: 1px solid #dee2e6; border-left: 4px solid #198754;">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold text-success">
                <i class="ph ph-hourglass me-2"></i> Partos Pendientes de Guardar
                <span class="badge bg-success text-white ms-2 rounded-pill">{{ count($pending_births) }}</span>
            </h6>
            <button type="button" wire:click="saveAll" class="btn btn-primary fw-bold px-4 py-2 shadow-sm">
                <i class="ph ph-check-circle me-2"></i> Guardar Todo ({{ count($pending_births) }} parto{{ count($pending_births) > 1 ? 's' : '' }})
            </button>
        </div>
        <div class="card-body p-0">
            @foreach ($pending_births as $bi => $pending)
                <div class="border-bottom p-3">
                    {{-- Birth Header Summary --}}
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex flex-wrap gap-3">
                            <div><span class="text-muted small">PIC:</span> <strong class="font-monospace">{{ $pending['birth_header']['pic_cycle'] }}-{{ str_pad($pending['birth_header']['pic_day'], 3, '0', STR_PAD_LEFT) }}</strong></div>
                            <div><span class="text-muted small">Sala:</span> <strong>{{ $pending['birth_header']['room'] }}</strong></div>
                            <div><span class="text-muted small">Jaula:</span> <strong>{{ $pending['birth_header']['cage'] }}</strong></div>
                            <div><span class="text-muted small">Madre:</span> <span class="badge bg-light text-dark border font-monospace fw-bold px-3 py-2">{{ $pending['birth_header']['mother_tag'] }}</span></div>
                            <div><span class="text-muted small">Padre:</span> <span class="badge bg-light text-secondary border font-monospace px-3 py-2">{{ $pending['birth_header']['father_tag'] }}</span></div>
                            <div><span class="text-muted small">LNV:</span> <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3">{{ $pending['birth_header']['lnv'] }}</span></div>
                            <div><span class="text-muted small">Raza:</span> <strong class="text-primary">{{ $pending['birth_header']['genetic_name'] }}</strong></div>
                            <div><span class="text-muted small">Selección:</span> <strong class="text-indigo">{{ $pending['birth_header']['quantity'] }}</strong></div>
                        </div>
                        <button wire:click="removePending({{ $bi }})" wire:confirm="¿Eliminar este parto de la planilla?" class="btn btn-sm btn-light text-danger border-0" title="Quitar">
                            <i class="ph-bold ph-trash"></i>
                        </button>
                    </div>

                    {{-- Piglet Detail Table --}}
                    <div class="table-responsive rounded border bg-light">
                        <table class="table table-sm table-hover mb-0 align-middle">
                            <thead class="bg-white text-muted small text-uppercase fw-bold">
                                <tr>
                                    <th class="ps-3">#</th>
                                    <th>ID Oreja</th>
                                    <th>ID Arete</th>
                                    <th style="width:90px">Peso</th>
                                    <th title="N Pesones">N P.</th>
                                    <th>IZQ</th>
                                    <th>DTRZ IZQ.</th>
                                    <th>DTRZ DER.</th>
                                    <th style="width:110px">Sexo</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pending['piglets'] as $pi => $piglet)
                                    <tr>
                                        <td class="ps-3 text-muted small">{{ $pi + 1 }}</td>
                                        <td><span class="badge bg-white border text-dark font-monospace py-1 px-2">{{ $piglet['ear_id'] }}</span></td>
                                        <td><span class="badge bg-white border text-primary font-monospace py-1 px-2">{{ $piglet['generated_id'] }}</span></td>
                                        <td><input type="text" wire:model="pending_births.{{ $bi }}.piglets.{{ $pi }}.weight" class="form-control form-control-sm decimal-mask" placeholder="0,00"></td>
                                        <td><input type="number" wire:model="pending_births.{{ $bi }}.piglets.{{ $pi }}.teats_total" class="form-control form-control-sm @if($piglet['sex'] === 'Macho') bg-light text-muted @endif" @if($piglet['sex'] === 'Macho') disabled @endif></td>
                                        <td><input type="number" wire:model="pending_births.{{ $bi }}.piglets.{{ $pi }}.teats_left" class="form-control form-control-sm @if($piglet['sex'] === 'Macho') bg-light text-muted @endif" @if($piglet['sex'] === 'Macho') disabled @endif></td>
                                        <td><input type="number" wire:model="pending_births.{{ $bi }}.piglets.{{ $pi }}.teats_behind_shoulder_left" class="form-control form-control-sm @if($piglet['sex'] === 'Macho') bg-light text-muted @endif" @if($piglet['sex'] === 'Macho') disabled @endif></td>
                                        <td><input type="number" wire:model="pending_births.{{ $bi }}.piglets.{{ $pi }}.teats_behind_shoulder_right" class="form-control form-control-sm @if($piglet['sex'] === 'Macho') bg-light text-muted @endif" @if($piglet['sex'] === 'Macho') disabled @endif></td>
                                        <td>
                                            <select wire:model="pending_births.{{ $bi }}.piglets.{{ $pi }}.sex" class="form-select form-select-sm">
                                                <option value="Hembra">Hembra</option>
                                                <option value="Macho">Macho</option>
                                                <option value="Mixto">Mixto</option>
                                            </select>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="card-footer bg-white text-end py-3">
            <button type="button" wire:click="saveAll" class="btn btn-primary fw-bold px-5 py-2 shadow-sm">
                <i class="ph ph-check-circle me-2"></i> Guardar Todo ({{ count($pending_births) }} parto{{ count($pending_births) > 1 ? 's' : '' }})
            </button>
        </div>
    </div>
    @endif

    <style>
        .text-indigo { color: #6366f1; }
        .border-4 { border-width: 4px !important; }
    </style>
</div>
