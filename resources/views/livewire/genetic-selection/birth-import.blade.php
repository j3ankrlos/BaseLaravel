<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-xl-8 col-lg-10">
            <!-- Header Section -->
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h4 class="mb-0 fw-bold text-dark">
                        <a href="/genetic-selection/list" wire:navigate class="text-muted pe-2" title="Volver al Listado"><i class="ph ph-arrow-left"></i></a>
                        Importación Masiva de Partos
                    </h4>
                    <p class="text-muted small mt-1 mb-0 ps-4 ms-2">Cargue su archivo Excel siguiendo el formato establecido para poblar el sistema rápidamente.</p>
                </div>
                <div class="text-end">
                    <span class="badge bg-soft-info text-info rounded-pill px-3 py-2">
                        <i class="ph ph-file-xls me-1"></i> Formato soportado: XLSX, XLS, CSV
                    </span>
                </div>
            </div>

            <!-- Upload Card -->
            <div class="card shadow-sm border-0 mb-4 overflow-hidden">
                <div class="card-body p-0">
                    <div class="row g-0">
                        <div class="col-lg-5 bg-light p-5 d-flex flex-column justify-content-center border-end">
                            <div class="text-center mb-4">
                                <div class="icon-shape bg-primary text-white rounded-circle shadow-lg mb-4 mx-auto" style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center;">
                                    <i class="ph ph-upload-simple fs-1"></i>
                                </div>
                                <h5 class="fw-bold">Paso 1: Seleccionar Archivo</h5>
                                <p class="text-muted small px-4">Asegúrese de que las columnas coincidan con el formato de la tabla de partos.</p>
                            </div>

                            <form wire:submit.prevent="import">
                                <div class="mb-4">
                                    <div 
                                        x-data="{ isUploading: false, progress: 0 }"
                                        x-on:livewire-upload-start="isUploading = true"
                                        x-on:livewire-upload-finish="isUploading = false"
                                        x-on:livewire-upload-error="isUploading = false"
                                        x-on:livewire-upload-progress="progress = $event.detail.progress"
                                    >
                                        <div class="upload-zone position-relative rounded-3 border-dashed p-4 text-center cursor-pointer mb-2" 
                                             style="border: 2px dashed #dee2e6; transition: border-color .3s ease;"
                                             onclick="document.getElementById('excelFile').click()">
                                            <input type="file" id="excelFile" wire:model="excelFile" class="d-none">
                                            
                                            @if($excelFile)
                                                <div class="text-success">
                                                    <i class="ph ph-file-check fs-2 mb-2"></i>
                                                    <div class="small fw-bold">{{ $excelFile->getClientOriginalName() }}</div>
                                                    <div class="text-muted smaller">{{ number_format($excelFile->getSize() / 1024, 2) }} KB</div>
                                                </div>
                                            @else
                                                <div class="text-muted py-2">
                                                    <i class="ph ph-hand-pointing fs-2 mb-2"></i>
                                                    <p class="mb-0 small">Haga clic o arrastre el archivo aquí</p>
                                                </div>
                                            @endif

                                            <!-- Progress Bar -->
                                            <div x-show="isUploading" class="progress mt-3" style="height: 4px;">
                                                <div class="progress-bar bg-primary" :style="`width: ${progress}%`" role="progressbar"></div>
                                            </div>
                                        </div>
                                        @error('excelFile') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary w-100 py-3 fw-bold shadow-sm" {{ !$excelFile || $importing ? 'disabled' : '' }}>
                                    <span wire:loading.remove wire:target="import">
                                        <i class="ph ph-rocket-launch me-2"></i> Iniciar Importación
                                    </span>
                                    <span wire:loading wire:target="import">
                                        <i class="ph ph-spinner ph-spin me-2"></i> Procesando Archivo...
                                    </span>
                                </button>
                            </form>
                        </div>

                        <div class="col-lg-7 p-5">
                            <h6 class="fw-bold mb-4"><i class="ph ph-list-checks me-2 text-primary"></i> Guía de Columnas</h6>
                            
                            <div class="table-responsive">
                                <table class="table table-sm table-borderless small">
                                    <thead class="text-muted border-bottom">
                                        <tr>
                                            <th>Columna Excel</th>
                                            <th>Campo Sistema</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr><td><code class="text-indigo">Fecha</code></td><td class="text-muted">Fecha del Parto (d/m/Y)</td></tr>
                                        <tr><td><code class="text-indigo">Vuelta</code></td><td class="text-muted">Número de Ciclo PIC</td></tr>
                                        <tr><td><code class="text-indigo">PIC</code></td><td class="text-muted">Día del Ciclo PIC (1-999)</td></tr>
                                        <tr><td><code class="text-indigo">JAULA</code></td><td class="text-muted">Número de Jaula</td></tr>
                                        <tr><td><code class="text-indigo">IDMADRE</code></td><td class="text-muted">Tag de la Madre</td></tr>
                                        <tr><td><code class="text-indigo">PARIDAD</code></td><td class="text-muted">Número de Parto de la Madre</td></tr>
                                        <tr><td><code class="text-indigo">GENETICA</code></td><td class="text-muted">Raza (YORK, YORK-T...)</td></tr>
                                        <tr><td><code class="text-indigo">ID OREJA</code></td><td class="text-muted">ID Individual (OREJA)</td></tr>
                                        <tr><td><code class="text-indigo">ID ARETE</code></td><td class="text-muted">Arete Generado</td></tr>
                                        <tr><td><code class="text-indigo">SEXO</code></td><td class="text-muted">Hembra / Macho</td></tr>
                                        <tr><td><code class="text-indigo">Responsable</code></td><td class="text-muted">Nombre del Operador</td></tr>
                                        <tr><td><code class="text-indigo">Lote Maternidad</code></td><td class="text-muted">Código del Lote</td></tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-4 p-3 bg-soft-warning border-start border-4 border-warning rounded">
                                <p class="mb-0 small text-warning-emphasis">
                                    <i class="ph ph-warning-circle me-1 fw-bold"></i> 
                                    <strong>Importante:</strong> El sistema agrupa automáticamente las filas que pertenecen al mismo parto basándose en la Madre, Fecha, Sala y Jaula.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Results Section -->
            @if($results)
                <div class="card shadow-lg border-0 bg-white animate__animated animate__fadeInUp">
                    <div class="card-body p-5">
                        <div class="text-center">
                            @if($results['success'])
                                <div class="icon-shape bg-soft-success text-success rounded-circle mb-4 mx-auto" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                                    <i class="ph ph-check-circle fs-1"></i>
                                </div>
                                <h4 class="fw-bold text-success mb-2">¡Importación Exitosa!</h4>
                                <div class="row mt-4 g-3">
                                    <div class="col-6">
                                        <div class="p-3 bg-light rounded-3">
                                            <h2 class="mb-0 fw-bold">{{ $results['births'] }}</h2>
                                            <div class="text-muted small text-uppercase fw-bold">Partos Creados</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="p-3 bg-light rounded-3">
                                            <h2 class="mb-0 fw-bold">{{ $results['details'] }}</h2>
                                            <div class="text-muted small text-uppercase fw-bold">Lechones Registrados</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-5 d-flex gap-2 justify-content-center">
                                    <a href="/genetic-selection/list" wire:navigate class="btn btn-primary px-4">Ir al Listado de Partos</a>
                                    <button wire:click="$set('results', null)" class="btn btn-outline-secondary px-4">Importar otro archivo</button>
                                </div>
                            @else
                                <div class="icon-shape bg-soft-danger text-danger rounded-circle mb-4 mx-auto" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                                    <i class="ph ph-x-circle fs-1"></i>
                                </div>
                                <h4 class="fw-bold text-danger mb-2">Error en la Importación</h4>
                                <p class="text-muted">{{ $results['error'] }}</p>
                                <button wire:click="$set('results', null)" class="btn btn-danger px-4 mt-3">Reintentar</button>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
    .upload-zone:hover {
        border-color: #0d6efd !important;
        background-color: #f8f9fa;
    }
    .bg-soft-info { background-color: rgba(13, 202, 240, 0.1); }
    .bg-soft-success { background-color: rgba(25, 135, 84, 0.1); }
    .bg-soft-danger { background-color: rgba(220, 53, 69, 0.1); }
    .bg-soft-warning { background-color: rgba(255, 193, 7, 0.1); }
    .text-indigo { color: #6610f2; }
    .smaller { font-size: 0.75rem; }
    .cursor-pointer { cursor: pointer; }
</style>
