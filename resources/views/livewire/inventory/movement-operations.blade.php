<div>
    <x-slot name="header">
        <div class="d-flex align-items-center">
            <div class="bg-primary bg-opacity-10 p-2 rounded-3 me-3">
                <i class="ph ph-arrows-merge fs-3 text-primary"></i>
            </div>
            <div>
                <h4 class="mb-0 fw-bold text-dark">Central de Operaciones Multi-Etapa</h4>
                <p class="text-muted small mb-0">Gestión de flujo de vida: Maternidad → Recría → Levante → Pubertad → Gestación.</p>
            </div>
        </div>
    </x-slot>

    <div class="container-fluid py-4">
        <div class="row">
            <!-- SIDEBAR: OPCIONES DE FLUJO -->
            <div class="col-md-3 mb-4">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden sticky-top" style="top: 20px;">
                    <div class="card-header bg-dark py-3 px-4">
                        <h6 class="text-white mb-0 fw-bold small text-uppercase letter-spacing-1">Operaciones de Inventario</h6>
                    </div>
                    <div class="list-group list-group-flush">
                        {{-- SECCION INGRESOS --}}
                        <div class="bg-light px-4 py-2 small fw-bold text-muted border-bottom text-uppercase">Logística de Ingresos</div>
                        <button wire:click="setTab('ingresos')" class="list-group-item list-group-item-action py-3 px-4 border-start-0 @if($activeTab == 'ingresos') active-op @endif">
                            <div class="d-flex align-items-center">
                                <i class="ph ph-sign-in fs-4 me-3"></i>
                                <div>
                                    <div class="fw-bold">Ingresos</div>
                                    <div class="small opacity-75">Recría, Levante, Pubertad</div>
                                </div>
                            </div>
                        </button>

                        {{-- SECCION TRASLADOS --}}
                        <div class="bg-light px-4 py-2 small fw-bold text-muted border-bottom text-uppercase mt-2">Manejo Interno</div>
                        <button wire:click="setTab('movimientos')" class="list-group-item list-group-item-action py-3 px-4 @if($activeTab == 'movimientos') active-op @endif">
                            <div class="d-flex align-items-center">
                                <i class="ph ph-truck fs-4 me-3"></i>
                                <div>
                                    <div class="fw-bold">Movimientos</div>
                                    <div class="small opacity-75">Entre galpones / corrales</div>
                                </div>
                            </div>
                        </button>

                        <button wire:click="setTab('promocion')" class="list-group-item list-group-item-action py-3 px-4 @if($activeTab == 'promocion') active-op @endif">
                            <div class="d-flex align-items-center">
                                <i class="ph ph-medal fs-4 me-3"></i>
                                <div>
                                    <div class="fw-bold">Promoción Machos</div>
                                    <div class="small opacity-75">STUD / Celadores</div>
                                </div>
                            </div>
                        </button>

                        {{-- SECCION REPRODUCCIÓN --}}
                        <div class="bg-light px-4 py-2 small fw-bold text-muted border-bottom text-uppercase mt-2">Reproducción & Celos</div>
                        <button wire:click="setTab('celos')" class="list-group-item list-group-item-action py-3 px-4 @if($activeTab == 'celos') active-op @endif">
                            <div class="d-flex align-items-center">
                                <i class="ph ph-calendar-heart fs-4 me-3"></i>
                                <div>
                                    <div class="fw-bold">Celos Diarios</div>
                                    <div class="small opacity-75">Detección y seguimiento</div>
                                </div>
                            </div>
                        </button>
                        <button wire:click="setTab('activaciones')" class="list-group-item list-group-item-action py-3 px-4 @if($activeTab == 'activaciones') active-op @endif">
                            <div class="d-flex align-items-center">
                                <i class="ph ph-lightning fs-4 me-3"></i>
                                <div>
                                    <div class="fw-bold">Activaciones</div>
                                    <div class="small opacity-75">Primeras Inseminaciones</div>
                                </div>
                            </div>
                        </button>

                        {{-- SECCION BAJAS --}}
                        <div class="bg-light px-4 py-2 small fw-bold text-muted border-bottom text-uppercase mt-2">Salidas</div>
                        <button wire:click="setTab('venta')" class="list-group-item list-group-item-action py-3 px-4 @if($activeTab == 'venta') active-op-sale @endif">
                            <div class="d-flex align-items-center">
                                <i class="ph ph-shopping-cart fs-4 me-3"></i>
                                <div class="fw-bold">Venta / Descarte</div>
                            </div>
                        </button>
                        <button wire:click="setTab('mortalidad')" class="list-group-item list-group-item-action py-3 px-4 @if($activeTab == 'mortalidad') active-op-death @endif">
                            <div class="d-flex align-items-center">
                                <i class="ph ph-skull fs-4 me-3"></i>
                                <div class="fw-bold">Mortalidad</div>
                            </div>
                        </button>
                    </div>
                </div>
            </div>

            <!-- MAIN AREA -->
            <div class="col-md-9">
                <!-- Global Date Selection -->
                <div class="card shadow-sm rounded-4 mb-4 bg-white border-start border-primary border-4">
                    <div class="card-body py-3 px-4">
                        <div class="row align-items-center">
                            <div class="col-md-5">
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary bg-opacity-10 p-2 rounded-circle me-3">
                                        <i class="ph ph-calendar-blank text-primary fs-5"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold text-dark small text-uppercase">Cronología de Operación</h6>
                                        <p class="text-muted smallest mb-0">Seleccione la fecha para este registro</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-7">
                                <div class="row g-3">
                                    <div class="col-md-7">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-light border-primary border-opacity-10 fw-bold small">FECHA</span>
                                            <input type="date" wire:model.live="operation_date" class="form-control border-primary border-opacity-10 fw-bold">
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-light border-primary border-opacity-10 fw-bold small">PIC</span>
                                            <input type="text" wire:model.live="operation_pic" class="form-control border-primary border-opacity-10 fw-bold text-center" placeholder="V-DDD">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- FORMULARIO: INGRESOS --}}
                @if($activeTab == 'ingresos')
                <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
                    <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                        <div class="nav nav-pills nav-fill bg-light p-1 rounded-3 mb-4 border">
                            <button wire:click="setIngresoType('RECRIA')" class="nav-link py-2 @if($ingresoType == 'RECRIA') active @endif">Mat. → Recría</button>
                            <button wire:click="setIngresoType('LEVANTE')" class="nav-link py-2 @if($ingresoType == 'LEVANTE') active @endif">Rec. → Levante</button>
                            <button wire:click="setIngresoType('PUBERTAD')" class="nav-link py-2 @if($ingresoType == 'PUBERTAD') active @endif">Lev. → Pubertad</button>
                        </div>
                        <h5 class="fw-bold text-dark mb-0"><i class="ph ph-sign-in me-2 text-primary"></i> Registro de Ingreso: {{ $ingresoType }}</h5>
                        <hr class="mt-3 opacity-10">
                    </div>
                    <div class="card-body p-4">
                        <form wire:submit="processIngreso">
                            <div class="row g-4">
                                <!-- SOURCE SELECTION -->
                                <div class="col-md-12">
                                    @if($ingresoType == 'RECRIA')
                                        <div class="alert alert-warning border-warning border-opacity-25 bg-warning bg-opacity-10 rounded-4 p-3 mb-4 d-flex align-items-center gap-3 shadow-sm">
                                            <i class="ph ph-warning-circle fs-4 text-warning"></i>
                                            <div class="small fw-bold text-warning-emphasis">
                                                RECORDATORIO CRÍTICO: Si la sala de maternidad tiene genética F1, debe realizar primero el ingreso de esta raza antes que cualquier otra para asegurar la correcta limpieza de la sala.
                                            </div>
                                        </div>
                                    @endif
                                    <label class="form-label fw-bold small text-muted text-uppercase">Seleccion de Origen</label>
                                    @if($ingresoType == 'RECRIA')
                                        <div class="row g-2 mb-4 align-items-end">
                                            <div class="col-md-2">
                                                <label class="form-label fw-bold small">1. Genética</label>
                                                <select wire:model.live="i_genetic_id" class="form-select shadow-none border-primary border-opacity-25 bg-light bg-opacity-50 text-uppercase fw-bold">
                                                    <option value="">Seleccione Genética...</option>
                                                    @foreach($genetics as $gen)
                                                        <option value="{{ $gen->id }}">{{ $gen->name }}</option>
                                                    @endforeach
                                                </select>
                                                @error('i_genetic_id') <span class="text-danger small">{{ $message }}</span> @enderror
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label fw-bold small">2. Sala</label>
                                                <select wire:model.live="i_selected_room" class="form-select shadow-none border-primary border-opacity-25 bg-light bg-opacity-50 fw-bold" @if(!$i_genetic_id) disabled @endif>
                                                    <option value="">Sala...</option>
                                                    @foreach($rooms as $room)
                                                        <option value="{{ $room }}">{{ $room }}</option>
                                                    @endforeach
                                                </select>
                                                @error('i_selected_room') <span class="text-danger small">{{ $message }}</span> @enderror
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label fw-bold small">3. Cantidad</label>
                                                @php 
                                                    $isF1Batch = $i_genetic_id == 7;
                                                    $isReadOnly = $ingresoType == 'RECRIA' && !$isF1Batch;
                                                @endphp
                                                <input type="number" wire:model.live="i_quantity" class="form-control @if(!$isReadOnly) border-primary @else bg-light @endif fw-bold" placeholder="Cant." @if($isReadOnly) readonly @endif>
                                                @error('i_quantity') <span class="text-danger small">{{ $message }}</span> @enderror
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label fw-bold small">4. Peso (Kg)</label>
                                                <input type="text" 
                                                       wire:model.live="i_weight" 
                                                       class="form-control border-primary border-opacity-25 bg-light bg-opacity-50 fw-bold text-center decimal-mask" 
                                                       placeholder="0,00">
                                                @error('i_weight') <span class="text-danger small">{{ $message }}</span> @enderror
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label fw-bold small">5. Lote de Manejo</label>
                                                <div class="input-group shadow-none">
                                                    <span class="input-group-text bg-light border-primary border-opacity-25 px-2"><i class="ph ph-tag"></i></span>
                                                    <input type="text" wire:model.live="i_management_lot" class="form-control border-primary border-opacity-25 bg-light bg-opacity-50 fw-bold" placeholder="PROC">
                                                </div>
                                                @error('i_management_lot') <span class="text-danger small">{{ $message }}</span> @enderror
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label fw-bold small">6. Alimento</label>
                                                @php
                                                    $barnId = $i_barn_id;
                                                    $locationName = null;
                                                    if ($barnId) {
                                                        $b = collect($barns)->firstWhere('id', $barnId);
                                                        $locationName = $b?->name;
                                                    }
                                                    if ($ingresoType == 'RECRIA') {
                                                        $locationName = 'RECRIA';
                                                    }
                                                    $availableFeeds = $this->getAvailableFeedTypes($locationName);
                                                @endphp
                                                <select wire:model="i_feed_type" class="form-select border-primary border-opacity-25 bg-primary bg-opacity-10 fw-bold text-primary shadow-none" @if(count($availableFeeds) <= 1) disabled @endif>
                                                    @foreach($availableFeeds as $feed)
                                                        <option value="{{ $feed }}">{{ $feed }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        
                                        @if($i_selected_room)
                                            @if($i_genetic_id == 7)
                                                <!-- MODO LOTE (F1) -->
                                                <div class="alert alert-info border-primary border-opacity-25 bg-primary bg-opacity-10 rounded-4 p-4 mb-4 d-flex align-items-center gap-3">
                                                    <div class="bg-primary bg-opacity-20 p-3 rounded-circle">
                                                        <i class="ph ph-arrows-merge text-primary fs-3"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-1 text-primary fw-bold">Modo de Ingreso: Lote F1</h6>
                                                        <p class="mb-0 small text-muted">Ingrese la cantidad total en el campo inferior. Los lotes marcados como <span class="badge bg-primary">PROC</span> se actualizarán automáticamente al cerrar el lote definitivo.</p>
                                                    </div>
                                                </div>
                                            @else
                                                <!-- MODO INDIVIDUAL (Genética) -->
                                                <div class="table-responsive border rounded-3 mb-2 shadow-sm" style="max-height: 480px; overflow-y: auto;">
                                                    <table class="table table-sm table-hover align-middle mb-0" style="min-width: 100%;">
                                                        <thead class="bg-primary text-white sticky-top shadow-sm">
                                                            <tr style="background: var(--sidebar-bg) !important;">
                                                                <th width="40" class="text-center">
                                                                    <input type="checkbox" wire:model.live="i_all_selected" class="form-check-input bg-primary border-white">
                                                                </th>
                                                                <th width="90">F.PARTO</th>
                                                                <th width="60">SALA</th>
                                                                <th width="100">MADRE</th>
                                                                <th width="100">PADRE</th>
                                                                <th width="50" class="text-center">LNV</th>
                                                                <th width="120">ID OREJA</th>
                                                                <th width="120">ID ARETE</th>
                                                                <th width="90">SEXO</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="small">
                                                            @forelse($i_birth_details as $detail)
                                                                <tr class="{{ in_array($detail['id'], $i_selected_details) ? 'table-primary' : '' }}">
                                                                    <td class="text-center">
                                                                        <input type="checkbox" wire:model.live="i_selected_details" value="{{ $detail['id'] }}" class="form-check-input">
                                                                    </td>
                                                                    <td class="fw-bold">{{ $detail['birth']['pic_cycle'] }}-{{ $detail['birth']['pic_day'] }}</td>
                                                                    <td><span class="badge bg-secondary opacity-75">{{ $detail['birth']['room'] }}</span></td>
                                                                    <td>{{ $detail['birth']['mother_tag'] }}</td>
                                                                    <td>{{ $detail['birth']['father_tag'] }}</td>
                                                                    <td class="text-center">{{ $detail['birth']['lnv'] }}</td>
                                                                    <td class="small-id"><code class="text-dark">{{ $detail['ear_id'] }}</code></td>
                                                                    <td><span class="badge bg-white text-primary border border-primary px-1" style="font-size: 0.75rem;">{{ $detail['generated_id'] }}</span></td>

                                                                    <td class="text-muted small">
                                                                        <span class="badge {{ $detail['sex'] == 'Hembra' ? 'bg-danger' : 'bg-primary' }} bg-opacity-10 text-{{ $detail['sex'] == 'Hembra' ? 'danger' : 'primary' }} rounded-pill px-2">
                                                                            {{ $detail['sex'] }}
                                                                        </span>
                                                                    </td>
                                                                </tr>
                                                            @empty
                                                                <tr>
                                                                    <td colspan="10" class="text-center py-5 text-muted">No hay animales disponibles de esta genética en la sala seleccionada.</td>
                                                                </tr>
                                                            @endforelse
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <div class="d-flex justify-content-between p-2 bg-light rounded-3 border mb-3">
                                                    <div class="text-muted small">
                                                        Total Sala (Genética): <span class="fw-bold text-dark">{{ count($i_birth_details) }}</span>
                                                    </div>
                                                    <div class="small fw-bold text-primary">
                                                        Seleccionados: {{ count($i_selected_details) }}
                                                    </div>
                                                </div>
                                            @endif
                                        @endif
                                    @else
                                        {{-- ── LEVANTE: misma metodología que RECRIA ── --}}
                                        <div class="row g-2 mb-4 align-items-end">
                                            <div class="col-md-2">
                                                <label class="form-label fw-bold small">1. Genética</label>
                                                <select wire:model.live="l_genetic_id" class="form-select shadow-none border-primary border-opacity-25 bg-light bg-opacity-50 text-uppercase fw-bold">
                                                    <option value="">Seleccione Genética...</option>
                                                    @foreach($genetics as $gen)
                                                        <option value="{{ $gen->id }}">{{ $gen->name }}</option>
                                                    @endforeach
                                                </select>
                                                @error('l_genetic_id') <span class="text-danger small">{{ $message }}</span> @enderror
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label fw-bold small">2. Lote</label>
                                                <select wire:model.live="l_selected_lot" class="form-select shadow-none border-primary border-opacity-25 bg-light bg-opacity-50 fw-bold" @if(!$l_genetic_id) disabled @endif>
                                                    <option value="">Lote...</option>
                                                    @foreach($l_lots as $lotInfo)
                                                        <option value="{{ $lotInfo['lot'] }}">{{ $lotInfo['lot'] }} ({{ $lotInfo['total'] }})</option>
                                                    @endforeach
                                                </select>
                                                @error('l_selected_lot') <span class="text-danger small">{{ $message }}</span> @enderror
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label fw-bold small">3. Cantidad</label>
                                                @php $isLevanteF1 = ($l_genetic_id ?? null) == 7; @endphp
                                                <input type="number" wire:model.live="i_quantity"
                                                    class="form-control @if(!$isLevanteF1) bg-light @else border-primary @endif fw-bold"
                                                    placeholder="Cant."
                                                    @if(!$isLevanteF1) readonly @endif>
                                                @error('i_quantity') <span class="text-danger small">{{ $message }}</span> @enderror
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label fw-bold small">4. Peso (Kg)</label>
                                                <input type="text"
                                                    wire:model.live="i_weight"
                                                    class="form-control border-primary border-opacity-25 bg-light bg-opacity-50 fw-bold text-center decimal-mask"
                                                    placeholder="0,00">
                                                @error('i_weight') <span class="text-danger small">{{ $message }}</span> @enderror
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label fw-bold small">5. Alimento</label>
                                                @php
                                                    $levanteFeeds = $this->getAvailableFeedTypes('LA');
                                                @endphp
                                                <select wire:model="i_feed_type" class="form-select border-primary border-opacity-25 bg-primary bg-opacity-10 fw-bold text-primary shadow-none" @if(count($levanteFeeds) <= 1) disabled @endif>
                                                    @foreach($levanteFeeds as $feed)
                                                        <option value="{{ $feed }}">{{ $feed }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        @if($l_selected_lot)
                                            @if(($l_genetic_id ?? null) == 7)
                                                {{-- MODO LOTE (F1) --}}
                                                <div class="alert alert-info border-primary border-opacity-25 bg-primary bg-opacity-10 rounded-4 p-4 mb-4 d-flex align-items-center gap-3">
                                                    <div class="bg-primary bg-opacity-20 p-3 rounded-circle">
                                                        <i class="ph ph-arrows-merge text-primary fs-3"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-1 text-primary fw-bold">Modo de Ingreso: Lote F1 → Levante</h6>
                                                        <p class="mb-0 small text-muted">Lote seleccionado: <span class="badge bg-primary">{{ $l_selected_lot }}</span>. Ingrese la cantidad a mover en el campo de cantidad.</p>
                                                    </div>
                                                </div>
                                            @else
                                                {{-- MODO INDIVIDUAL --}}
                                                <div class="table-responsive border rounded-3 mb-2 shadow-sm" style="max-height: 480px; overflow-y: auto;">
                                                    <table class="table table-sm table-hover align-middle mb-0" style="min-width: 100%;">
                                                        <thead class="sticky-top shadow-sm" style="background: var(--sidebar-bg) !important;">
                                                            <tr>
                                                                <th width="40" class="text-center">
                                                                    <input type="checkbox" wire:model.live="l_all_selected" class="form-check-input bg-primary border-white">
                                                                </th>
                                                                <th width="120">ARETE / ID</th>
                                                                <th width="100">GENÉTICA</th>
                                                                <th width="100">LOTE</th>
                                                                <th width="80" class="text-center">CANT.</th>
                                                                <th width="120">ETAPA ACTUAL</th>
                                                                <th width="90">SEXO</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="small">
                                                            @forelse($l_animal_list as $animal)
                                                                <tr class="{{ in_array($animal['id'], $l_selected_animals) ? 'table-primary' : '' }}">
                                                                    <td class="text-center">
                                                                        <input type="checkbox" wire:model.live="l_selected_animals" value="{{ $animal['id'] }}" class="form-check-input">
                                                                    </td>
                                                                    <td class="small-id"><code class="text-dark">{{ $animal['internal_id'] ?? $animal['identifier'] ?? 'N/A' }}</code></td>
                                                                    <td>{{ $animal['genetic']['name'] ?? '-' }}</td>
                                                                    <td><span class="badge bg-secondary opacity-75">{{ $animal['management_lot'] }}</span></td>
                                                                    <td class="text-center fw-bold">{{ $animal['quantity'] }}</td>
                                                                    <td><span class="badge bg-warning text-dark">RECRÍA</span></td>
                                                                    <td>
                                                                        <span class="badge {{ ($animal['sex'] ?? '') == 'Hembra' ? 'bg-danger' : 'bg-primary' }} bg-opacity-10 text-{{ ($animal['sex'] ?? '') == 'Hembra' ? 'danger' : 'primary' }} rounded-pill px-2">
                                                                            {{ $animal['sex'] ?? '-' }}
                                                                        </span>
                                                                    </td>
                                                                </tr>
                                                            @empty
                                                                <tr>
                                                                    <td colspan="7" class="text-center py-5 text-muted">No hay animales de esta genética en el lote seleccionado.</td>
                                                                </tr>
                                                            @endforelse
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <div class="d-flex justify-content-between p-2 bg-light rounded-3 border mb-3">
                                                    <div class="text-muted small">
                                                        Total disponible: <span class="fw-bold text-dark">{{ count($l_animal_list) }}</span>
                                                    </div>
                                                    <div class="small fw-bold text-primary">
                                                        Seleccionados: {{ count($l_selected_animals) }}
                                                    </div>
                                                </div>
                                            @endif
                                        @endif
                                    @endif
                                </div>

                                <!-- DESTINATION LOGISTICS -->
                                <div class="col-md-12">
                                    <div class="p-3 bg-white rounded-4 border border-light-subtle shadow-sm">
                                        <div class="row g-3">
                                            <div class="col-md-12">
                                                <label class="form-label fw-bold small text-muted text-uppercase m-0">Logística de Ubicación Destino</label>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label fw-bold small">Nave (Galpón)</label>
                                                @php 
                                                    $allowedBarnNames = match($ingresoType) {
                                                        'RECRIA' => ['RECRIA'],
                                                        'LEVANTE' => ['LA', 'LB', 'LE'],
                                                        'PUBERTAD' => ['PUB1', 'PUB2'],
                                                        default => []
                                                    };
                                                    $filteredBarns = collect($barns)->whereIn('name', $allowedBarnNames);
                                                    $currentBarn = $filteredBarns->firstWhere('id', $i_barn_id);
                                                @endphp

                                                @if($filteredBarns->count() == 1 && $currentBarn)
                                                    <div class="input-group">
                                                        <input type="text" class="form-control bg-light border-light-subtle text-primary fw-bold" value="{{ $currentBarn->name }}" readonly>
                                                        <span class="input-group-text bg-light border-light-subtle border-start-0">
                                                            <i class="ph ph-lock-keyhole small opacity-50"></i>
                                                        </span>
                                                    </div>
                                                @else
                                                    <select wire:model.live="i_barn_id" class="form-select border-light-subtle">
                                                        <option value="">Seleccione Nave...</option>
                                                        @foreach($filteredBarns as $b)
                                                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                                                        @endforeach
                                                    </select>
                                                @endif
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label fw-bold small">Sección</label>
                                                @php 
                                                    $currentSection = collect($barnSections)->firstWhere('id', $i_barn_section_id);
                                                    $isLevanteNave = $currentBarn && in_array($currentBarn->name, ['LA', 'LB', 'LE']);
                                                @endphp

                                                @if($isLevanteNave)
                                                    <div class="input-group">
                                                        <input type="text" class="form-control bg-light border-light-subtle text-primary fw-bold" value="C" readonly>
                                                        <span class="input-group-text bg-light border-light-subtle border-start-0">
                                                            <i class="ph ph-lock-keyhole small opacity-50"></i>
                                                        </span>
                                                    </div>
                                                @elseif($ingresoType == 'RECRIA' && count($recriaAvailableSections) > 0)
                                                    <select wire:model.live="i_barn_section_id" class="form-select border-light-subtle">
                                                        <option value="">Seleccione Sección...</option>
                                                        @foreach($recriaAvailableSections as $rs)
                                                            @if(!$rs['is_full'])
                                                                <option value="{{ $rs['id'] }}" {{ $i_barn_section_id == $rs['id'] ? 'selected' : '' }}>
                                                                    {{ $rs['name'] }}
                                                                    @if($rs['is_proc']) ← EN PROCESO @endif
                                                                </option>
                                                            @endif
                                                        @endforeach
                                                    </select>
                                                @else
                                                    <select wire:model.live="i_barn_section_id" class="form-select border-light-subtle" @if(!$i_barn_id) disabled @endif>
                                                        <option value="">Seleccione Sección...</option>
                                                        @foreach($barnSections->where('barn_id', $i_barn_id) as $bs)
                                                            <option value="{{ $bs->id }}">{{ $bs->name }}</option>
                                                        @endforeach
                                                    </select>
                                                @endif
                                                @error('i_barn_section_id') <span class="text-danger small">{{ $message }}</span> @enderror
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label fw-bold small">Corral (Manual o Lista)</label>
                                                <input list="i_pens_list" wire:model.live="i_pen_name" class="form-control border-light-subtle" placeholder="Escriba o elija..." @if(!$i_barn_section_id) disabled @endif>
                                                <datalist id="i_pens_list">
                                                    @foreach($pens->where('barn_section_id', $i_barn_section_id) as $p)
                                                        <option value="{{ $p->name }}">Capacidad: {{ $p->capacity ?? '?' }}</option>
                                                    @endforeach
                                                </datalist>
                                                @error('i_pen_name') <span class="text-danger small">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- SI ES PUBERTAD, MOSTRAR CAMPOS DE INDIVIDUALIZACION --}}
                                @if($ingresoType == 'PUBERTAD')
                                <div class="col-md-12 mt-4">
                                    <div class="card bg-info bg-opacity-5 border-info border-opacity-10 rounded-4">
                                        <div class="card-body">
                                            <h6 class="fw-bold text-info mb-3">Parámetros de Individualización (Generar IDs)</h6>
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-bold">Prefijo de Arete</label>
                                                    <input type="text" wire:model="i_prefix_id" class="form-control" placeholder="Ej. PUB-">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-bold">Correlativo Inicio</label>
                                                    <input type="number" wire:model="i_start_correlative" class="form-control">
                                                </div>
                                            </div>
                                            <p class="small text-muted mt-2 mb-0"><i class="ph ph-info me-1"></i> Se crearán individuos únicos con los IDs generados automáticamente.</p>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>

                            <div class="mt-4 d-flex justify-content-between align-items-center">
                                @if($ingresoType == 'RECRIA' && $i_selected_room && $i_genetic_id != 7 && count($i_selected_details) > 0)
                                    @php
                                        $allSelected = (count($i_selected_details) === count($i_birth_details));
                                    @endphp

                                    @if($allSelected)
                                        <div class="bg-success bg-opacity-10 px-4 py-2 rounded-pill border border-success border-opacity-25 text-success small fw-bold">
                                            <i class="ph ph-check-circle me-1"></i> Se moverá el total de la sala (Cierre natural)
                                        </div>
                                    @else
                                        <div class="form-check form-switch bg-warning bg-opacity-10 px-4 py-2 rounded-pill border border-warning border-opacity-25 shadow-sm">
                                            <input class="form-check-input ms-0 me-2" type="checkbox" role="switch" id="discardRemaining" wire:model="i_discard_remaining">
                                            <label class="form-check-label fw-bold text-warning small" for="discardRemaining">
                                                <i class="ph ph-warning-circle me-1"></i> Cerrar sala con descarte (Descarta sobrantes)
                                            </label>
                                        </div>
                                    @endif
                                @else
                                    <div></div>
                                @endif
                                
                                <button type="submit" class="btn btn-primary px-5 py-2 fw-bold rounded-pill shadow-sm">
                                    <i class="ph ph-check-circle me-1"></i> Confirmar Ingreso
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                @endif

                {{-- FORMULARIO: MOVIMIENTOS --}}
                @if($activeTab == 'movimientos')
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white border-0 pt-4 px-4">
                        <h5 class="fw-bold text-dark"><i class="ph ph-truck me-2 text-primary"></i> Movimiento Interno de Inventario</h5>
                        <p class="text-muted small">Reubicación de animales entre naves o corrales dentro de la misma etapa.</p>
                    </div>
                    <div class="card-body p-4">
                        <form wire:submit="processMovimiento">
                            <div class="row g-4">
                                <div class="col-md-12">
                                    <label class="form-label fw-bold">Seleccionar Animal/Lote</label>
                                    <select wire:model="m_animal_id" class="form-select form-select-lg shadow-none">
                                        <option value="">Seleccione de Inventario Activo...</option>
                                        @foreach($activeInventory as $item)
                                            <option value="{{ $item->id }}">
                                                [{{ $item->stage->name }}] {{ $item->identifier ?? $item->management_lot }} | Cant: {{ $item->quantity }} | Ubic: {{ $item->barnSection->barn->name }} - {{ $item->pen->name ?? $item->barnSection->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Cantidad a Mover</label>
                                    <input type="number" wire:model="m_quantity" class="form-control form-control-lg">
                                </div>
                                
                                <!-- DESTINATION LOGISTICS MOVEMENTS -->
                                <div class="col-md-12 mt-3">
                                    <div class="bg-primary bg-opacity-5 p-4 rounded-4 border border-primary border-opacity-10 shadow-sm">
                                        <div class="row g-3">
                                            <div class="col-md-12">
                                                <label class="form-label fw-bold small text-primary text-uppercase m-0">Logística de Destino Interno</label>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label fw-bold small">Nave (Galpón)</label>
                                                <select wire:model.live="m_barn_id" class="form-select border-primary border-opacity-25">
                                                    <option value="">Seleccione Nave...</option>
                                                    @foreach($barns as $b)
                                                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label fw-bold small">Sección</label>
                                                <select wire:model.live="m_barn_section_id" class="form-select border-primary border-opacity-25" @if(!$m_barn_id) disabled @endif>
                                                    <option value="">Seleccione Sección...</option>
                                                    @foreach($barnSections->where('barn_id', $m_barn_id) as $bs)
                                                        <option value="{{ $bs->id }}">{{ $bs->name }}</option>
                                                    @endforeach
                                                </select>
                                                @error('m_barn_section_id') <span class="text-danger small">{{ $message }}</span> @enderror
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label fw-bold small">Corral (Manual o Lista)</label>
                                                <input list="m_pens_list" wire:model.live="m_pen_name" class="form-control border-primary border-opacity-25" placeholder="Escriba o elija..." @if(!$m_barn_section_id) disabled @endif>
                                                <datalist id="m_pens_list">
                                                    @foreach($pens->where('barn_section_id', $m_barn_section_id) as $p)
                                                        <option value="{{ $p->name }}">Capacidad: {{ $p->capacity ?? '?' }}</option>
                                                    @endforeach
                                                </datalist>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4 text-end">
                                <button type="submit" class="btn btn-primary px-5 rounded-pill fw-bold">Grabar Movimiento</button>
                            </div>
                        </form>
                    </div>
                </div>
                @endif

                {{-- FORMULARIO: CELOS --}}
                @if($activeTab == 'celos')
                <div class="card shadow-sm rounded-4">
                    <div class="card-header bg-white border-0 pt-4 px-4">
                        <h5 class="fw-bold text-dark"><i class="ph ph-calendar-heart me-2 text-primary"></i> Registro de Celo Diario</h5>
                    </div>
                    <div class="card-body p-4">
                        <form wire:submit="processCelo">
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label fw-bold">Hembra Seleccionada</label>
                                    <select wire:model="c_animal_id" class="form-select shadow-none">
                                        <option value="">Seleccione Hembra...</option>
                                        @foreach($activeInventory->where('type', 'INDIVIDUO')->where('sex', 'Hembra') as $item)
                                            <option value="{{ $item->id }}">{{ $item->identifier }} ({{ $item->stage->name }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold">Observaciones</label>
                                    <textarea wire:model="c_note" class="form-control" rows="2" placeholder="Ej. Celo intenso, lista para monta..."></textarea>
                                </div>
                            </div>
                            <div class="mt-4 text-end">
                                <button type="submit" class="btn btn-primary px-5 rounded-pill fw-bold shadow-sm">Registrar Celo</button>
                            </div>
                        </form>
                    </div>
                </div>
                @endif

                {{-- FORMULARIO: ACTIVACIONES --}}
                @if($activeTab == 'activaciones')
                <div class="card shadow-sm rounded-4">
                    <div class="card-header bg-white border-0 pt-4 px-4">
                        <h5 class="fw-bold text-dark"><i class="ph ph-lightning me-2 text-warning fs-3"></i> Activación de Hembra (1era Inseminación)</h5>
                        <p class="text-muted small">Marca el inicio reproductivo. La hembra pasará automáticamente a la etapa de <strong>Gestación</strong>.</p>
                    </div>
                    <div class="card-body p-4">
                        <form wire:submit="processActivacion">
                            <div class="row g-4">
                                <div class="col-md-12">
                                    <label class="form-label fw-bold">Hembra a Activar (Pubertad/Remplazo)</label>
                                    <select wire:model="act_animal_id" class="form-select form-select-lg">
                                        <option value="">Seleccione Hembra...</option>
                                        @foreach($activeInventory->where('type', 'INDIVIDUO')->where('sex', 'Hembra')->whereIn('stage.name', ['Pubertad', 'Monta']) as $item)
                                            <option value="{{ $item->id }}">{{ $item->identifier }} - Ubic: {{ $item->barnSection->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label fw-bold">Verraco / Dosis Inseminante</label>
                                    <input type="text" wire:model="act_boar" class="form-control" placeholder="ID del Verraco">
                                </div>
                            </div>
                            <div class="mt-4 text-end">
                                <button type="submit" class="btn btn-warning text-dark px-5 rounded-pill fw-bold shadow-sm">Activar Hembra</button>
                            </div>
                        </form>
                    </div>
                </div>
                @endif

                {{-- FORMULARIO: VENTA --}}
                @if($activeTab == 'venta')
                <div class="card shadow-sm rounded-4 border-start border-primary border-5">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-4"><i class="ph ph-shopping-cart me-2"></i> Despacho por Venta / Descarte</h5>
                        <form wire:submit="processVenta">
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label fw-bold">Item de Inventario</label>
                                    <select wire:model="v_animal_id" class="form-select">
                                        <option value="">Seleccione...</option>
                                        @foreach($activeInventory as $item)
                                            <option value="{{ $item->id }}">{{ $item->identifier ?? $item->management_lot }} | Cant: {{ $item->quantity }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Cant. Venta</label>
                                    <input type="number" wire:model="v_quantity" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Peso Total (kg)</label>
                                    <input type="text" wire:model="v_weight" class="form-control decimal-mask" placeholder="0,00">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Factura / Ref</label>
                                    <input type="text" wire:model="v_invoice" class="form-control">
                                </div>
                            </div>
                            <div class="mt-4 text-end">
                                <button type="submit" class="btn btn-primary px-5 rounded-pill fw-bold">Registrar Salida</button>
                            </div>
                        </form>
                    </div>
                </div>
                @endif

                {{-- FORMULARIO: MORTALIDAD --}}
                @if($activeTab == 'mortalidad')
                <div class="card shadow-sm rounded-4 border-start border-danger border-5">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-4 text-danger"><i class="ph ph-skull me-2"></i> Reporte de Mortalidad (Baja)</h5>
                        <form wire:submit="processMortalidad">
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label class="form-label fw-bold">Animal / Lote afectado</label>
                                    <select wire:model="d_animal_id" class="form-select">
                                        <option value="">Seleccione...</option>
                                        @foreach($activeInventory as $item)
                                            <option value="{{ $item->id }}">{{ $item->identifier ?? $item->management_lot }} | Vivos: {{ $item->quantity }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Cant. Bajas</label>
                                    <input type="number" wire:model="d_quantity" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Causa de Muerte</label>
                                    <select wire:model="d_death_cause_id" class="form-select">
                                        <option value="">Seleccione Causa...</option>
                                        @foreach($deathCauses as $dc)
                                            <option value="{{ $dc->id }}">{{ $dc->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Nota adicional</label>
                                    <input type="text" wire:model="d_note" class="form-control" placeholder="Opcional">
                                </div>
                            </div>
                            <div class="mt-4 text-end">
                                <button type="submit" class="btn btn-danger px-5 rounded-pill fw-bold shadow-sm">Confirmar Baja Médica</button>
                            </div>
                        </form>
                    </div>
                </div>
                @endif

                {{-- FORMULARIO: PROMOCIÓN --}}
                @if($activeTab == 'promocion')
                <div class="card shadow-sm rounded-4 border-start border-info border-5">
                    <div class="card-header bg-white border-0 pt-4 px-4">
                        <h5 class="fw-bold text-dark"><i class="ph ph-medal me-2 text-info fs-3"></i> Evaluación y Promoción de Machos</h5>
                        <p class="text-muted small">Promueva machos de Pubertad a su rol definitivo como <strong>Sementales (STUD)</strong> o <strong>Celadores (DM)</strong>.</p>
                    </div>
                    <div class="card-body p-4">
                        <form wire:submit="processPromocion">
                            <div class="row g-4">
                                <div class="col-md-12">
                                    <label class="form-label fw-bold small text-muted text-uppercase">Seleccionar Macho en Pubertad</label>
                                    <select wire:model="p_animal_id" class="form-select form-select-lg">
                                        <option value="">Seleccione Macho...</option>
                                        @foreach($activeInventory->where('sex', 'Macho')->where('stage.name', 'Pubertad') as $item)
                                            <option value="{{ $item->id }}">{{ $item->identifier ?? $item->management_lot }} - Ubic: {{ $item->barnSection->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('p_animal_id') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label fw-bold small text-muted text-uppercase">Rol / Destino</label>
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <div class="form-check card-radio p-0">
                                                <input class="form-check-input d-none" type="radio" wire:model="p_target_role" value="STUD" id="roleSTUD">
                                                <label class="form-check-label d-block p-3 border rounded-4 text-center cursor-pointer @if($p_target_role == 'STUD') border-primary bg-primary bg-opacity-10 @endif" for="roleSTUD">
                                                    <i class="ph ph-needle fs-2 mb-2 d-block text-primary"></i>
                                                    <span class="fw-bold d-block">STUD (Extractor)</span>
                                                    <span class="smallest text-muted">Macho Activo</span>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-check card-radio p-0">
                                                <input class="form-check-input d-none" type="radio" wire:model="p_target_role" value="CELADOR_DM1" id="roleDM1">
                                                <label class="form-check-label d-block p-3 border rounded-4 text-center cursor-pointer @if($p_target_role == 'CELADOR_DM1') border-primary bg-primary bg-opacity-10 @endif" for="roleDM1">
                                                    <i class="ph ph-magnifying-glass fs-2 mb-2 d-block text-primary"></i>
                                                    <span class="fw-bold d-block">Celador DM1</span>
                                                    <span class="smallest text-muted">Detección de Celo</span>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-check card-radio p-0">
                                                <input class="form-check-input d-none" type="radio" wire:model="p_target_role" value="CELADOR_DM2" id="roleDM2">
                                                <label class="form-check-label d-block p-3 border rounded-4 text-center cursor-pointer @if($p_target_role == 'CELADOR_DM2') border-primary bg-primary bg-opacity-10 @endif" for="roleDM2">
                                                    <i class="ph ph-magnifying-glass fs-2 mb-2 d-block text-primary"></i>
                                                    <span class="fw-bold d-block">Celador DM2</span>
                                                    <span class="smallest text-muted">Detección de Celo</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    @error('p_target_role') <span class="text-danger small d-block mt-2">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="mt-5 text-end">
                                <button type="submit" class="btn btn-info text-white px-5 py-2 rounded-pill fw-bold shadow-sm">
                                    <i class="ph ph-check-circle me-1"></i> Confirmar Promoción
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                @endif

            </div>
        </div>
    </div>

    <!-- STYLES ADICIONALES -->
    <style>
        .active-op {
            background: linear-gradient(90deg, rgba(13,110,253,0.1) 0%, transparent 100%) !important;
            border-left: 4px solid #0d6efd !important;
            color: #0d6efd !important;
        }
        .active-op .fw-bold { color: #0d6efd !important; }
        
        .active-op-sale {
            background: linear-gradient(90deg, rgba(25,135,84,0.1) 0%, transparent 100%) !important;
            border-left: 4px solid #198754 !important;
            color: #198754 !important;
        }
        
        .active-op-death {
            background: linear-gradient(90deg, rgba(220,53,69,0.1) 0%, transparent 100%) !important;
            border-left: 4px solid #dc3545 !important;
            color: #dc3545 !important;
        }

        .list-group-item {
            border: none;
            transition: all 0.2s ease;
        }
        .list-group-item:hover:not(.active) {
            background-color: #f8f9fa;
        }
        .letter-spacing-1 { letter-spacing: 1px; }
    </style>
</div>
