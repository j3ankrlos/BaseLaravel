<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Gestión de Almacén A002</h2>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#importExcelModal">
                <i class="ph ph-file-csv"></i> Cargar Excel
            </button>
            <button class="btn btn-primary" wire:click="openModal">
                <i class="ph ph-plus-circle"></i> Nuevo Producto
            </button>
        </div>
    </div>

    <!-- Buscador Integrado -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="input-group">
                <span class="input-group-text bg-light"><i class="ph ph-magnifying-glass"></i></span>
                <input type="text" class="form-control" placeholder="Buscar por código o descripción del producto..." wire:model.live.debounce.300ms="search">
            </div>
        </div>
    </div>

    <!-- Tabla de Productos -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Código</th>
                            <th>Producto</th>
                            <th>UMB</th>
                            <th>Clasificación</th>
                            <th class="text-end">Stock Actual</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($products as $product)
                            <tr>
                                <td><span class="badge bg-secondary">{{ $product->Codigo }}</span></td>
                                <td class="text-wrap" style="max-width: 250px;">{{ $product->Producto }}</td>
                                <td>{{ $product->UMB }}</td>
                                <td>{{ $product->Clasificacion ?? '-' }}</td>
                                <td class="text-end fw-bold {{ $product->Stock <= $product->StockMin ? 'text-danger' : 'text-success' }}">
                                    {{ number_format($product->Stock, 3, ',', '.') }}
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-primary" wire:click="editProduct({{ $product->id }})">
                                        <i class="ph ph-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" wire:click="confirmDelete({{ $product->id }})">
                                        <i class="ph ph-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">
                                    <i class="ph ph-package fs-1 d-block mb-2"></i>
                                    No se encontraron productos en el almacén A002.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($products->hasPages())
        <div class="card-footer bg-white pt-3 pb-1 border-top-0">
            {{ $products->links() }}
        </div>
        @endif
    </div>

    <!-- Modal Formulario Nuevo / Editar -->
    <div class="modal fade" id="productModal" tabindex="-1" wire:ignore.self aria-labelledby="productModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="productModalLabel">{{ $isEditMode ? 'Editar Producto' : 'Nuevo Producto' }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form wire:submit.prevent="saveProduct">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label fw-bold small text-uppercase text-secondary">Código <span class="text-danger">*</span></label>
                                <input type="text" class="form-control bg-light" wire:model="Codigo" required>
                                @error('Codigo') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-bold small text-uppercase text-secondary">Descripción del Producto <span class="text-danger">*</span></label>
                                <input type="text" class="form-control bg-light" wire:model="Producto" required>
                                @error('Producto') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase text-secondary">UMB <span class="text-danger">*</span></label>
                                <input type="text" class="form-control bg-light" wire:model="UMB" required>
                                @error('UMB') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase text-secondary">Clasificación</label>
                                <input type="text" class="form-control bg-light" wire:model="Clasificacion">
                                @error('Clasificacion') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="col-12"><hr class="my-3 opacity-50"></div>
                            
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-uppercase text-secondary">Stock Actual</label>
                                <input type="number" step="0.01" class="form-control bg-light fw-bold" wire:model="Stock" required>
                                @error('Stock') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-uppercase text-secondary">Stock Mínimo</label>
                                <input type="number" step="0.01" class="form-control bg-light" wire:model="StockMin" required>
                                @error('StockMin') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-uppercase text-secondary">Solicitud Mínima</label>
                                <input type="number" step="0.01" class="form-control bg-light" wire:model="SolicitudMin" required>
                                @error('SolicitudMin') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary px-4 shadow-sm fw-bold" wire:loading.attr="disabled">
                                <i class="ph ph-floppy-disk me-1"></i>
                                <span wire:loading.remove wire:target="saveProduct">Guardar Cambios</span>
                                <span wire:loading wire:target="saveProduct">Guardando...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Importación Excel -->
    <div class="modal fade" id="importExcelModal" tabindex="-1" wire:ignore.self aria-labelledby="importExcelModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="importExcelModalLabel">Cargar Excel (Almacén A002)</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info border-0 bg-info-subtle text-info small mb-4">
                        <i class="ph ph-info me-2"></i>
                        <strong>Carga Inteligente / Completa:</strong>
                        <ul class="mb-0 ps-3 mt-2">
                            <li>Se crearán nuevos productos o se actualizarán los existentes (incluyendo clasificación y stocks mínimos).</li>
                            <li>Los productos fuera del Excel se pondrán en 0 existencias automáticamente.</li>
                        </ul>
                    </div>

                    <form wire:submit.prevent="importExcel">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-secondary text-uppercase small">Archivo Excel (.xlsx, .csv)</label>
                            <input type="file" class="form-control bg-light" wire:model="excelFile" accept=".xlsx,.xls,.csv" required>
                            
                            <div wire:loading wire:target="excelFile" class="mt-2 text-primary small">
                                <i class="ph ph-spinner ph-spin me-1"></i> Subiendo archivo...
                            </div>
                            @error('excelFile') <span class="text-danger d-block mt-1 small">{{ $message }}</span> @enderror
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cerrar</button>
                            <button type="submit" class="btn btn-success px-4" wire:loading.attr="disabled" wire:target="importExcel">
                                <span wire:loading.remove wire:target="importExcel">
                                    <i class="ph ph-upload-simple me-1"></i> Procesar Inventario
                                </span>
                                <span wire:loading wire:target="importExcel">
                                    <i class="ph ph-spinner ph-spin me-1"></i> Procesando...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
