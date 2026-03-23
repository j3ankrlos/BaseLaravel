<div>
    <x-slot name="header">
        <div class="d-flex align-items-center">
            <div class="bg-info bg-opacity-10 p-2 rounded-3 me-3">
                <i class="ph ph-fingerprint fs-3 text-info"></i>
            </div>
            <div>
                <h4 class="mb-0 fw-bold text-dark">Visor de Trazabilidad Total</h4>
                <p class="text-muted small mb-0">Consulta el historial de vida, movimientos y eventos de lotes o animales individuales.</p>
            </div>
        </div>
    </x-slot>

    <div class="container-fluid py-4">
        <!-- BUSCADOR SUPERIOR -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-body p-4 bg-dark">
                        <div class="row align-items-center">
                            <div class="col-lg-3 mb-3 mb-lg-0 text-white">
                                <h6 class="mb-0 fw-bold small text-uppercase letter-spacing-1 d-flex align-items-center">
                                    <i class="ph ph-magnifying-glass fs-4 me-2 text-info"></i> Buscador de Animales
                                </h6>
                                <p class="smallest text-white-50 mt-1 mb-0 px-1">Ej: PROC, 20-888, F1-T-001</p>
                            </div>
                            <div class="col-lg-9">
                                <div class="input-group input-group-lg shadow-sm rounded-4 overflow-hidden border-0">
                                    <span class="input-group-text bg-white border-0 px-3"><i class="ph ph-magnifying-glass text-muted"></i></span>
                                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-0 shadow-none ps-0" placeholder="Buscar lote de manejo, identificación de arete, SAP...">
                                    @if(!empty($search))
                                        <button class="btn bg-white border-0 text-muted" wire:click="$set('search', '')"><i class="ph ph-x-circle"></i></button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    @if($search)
                    <div class="border-top bg-white" style="max-height: 250px; overflow-y: auto;">
                        <div class="row g-0">
                            @forelse($animals as $animal)
                                @php $rowId = $animal->fake_id ?? $animal->id; @endphp
                                <div class="col-md-4 col-lg-3 border-end border-bottom">
                                    <button wire:click="selectAnimal('{{ $rowId }}')" 
                                            class="w-100 text-start btn rounded-0 py-3 px-4 border-0 @if($selected_animal_id == $rowId) btn-light bg-info bg-opacity-10 border-bottom border-info border-3 @else btn-white @endif">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="fw-bold text-dark">{{ $animal->identifier ?? $animal->management_lot }}</div>
                                                <div class="smallest text-muted text-uppercase mt-1">
                                                    <i class="ph ph-dna text-info me-1"></i>{{ $animal->genetic->name }}
                                                </div>
                                            </div>
                                            <span class="badge {{ $animal->type == 'INDIVIDUO' ? 'bg-primary' : 'bg-secondary' }} bg-opacity-10 text-{{ $animal->type == 'INDIVIDUO' ? 'primary' : 'secondary' }} rounded-pill px-2" style="font-size: 0.65rem;">
                                                {{ $animal->type }}
                                            </span>
                                        </div>
                                    </button>
                                </div>
                            @empty
                                <div class="col-12 p-4 text-center text-muted">
                                    <span class="small fw-bold"><i class="ph ph-warning-circle me-1"></i> No se encontraron resultados</span>
                                </div>
                            @endforelse
                        </div>
                    </div>
                    @endif
                    
                    @if(!empty($search) && method_exists($animals, 'links'))
                        <div class="card-footer bg-light py-2 px-3 border-top-0">
                            {{ $animals->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- DETALLE DE TRAZABILIDAD (ANCHO COMPLETO) -->
        <div class="row">
            <div class="col-12">
                @if($selectedAnimal)
                <div class="animate__animated animate__fadeIn">
                    <!-- HEADER DEL ANIMAL -->
                    <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden" style="background: linear-gradient(to right, #ffffff, #f8f9fa);">
                        <div class="card-body p-0 d-flex align-items-stretch">
                            <div class="bg-indigo text-white p-3 d-flex align-items-center justify-content-center" style="min-width: 130px;">
                                <div class="text-center">
                                    <i class="ph ph-{{ $selectedAnimal->type == 'INDIVIDUO' ? 'user-focus' : 'users-three' }} fs-1 mb-1 d-block opacity-75"></i>
                                    <span class="badge bg-white text-indigo border-0 px-2 rounded-pill" style="font-size: 0.65rem;">{{ $selectedAnimal->type }}</span>
                                </div>
                            </div>
                            <div class="p-3 px-4 w-100">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <h4 class="fw-bolder mb-0 text-dark">{{ $selectedAnimal->identifier ?? $selectedAnimal->management_lot }}</h4>
                                    <span class="badge {{ $selectedAnimal->status == 'Activo' ? 'bg-success' : 'bg-danger' }} rounded-pill px-3 py-1 fw-bold fs-7 shadow-sm">
                                        <i class="ph ph-check-circle me-1"></i>{{ $selectedAnimal->status }}
                                    </span>
                                </div>
                                <div class="d-flex flex-wrap align-items-center mt-2 gap-3" style="font-size: 0.85rem;">
                                    <div><span class="text-muted">Genética:</span> <strong class="text-dark">{{ $selectedAnimal->genetic->name }}</strong></div>
                                    <span class="text-black-50 opacity-25">|</span>
                                    <div><span class="text-muted">Sexo/Tipo:</span> <strong class="text-dark">{{ $selectedAnimal->sex }}</strong></div>
                                    <span class="text-black-50 opacity-25">|</span>
                                    <div><span class="text-muted">Edad:</span> <strong class="text-primary">{{ $selectedAnimal->age_days }} días</strong></div>
                                    <span class="text-black-50 opacity-25">|</span>
                                    <div><span class="text-muted">SAP:</span> <strong class="text-dark">{{ $selectedAnimal->lote_sap ?? 'Sin asignar' }}</strong></div>
                                    <span class="text-black-50 opacity-25">|</span>
                                    <div><span class="text-muted">Ubicación Actual:</span> <strong class="text-dark"><i class="ph ph-map-pin text-info me-1"></i>{{ $selectedAnimal->barnSection->barn->name ?? '' }} - {{ $selectedAnimal->barnSection->name ?? '' }}</strong></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TABS -->
                    <ul class="nav nav-tabs nav-tabs-custom mb-4 border-0">
                        <li class="nav-item">
                            <a class="nav-link py-3 px-4 border-0 rounded-top-4 fw-bold @if($activeTab == 'general') active @else text-muted @endif" 
                               href="javascript:void(0)" wire:click="$set('activeTab', 'general')">
                                <i class="ph ph-info me-2"></i> Resumen de Vida
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link py-3 px-4 border-0 rounded-top-4 fw-bold @if($activeTab == 'timeline') active @else text-muted @endif" 
                               href="javascript:void(0)" wire:click="$set('activeTab', 'timeline')">
                                <i class="ph ph-table me-2"></i> Historial de Movimientos
                            </a>
                        </li>
                    </ul>

                    @if($activeTab == 'general')
                    <div class="card border-0 shadow-sm rounded-4 p-4">
                        <h6 class="fw-bold mb-4 text-dark"><i class="ph ph-clipboard-text me-2 text-info"></i> Detalles de Origen y Registro</h6>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <td class="text-muted small py-2">Fecha de Ingreso:</td>
                                        <td class="fw-bold py-2">{{ $selectedAnimal->entry_date ? $selectedAnimal->entry_date->format('d/m/Y') : 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted small py-2">PIC Ingreso:</td>
                                        <td class="fw-bold py-2">{{ $selectedAnimal->pic_cycle }}-{{ $selectedAnimal->pic_day }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted small py-2">Peso Ingreso:</td>
                                        <td class="fw-bold py-2 text-info">{{ number_format($selectedAnimal->weight, 2, ',', '.') }} Kg</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted small py-2">Origen (Sala):</td>
                                        <td class="fw-bold py-2">{{ $selectedAnimal->source }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <td class="text-muted small py-2">Lote de Manejo:</td>
                                        <td class="fw-bold py-2"><span class="badge bg-secondary px-3">{{ $selectedAnimal->management_lot }}</span></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted small py-2">ID Interno/Arete:</td>
                                        <td class="fw-bold py-2"><code class="text-dark">{{ $selectedAnimal->internal_id ?? 'N/A' }}</code></td>
                                    </tr>
                                    @if($selectedAnimal->parentAnimal)
                                    <tr>
                                        <td class="text-muted small py-2">Lote Original:</td>
                                        <td class="fw-bold py-2">
                                            <a href="javascript:void(0)" wire:click="selectAnimal({{ $selectedAnimal->parent_animal_id }})" class="text-decoration-none">
                                                {{ $selectedAnimal->parentAnimal->management_lot }} <i class="ph ph-arrow-square-out smallest"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endif
                                    <tr>
                                        <td class="text-muted small py-2">Alimento Actual:</td>
                                        <td class="fw-bold py-2">{{ $selectedAnimal->feed_type ?? 'N/A' }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        
                        @if($selectedAnimal->type == 'LOTE')
                        <div class="alert alert-info border-0 rounded-4 mt-4 d-flex align-items-center gap-3">
                            <i class="ph ph-info fs-3"></i>
                            <div>
                                <h6 class="mb-0 fw-bold">Registro de Lote Grupal</h6>
                                <p class="mb-0 small opacity-75">Este registro representa un grupo de animales. La cantidad actual es de <strong>{{ $selectedAnimal->quantity }}</strong> individuos.</p>
                            </div>
                        </div>
                        @endif
                    </div>
                    @endif

                    @if($activeTab == 'timeline')
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden animate__animated animate__fadeIn">
                        <div class="card-header bg-white border-bottom py-3 px-4">
                            <h6 class="fw-bold mb-0 text-dark"><i class="ph ph-list-dashes me-2 text-indigo"></i> Registro Histórico y Movimientos</h6>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                                <thead class="bg-light text-muted text-uppercase fw-bold letter-spacing-1" style="font-size: 0.70rem; border-top: 1px solid #eee;">
                                    <tr>
                                        <th class="ps-4 py-3">Fecha</th>
                                        <th class="py-3">Suceso</th>
                                        <th class="py-3">Detalle</th>
                                        <th class="py-3">Localización</th>
                                        <th class="py-3 text-center">Cant.</th>
                                        <th class="py-3">Operario / Cambio</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($movements as $movement)
                                        <tr>
                                            <td class="ps-4">
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="rounded-circle bg-{{ $this->getMovementColor($movement->movement_type) }}" style="width: 8px; height: 8px;"></div>
                                                    <div>
                                                        <span class="fw-bold text-dark d-block mb-1">{{ $movement->movement_date->format('d/m/Y') }}</span>
                                                        <span class="smallest fw-bold text-muted bg-light px-2 py-1 rounded">PIC: {{ $movement->pic_cycle }}-{{ str_pad($movement->pic_day, 3, '0', STR_PAD_LEFT) }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $this->getMovementColor($movement->movement_type) }} bg-opacity-10 text-{{ $this->getMovementColor($movement->movement_type) }} fw-bold px-2 py-1 border border-{{ $this->getMovementColor($movement->movement_type) }} border-opacity-25 shadow-none rounded-2">
                                                    {{ $movement->movement_type }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="text-wrap text-muted" style="max-width: 280px; font-size: 0.82rem;">
                                                    <span class="@if($movement->movement_type == 'MUERTE' || $movement->movement_type == 'VENTA') text-danger fw-bold @else text-dark @endif">
                                                        {{ $movement->note ?? 'Sin observaciones' }}
                                                    </span>
                                                    @if($movement->deathCause)
                                                        <br><span class="smallest text-danger fw-bold"><i class="ph ph-skull me-1"></i>{{ $movement->deathCause->name }}</span>
                                                    @endif
                                                    @if($movement->boar_identifier)
                                                        <br><span class="smallest text-primary fw-bold"><i class="ph ph-target me-1"></i>Verraco: {{ $movement->boar_identifier }}</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                @if($movement->toBarnSection)
                                                    <div class="fw-bold text-dark">{{ $movement->toBarnSection->name ?? '' }}</div>
                                                    <div class="smallest text-muted">{{ $movement->toBarnSection->barn->name ?? '' }} @if($movement->to_pen_id) (C-{{ $movement->to_pen_id }}) @endif</div>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <span class="fw-bold fs-6 {{ $movement->quantity < 0 ? 'text-danger' : 'text-dark' }}">
                                                    {{ $movement->quantity > 0 ? '+' : '' }}{{ $movement->quantity }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="fw-bold text-dark">{{ $movement->user->name ?? 'Sistema' }}</div>
                                                <div class="smallest text-muted">Creado: {{ $movement->created_at->format('d/m/Y h:i') }}</div>
                                            </td>
                                        </tr>
                                    @endforeach

                                    @if($birthEvent && $birthEvent->birth)
                                        <tr>
                                            <td class="ps-4">
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="rounded-circle" style="width: 8px; height: 8px; background-color: #6f42c1;"></div>
                                                    <div>
                                                        <span class="fw-bold text-dark d-block mb-1">{{ \Carbon\Carbon::parse($birthEvent->birth->calendar_date)->format('d/m/Y') }}</span>
                                                        <span class="smallest fw-bold text-muted bg-light px-2 py-1 rounded">PIC: {{ $birthEvent->birth->pic_cycle }}-{{ str_pad($birthEvent->birth->pic_day, 3, '0', STR_PAD_LEFT) }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge fw-bold px-2 py-1 shadow-none rounded-2" style="background-color: rgba(111, 66, 193, 0.1); color: #6f42c1; border: 1px solid rgba(111, 66, 193, 0.3);">
                                                    NACIMIENTO / PARTO
                                                </span>
                                            </td>
                                            <td>
                                                <div class="text-wrap text-muted" style="max-width: 280px; font-size: 0.82rem;">
                                                    <span class="text-dark">Nacimiento en Maternidad.</span>
                                                    <br><span class="smallest text-primary fw-bold"><i class="ph ph-gender-female me-1"></i>Madre: {{ $birthEvent->birth->mother_tag }}</span>
                                                    <br><span class="smallest text-info fw-bold"><i class="ph ph-gender-male me-1"></i>Padre: {{ $birthEvent->birth->father_tag }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="fw-bold text-dark">Maternidad</div>
                                                <div class="smallest text-muted">Sala {{ $birthEvent->birth->room }}</div>
                                            </td>
                                            <td class="text-center">
                                                <span class="fw-bold text-dark">-</span>
                                            </td>
                                            <td>
                                                <div class="fw-bold text-dark">{{ $birthEvent->birth->responsible->name ?? 'Sistema' }}</div>
                                                <div class="smallest text-muted">Creado: {{ $birthEvent->birth->created_at->format('d/m/Y h:i') }}</div>
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                            
                            @if($movements->isEmpty() && !$birthEvent)
                                <div class="text-center py-5 text-muted border-top">
                                    <i class="ph ph-table fs-1 opacity-25 d-block mb-3"></i>
                                    <span class="small fw-bold">No hay registros históricos para este animal</span>
                                </div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
                @else
                <div class="card border-0 shadow-sm rounded-4 bg-white d-flex flex-column align-items-center justify-content-center p-5 animate__animated animate__fadeIn" style="min-height: 480px;">
                    <div class="bg-light bg-opacity-75 p-5 rounded-circle mb-4">
                        <i class="ph ph-fingerprint fs-max opacity-25 text-primary"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">Seleccione un animal para consultar</h5>
                    <p class="text-muted text-center" style="max-width: 320px;">Use el buscador a la izquierda para encontrar un registro por lote o identificación individual y ver su trazabilidad completa.</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <style>
        .nav-tabs-custom .nav-link {
            transition: all 0.3s ease;
            position: relative;
        }
        .nav-tabs-custom .nav-link.active {
            background-color: white !important;
            color: #0dcaf0 !important;
        }
        .nav-tabs-custom .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background-color: #0dcaf0;
            border-radius: 4px 4px 0 0;
        }
        .nav-tabs-custom .nav-link:hover:not(.active) {
            background-color: rgba(var(--bs-light-rgb), 0.5);
        }
        .fs-max { font-size: 6rem; }
        .timeline-container::before {
            content: '';
            position: absolute;
            left: 0;
            top: 10px;
            bottom: 0;
            width: 3px;
            background: rgba(var(--bs-light-rgb), 1);
            margin-left: -3px;
        }
        .timeline-item:last-child { margin-bottom: 0 !important; }
        .letter-spacing-1 { letter-spacing: 1px; }
        .cursor-pointer { cursor: pointer; }
    </style>
</div>
