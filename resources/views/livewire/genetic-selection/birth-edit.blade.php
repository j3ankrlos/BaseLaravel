<div>
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <a href="/genetic-selection/list" wire:navigate class="text-muted small d-block mb-1">
                <i class="ph ph-arrow-left me-1"></i> Volver al Listado
            </a>
            <h4 class="mb-0 fw-bold text-dark">
                <i class="ph ph-pencil me-2 text-primary"></i> Editar Registro de Parto
            </h4>
        </div>
    </div>


    <form wire:submit.prevent="save">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold">Datos Generales del Parto</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Fecha Calendario</label>
                        <input type="date" wire:model.live="calendar_date" class="form-control">
                        @error('calendar_date') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-1">
                        <label class="form-label fw-bold">Vuelta</label>
                        <input type="number" wire:model.live="pic_cycle" class="form-control text-center">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold">Fecha PIC</label>
                        <input type="number" wire:model.live="pic_day" class="form-control text-center" min="0" max="999">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Sala</label>
                        <input type="text" wire:model="sala" class="form-control">
                        @error('sala') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Jaula</label>
                        <input type="text" wire:model="jaula" class="form-control">
                        @error('jaula') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold">Madre (ID/Tag)</label>
                        <input type="text" wire:model="madre" class="form-control">
                        @error('madre') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold">Paridad</label>
                        <input type="number" wire:model="paridad" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Padre (ID/Tag)</label>
                        <input type="text" wire:model="padre" class="form-control">
                        @error('padre') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold">LNV</label>
                        <input type="number" wire:model="lnv" class="form-control">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold">Lote Maternidad</label>
                        <input type="text" wire:model="lote_maternidad" class="form-control" placeholder="Ej. 880">
                        @error('lote_maternidad') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold">PIC Destete</label>
                        <input type="number" wire:model="pic_destete" class="form-control" placeholder="Ej. 908">
                        @error('pic_destete') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold">Responsable</label>
                        <select wire:model="responsable_id" class="form-select">
                            <option value="">Seleccione...</option>
                            @foreach ($employees as $e)
                                <option value="{{ $e->id }}">{{ $e->name }}</option>
                            @endforeach
                        </select>
                        @error('responsable_id') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold">Detalle de Lechones</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-sm align-middle mb-0">
                        <thead class="bg-light text-muted small text-uppercase fw-bold">
                            <tr>
                                <th class="ps-3">ID Oreja</th>
                                <th>ID Arete</th>
                                <th style="width:100px">Peso (Kg)</th>
                                <th title="N Pesones">N Pes.</th>
                                <th>IZQ</th>
                                <th>DTRZ IZQ.</th>
                                <th>DTRZ DER.</th>
                                <th style="width:120px">Sexo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($piglets as $index => $piglet)
                                <tr>
                                    <td class="ps-3"><span class="badge bg-white text-dark border font-monospace py-2">{{ $piglet['ear_id'] }}</span></td>
                                    <td><span class="badge bg-light text-primary border font-monospace py-2">{{ $piglet['generated_id'] }}</span></td>
                                    <td><input type="text" wire:model="piglets.{{ $index }}.weight" class="form-control form-control-sm decimal-mask" placeholder="0,00"></td>
                                    <td><input type="number" wire:model="piglets.{{ $index }}.teats_total" class="form-control form-control-sm @if($piglet['sex'] === 'Macho') bg-light text-muted @endif" @if($piglet['sex'] === 'Macho') disabled @endif></td>
                                    <td><input type="number" wire:model="piglets.{{ $index }}.teats_left" class="form-control form-control-sm @if($piglet['sex'] === 'Macho') bg-light text-muted @endif" @if($piglet['sex'] === 'Macho') disabled @endif></td>
                                    <td><input type="number" wire:model="piglets.{{ $index }}.teats_behind_shoulder_left" class="form-control form-control-sm @if($piglet['sex'] === 'Macho') bg-light text-muted @endif" @if($piglet['sex'] === 'Macho') disabled @endif></td>
                                    <td><input type="number" wire:model="piglets.{{ $index }}.teats_behind_shoulder_right" class="form-control form-control-sm @if($piglet['sex'] === 'Macho') bg-light text-muted @endif" @if($piglet['sex'] === 'Macho') disabled @endif></td>
                                    <td>
                                        <select wire:model="piglets.{{ $index }}.sex" class="form-select form-select-sm">
                                            <option value="Hembra">Hembra</option>
                                            <option value="Macho">Macho</option>
                                            <option value="Mixto">Mixto</option>
                                        </select>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center py-4 text-muted">Sin lechones registrados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between">
            <a href="/genetic-selection/list" wire:navigate class="btn btn-light border fw-bold px-4 py-2">
                <i class="ph ph-x-circle me-1"></i> Cancelar
            </a>
            <button type="submit" class="btn btn-primary fw-bold px-5 py-2 shadow-sm">
                <i class="ph ph-floppy-disk me-2"></i> Guardar Cambios
            </button>
        </div>
    </form>
</div>
