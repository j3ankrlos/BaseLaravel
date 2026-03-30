<div>
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="fw-bold"><i class="ph ph-database me-2 text-primary"></i> Migración de Datos Centralizada</h2>
            <p class="text-muted">Espacio exclusivo para administradores. Cargue datos maestros al sistema desde plantillas de Excel.</p>
        </div>
    </div>

    <div class="row g-4">
        <!-- Card: Importación de Personal -->
        <div class="col-md-4">
            <div class="card h-100 shadow-sm border-0 transition-hover">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-4">
                        <div class="bg-primary-subtle p-3 rounded-3 me-3">
                            <i class="ph ph-users-three fs-2 text-primary"></i>
                        </div>
                        <div>
                            <h4 class="mb-1 fw-bold">Gestión de Personal</h4>
                            <span class="badge bg-primary">Tabla: employees</span>
                        </div>
                    </div>
                    
                    <p class="text-muted small mb-4">
                        Importe la nómina de empleados, asignando automáticamente áreas, cargos, unidades y centros de costo.
                    </p>

                    <div class="upload-zone p-4 border-2 border-dashed rounded-3 text-center mb-4 {{ $employeeFile ? 'border-primary bg-light' : 'border-secondary' }}" 
                         onclick="document.getElementById('employeeFile').click()" style="cursor: pointer;">
                        <i class="ph {{ $employeeFile ? 'ph-check-circle text-primary' : 'ph-cloud-arrow-up text-muted' }} fs-1 mb-2"></i>
                        <p class="mb-0 {{ $employeeFile ? 'fw-bold text-primary text-truncate' : 'text-muted' }}">
                            {{ $employeeFile ? $employeeFile->getClientOriginalName() : 'Seleccionar Personal' }}
                        </p>
                        <input type="file" id="employeeFile" wire:model="employeeFile" class="d-none" accept=".xlsx,.xls,.csv">
                    </div>

                    <div class="d-grid">
                        <button wire:click="importEmployees" class="btn btn-primary py-2 fw-bold" 
                                wire:loading.attr="disabled" {{ !$employeeFile ? 'disabled' : '' }}>
                            <span wire:loading.remove wire:target="importEmployees">
                                <i class="ph ph-upload-simple me-2"></i> Importar
                            </span>
                            <span wire:loading wire:target="importEmployees">
                                <i class="ph ph-spinner ph-spin me-2"></i> Procesando...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card: Importación de Partos -->
        <div class="col-md-4">
            <div class="card h-100 shadow-sm border-0 transition-hover">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-4">
                        <div class="bg-success-subtle p-3 rounded-3 me-3">
                            <i class="ph ph-baby fs-2 text-success"></i>
                        </div>
                        <div>
                            <h4 class="mb-1 fw-bold">Registro de Partos</h4>
                            <span class="badge bg-success">Tabla: births / birth_details</span>
                        </div>
                    </div>
                    
                    <p class="text-muted small mb-4">
                        Cargue el historial de partos. El sistema creará automáticamente los registros de la madre y los lechones.
                    </p>

                    <div class="upload-zone p-4 border-2 border-dashed rounded-3 text-center mb-4 {{ $birthFile ? 'border-success bg-light' : 'border-secondary' }}" 
                         onclick="document.getElementById('birthFile').click()" style="cursor: pointer;">
                        <i class="ph {{ $birthFile ? 'ph-check-circle text-success' : 'ph-cloud-arrow-up text-muted' }} fs-1 mb-2"></i>
                        <p class="mb-0 {{ $birthFile ? 'fw-bold text-success text-truncate' : 'text-muted' }}">
                            {{ $birthFile ? $birthFile->getClientOriginalName() : 'Seleccionar Partos' }}
                        </p>
                        <input type="file" id="birthFile" wire:model="birthFile" class="d-none" accept=".xlsx,.xls,.csv">
                    </div>

                    <div class="d-grid">
                        <button wire:click="importBirths" class="btn btn-success py-2 fw-bold text-white" 
                                wire:loading.attr="disabled" {{ !$birthFile ? 'disabled' : '' }}>
                            <span wire:loading.remove wire:target="importBirths">
                                <i class="ph ph-upload-simple me-2"></i> Cargar Partos
                            </span>
                            <span wire:loading wire:target="importBirths">
                                <i class="ph ph-spinner ph-spin me-2"></i> Procesando...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card: Importación de Inventario Animal -->
        <div class="col-md-4">
            <div class="card h-100 shadow-sm border-0 transition-hover">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-4">
                        <div class="bg-warning-subtle p-3 rounded-3 me-3">
                            <i class="ph ph-horse fs-2 text-warning"></i>
                        </div>
                        <div>
                            <h4 class="mb-1 fw-bold">Inventario Animal</h4>
                            <span class="badge bg-warning text-dark">Tabla: animals</span>
                        </div>
                    </div>
                    
                    <p class="text-muted small mb-4">
                        Carga masiva de animales con genealogía (Padre/Madre), consanguinidad y vinculación automática por internal_id.
                    </p>

                    <div class="upload-zone p-4 border-2 border-dashed rounded-3 text-center mb-4 {{ $animalFile ? 'border-warning bg-light' : 'border-secondary' }}" 
                         onclick="document.getElementById('animalFile').click()" style="cursor: pointer;">
                        <i class="ph {{ $animalFile ? 'ph-check-circle text-warning' : 'ph-cloud-arrow-up text-muted' }} fs-1 mb-2"></i>
                        <p class="mb-0 {{ $animalFile ? 'fw-bold text-warning text-truncate' : 'text-muted' }}">
                            {{ $animalFile ? $animalFile->getClientOriginalName() : 'Seleccionar Inventario' }}
                        </p>
                        <input type="file" id="animalFile" wire:model="animalFile" class="d-none" accept=".xlsx,.xls,.csv">
                    </div>

                    <div class="d-grid">
                        <button wire:click="importAnimals" class="btn btn-warning py-2 fw-bold text-dark" 
                                wire:loading.attr="disabled" {{ !$animalFile ? 'disabled' : '' }}>
                            <span wire:loading.remove wire:target="importAnimals">
                                <i class="ph ph-upload-simple me-2"></i> Cargar Inventario
                            </span>
                            <span wire:loading wire:target="importAnimals">
                                <i class="ph ph-spinner ph-spin me-2"></i> Procesando...
                            </span>
                        </button>
                    </div>

                    <div class="mt-3 text-center">
                        <small class="text-muted"><i class="ph ph-info me-1"></i> Requiere: I-D, F. INICIO, PADRE, MADRE.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .transition-hover:hover {
            transform: translateY(-5px);
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
        }
        .border-dashed {
            border-style: dashed !important;
        }
        .bg-primary-subtle { background-color: #e0f2fe; }
        .bg-success-subtle { background-color: #dcfce7; }
        .bg-warning-subtle { background-color: #fef3c7; }
    </style>
</div>
