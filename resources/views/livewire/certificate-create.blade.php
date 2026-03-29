<div>
    <style>
        .certificate-form .form-control, 
        .certificate-form .form-select,
        .certificate-form .input-group-text {
            font-size: 0.85rem;
            padding: 0.4rem 0.75rem;
        }
        .certificate-form .form-label {
            font-size: 0.75rem !important;
            margin-bottom: 0.25rem;
        }
        .certificate-form .card-header h5 {
            font-size: 1rem;
        }
    </style>
    <form wire:submit.prevent="save" class="certificate-form">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-0">Crear certificado médico veterinario</h2>
                <div class="text-muted small">Fecha de emisión: {{ \Carbon\Carbon::parse($fecha_registro)->format('d/m/Y') }}</div>
            </div>
            <div class="d-flex gap-2">
                <a href="/certificates" wire:navigate class="btn btn-outline-secondary">
                    <i class="ph ph-arrow-left me-1"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-primary px-4 shadow-sm" wire:loading.attr="disabled">
                    <i class="ph ph-floppy-disk me-1"></i> Guardar certificado
                </button>
            </div>
        </div>

        <div class="row g-4">
            {{-- SECCIÓN: DATOS DEL VETERINARIO --}}
            <div class="col-12 col-xl-3">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-header bg-white border-0 pt-4 pb-0 px-4 d-flex align-items-center gap-2">
                        <i class="ph ph-stethoscope fs-5 text-primary"></i>
                        <h5 class="fw-bold mb-0">DATOS DEL VETERINARIO</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Cédula</label>
                            <input type="text" wire:model="vet_cedula" class="form-control" disabled>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Nombre y Apellido</label>
                            <input type="text" value="{{ $vet_nombre }} {{ $vet_apellido }}" class="form-control" disabled>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Código Colegio Médico</label>
                            <input type="text" wire:model="vet_colegio_medico_codigo" class="form-control">
                            @error('vet_colegio_medico_codigo') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Código del Ministerio</label>
                            <input type="text" wire:model="vet_ministerio_codigo" class="form-control">
                            @error('vet_ministerio_codigo') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-0">
                            <label class="form-label fw-semibold small">Área de Reproducción</label>
                            <input type="text" wire:model="vet_area_reproduccion" class="form-control">
                            @error('vet_area_reproduccion') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- SECCIÓN: DATOS DEL ANIMAL --}}
            <div class="col-12 col-xl-9">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-0 pt-4 pb-0 px-4 d-flex align-items-center gap-2">
                        <i class="ph ph-piggy-bank fs-5 text-success"></i>
                        <h5 class="fw-bold mb-0">DATOS DEL ANIMAL</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold small">ID del Animal</label>
                                <input type="text" wire:model="animal_id" class="form-control bg-light">
                                @error('animal_id') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold small">Lote</label>
                                <input type="text" wire:model="lote" class="form-control bg-light">
                                @error('lote') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold small">Raza</label>
                                <input type="text" wire:model="raza" class="form-control bg-light">
                                @error('raza') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold small">Estatus</label>
                                <select wire:model="estatus" class="form-select bg-light">
                                    <option value="">Seleccione...</option>
                                    @foreach ($this->animalStatuses as $status)
                                        <option value="{{ $status->name }}">{{ $status->name }}</option>
                                    @endforeach
                                </select>
                                @error('estatus') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="col-md-2">
                                <label class="form-label fw-semibold small">Peso</label>
                                <input type="text" wire:model="peso" class="form-control bg-light decimal-mask" placeholder="0,00">
                                @error('peso') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold small">Sexo</label>
                                <select wire:model="sexo" class="form-select bg-light">
                                    <option value="">Seleccione...</option>
                                    <option value="M">Macho</option>
                                    <option value="F">Hembra</option>
                                </select>
                                @error('sexo') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-3 position-relative" x-data="{ showResults: false }">
                                <label class="form-label fw-semibold small">Nave</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="ph ph-magnifying-glass"></i></span>
                                    <input 
                                        wire:model.live.debounce.300ms="naveSearch" 
                                        type="text" 
                                        class="form-control bg-light border-start-0" 
                                        placeholder="Buscar nave..."
                                        @focus="showResults = true"
                                        @click.away="showResults = false"
                                        @keydown.escape="showResults = false"
                                        @keydown.enter="showResults = false"
                                    >
                                </div>
                                <input type="hidden" wire:model="nave">
                                
                                <div x-show="showResults && $wire.naveResults.length > 0" class="position-absolute w-100 mt-1 shadow-lg z-3 border border-light rounded overflow-hidden bg-white" style="left: 0; right: 0;">
                                    @foreach ($naveResults as $bn)
                                        <button type="button" 
                                                wire:click="selectNave('{{ $bn->name }}')" 
                                                @click="showResults = false"
                                                class="btn btn-link text-start text-dark w-100 p-2 border-bottom border-light text-decoration-none dropdown-item-hover">
                                            <div class="fw-bold text-dark small">{{ $bn->name }}</div>
                                        </button>
                                    @endforeach
                                </div>
                                @error('nave') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold small">Sección</label>
                                <select wire:model="seccion" wire:key="select-seccion-for-{{ $nave }}" class="form-select bg-light" {{ empty($nave) ? 'disabled' : '' }}>
                                    <option value="">Seleccione...</option>
                                    @foreach ($this->barnSections as $sec)
                                        <option value="{{ $sec->name }}" wire:key="sec-item-{{ $sec->id }}-{{ $nave }}">{{ $sec->name }}</option>
                                    @endforeach
                                </select>
                                @error('seccion') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-1">
                                <label class="form-label fw-semibold small">Corral</label>
                                <input type="text" wire:model="corral" class="form-control bg-light">
                                @error('corral') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-semibold small">Tipo de Muerte</label>
                                <select wire:model="tipo_muerte" class="form-select bg-light">
                                    <option value="">Seleccione...</option>
                                    @foreach ($this->deathTypes as $type)
                                        <option value="{{ $type->name }}">{{ $type->name }}</option>
                                    @endforeach
                                </select>
                                @error('tipo_muerte') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6 position-relative">
                                <label class="form-label fw-semibold small">Causa de Muerte</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="ph ph-magnifying-glass"></i></span>
                                    <input wire:model.live.debounce.300ms="causeSearch" type="text" class="form-control bg-light border-start-0" placeholder="Buscar causa...">
                                </div>
                                <input type="hidden" wire:model="causa_muerte">
                                @if(!empty($causeResults))
                                    <div class="position-absolute w-100 mt-1 shadow-lg z-3 border border-light rounded overflow-hidden bg-white" style="left: 0; right: 0;">
                                        @foreach ($causeResults as $cause)
                                            <button type="button" wire:click="selectCause({{ $cause->id }})" class="btn btn-link text-start text-dark w-100 p-2 border-bottom border-light text-decoration-none dropdown-item-hover">
                                                <div class="fw-bold text-dark small">{{ $cause->name }}</div>
                                                <div class="text-muted" style="font-size: 0.65rem;">{{ $cause->system->name }}</div>
                                            </button>
                                        @endforeach
                                    </div>
                                @endif
                                @error('causa_muerte') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold small">Sistema Involucrado</label>
                                <input type="text" wire:model="sistema_involucrado" class="form-control bg-light fw-bold" readonly placeholder="Auto-seleccionado">
                                @error('sistema_involucrado') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-8 position-relative">
                                <label class="form-label fw-semibold small">Reportado Por</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="ph ph-user-focus"></i></span>
                                    <input wire:model.live.debounce.300ms="reportadoSearch" type="text" class="form-control bg-light border-start-0" placeholder="Escriba el nombre para buscar...">
                                </div>
                                
                                <input type="hidden" wire:model="reportado_por">

                                @if(!empty($reportadoResults))
                                    <div class="position-absolute w-100 mt-1 shadow-lg z-3 border border-light rounded overflow-hidden bg-white" style="left: 0; right: 0;">
                                        @foreach ($reportadoResults as $emp)
                                            <button type="button" wire:click="selectReportado('{{ $emp['name'] }}')" class="btn btn-link text-start text-dark w-100 p-3 border-bottom border-light text-decoration-none dropdown-item-hover">
                                                <div class="fw-bold text-dark">{{ $emp['name'] }}</div>
                                                <div class="text-muted small" style="font-size: 0.65rem;">Supervisor/Encargado</div>
                                            </button>
                                        @endforeach
                                    </div>
                                @endif
                                @error('reportado_por') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">Fecha de Muerte</label>
                                <input type="date" wire:model="fecha_muerte" class="form-control bg-light">
                                @error('fecha_muerte') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Evaluación Externa</label>
                                <textarea wire:model="evaluacion_externa" class="form-control bg-light" rows="3"></textarea>
                                @error('evaluacion_externa') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Evaluación Interna</label>
                                <textarea wire:model="evaluacion_interna" class="form-control bg-light" rows="3"></textarea>
                                @error('evaluacion_interna') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- SECCIÓN: EVIDENCIA FOTOGRÁFICA --}}
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-0 pt-4 pb-0 px-4 d-flex align-items-center gap-2">
                        <i class="ph ph-camera fs-5 text-warning"></i>
                        <h5 class="fw-bold mb-0">EVIDENCIA FOTOGRÁFICA</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <div class="col-md-4 text-center">
                                <label class="form-label fw-bold d-block mb-3">ARETE</label>
                                <div class="photo-upload-container mx-auto" style="width: 100%; max-width: 300px;">
                                    @if ($arete_photo)
                                        <div class="position-relative mb-2">
                                            <img src="{{ $arete_photo->temporaryUrl() }}" class="img-fluid rounded border shadow-sm" style="max-height: 200px; width: 100%; object-fit: cover;">
                                            <button type="button" wire:click="$set('arete_photo', null)" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2 rounded-circle">
                                                <i class="ph ph-x"></i>
                                            </button>
                                        </div>
                                    @else
                                        <div class="upload-placeholder p-5 border border-dashed rounded bg-light cursor-pointer position-relative d-flex flex-column align-items-center justify-content-center" style="height: 200px;">
                                            <i class="ph ph-image fs-1 opacity-25"></i>
                                            <span class="text-muted small mt-2">Subir Foto Arete</span>
                                            <input type="file" wire:model="arete_photo" class="position-absolute top-0 start-0 w-100 h-100 opacity-0 cursor-pointer">
                                        </div>
                                    @endif
                                    @error('arete_photo') <span class="text-danger small d-block mt-2">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="col-md-4 text-center">
                                <label class="form-label fw-bold d-block mb-3">TATUAJE</label>
                                <div class="photo-upload-container mx-auto" style="width: 100%; max-width: 300px;">
                                    @if ($tatuaje_photo)
                                        <div class="position-relative mb-2">
                                            <img src="{{ $tatuaje_photo->temporaryUrl() }}" class="img-fluid rounded border shadow-sm" style="max-height: 200px; width: 100%; object-fit: cover;">
                                            <button type="button" wire:click="$set('tatuaje_photo', null)" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2 rounded-circle">
                                                <i class="ph ph-x"></i>
                                            </button>
                                        </div>
                                    @else
                                        <div class="upload-placeholder p-5 border border-dashed rounded bg-light cursor-pointer position-relative d-flex flex-column align-items-center justify-content-center" style="height: 200px;">
                                            <i class="ph ph-image fs-1 opacity-25"></i>
                                            <span class="text-muted small mt-2">Subir Foto Tatuaje</span>
                                            <input type="file" wire:model="tatuaje_photo" class="position-absolute top-0 start-0 w-100 h-100 opacity-0 cursor-pointer">
                                        </div>
                                    @endif
                                    @error('tatuaje_photo') <span class="text-danger small d-block mt-2">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="col-md-4 text-center">
                                <label class="form-label fw-bold d-block mb-3">OTRA</label>
                                <div class="photo-upload-container mx-auto" style="width: 100%; max-width: 300px;">
                                    @if ($otra_photo)
                                        <div class="position-relative mb-2">
                                            <img src="{{ $otra_photo->temporaryUrl() }}" class="img-fluid rounded border shadow-sm" style="max-height: 200px; width: 100%; object-fit: cover;">
                                            <button type="button" wire:click="$set('otra_photo', null)" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2 rounded-circle">
                                                <i class="ph ph-x"></i>
                                            </button>
                                        </div>
                                    @else
                                        <div class="upload-placeholder p-5 border border-dashed rounded bg-light cursor-pointer position-relative d-flex flex-column align-items-center justify-content-center" style="height: 200px;">
                                            <i class="ph ph-image fs-1 opacity-25"></i>
                                            <span class="text-muted small mt-2">Subir Otra Foto</span>
                                            <input type="file" wire:model="otra_photo" class="position-absolute top-0 start-0 w-100 h-100 opacity-0 cursor-pointer">
                                        </div>
                                    @endif
                                    @error('otra_photo') <span class="text-danger small d-block mt-2">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
