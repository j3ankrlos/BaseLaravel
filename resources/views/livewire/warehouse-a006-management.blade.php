<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Gestión de Almacén A006</h2>
        <button class="btn btn-warning" wire:click="openRequestModal">
            <i class="ph ph-paper-plane-right pe-1"></i> Nueva Solicitud a A002
        </button>
    </div>

    <!-- Buscador Integrado -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="input-group">
                <span class="input-group-text bg-light"><i class="ph ph-magnifying-glass"></i></span>
                <input type="text" class="form-control" placeholder="Buscar por código o descripción en A006..." wire:model.live.debounce.300ms="search">
            </div>
        </div>
    </div>

    <!-- Tabla de Productos A006 -->
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
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    <i class="ph ph-package fs-1 d-block mb-2"></i>
                                    El almacén A006 no tiene existencias. Realiza una solicitud al Almacén A002 para llenarlo.
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

    <!-- Modal Formulario Solicitud -->
    <div class="modal fade" id="requestModal" tabindex="-1" wire:ignore.self aria-labelledby="requestModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title" id="requestModalLabel"><i class="ph ph-paper-plane-right me-2"></i> Solicitar a Almacén A002</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 pb-0">
                    <div class="mb-4 position-relative">
                        <label class="form-label fw-bold text-primary">Buscador de Productos en A002</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-primary"><i class="ph ph-magnifying-glass text-primary"></i></span>
                            <input type="text" class="form-control border-primary shadow-sm" 
                                   wire:model.live.debounce.300ms="productSearch" 
                                   placeholder="Escriba el código o nombre del producto..." 
                                   @if(count($requestItems) >= 24) disabled @endif>
                        </div>
                        
                        @if(strlen($productSearch ?? '') >= 2)
                            <div class="position-absolute w-100 bg-white border border-primary rounded shadow mt-1" style="z-index: 1050; max-height: 250px; overflow-y: auto;">
                                @forelse ($searchResults as $result)
                                    <div class="p-3 border-bottom hover-bg-light cursor-pointer" style="cursor: pointer;" wire:click="selectProduct({{ $result->id }})">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <span class="badge bg-secondary me-2">{{ $result->Codigo }}</span>
                                                <span class="fw-bold">{{ $result->Producto }}</span>
                                            </div>
                                            <div class="text-muted small">
                                                Stock: {{ number_format($result->Stock, 3, ',', '.') }} {{ $result->UMB }}
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="p-3 text-center text-muted">No se encontraron productos en A002.</div>
                                @endforelse
                            </div>
                        @endif
                    </div>

                    <form wire:submit.prevent="submitRequest" id="requestForm">
                        <div class="mb-2 d-flex justify-content-between align-items-center">
                            <span class="fw-bold fs-6 text-uppercase text-secondary small">Ítems a Solicitar ({{ count($requestItems) }}/24)</span>
                        </div>

                        <div class="table-responsive border rounded-3 mb-3 bg-white" style="max-height: 40vh; overflow-y: auto;">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <thead class="table-light position-sticky top-0 shadow-sm" style="z-index: 1;">
                                    <tr>
                                        <th style="width: 5%" class="text-center py-2">#</th>
                                        <th style="width: 13%">Código</th>
                                        <th style="width: 35%">Producto</th>
                                        <th style="width: 8%" class="text-center">UMB</th>
                                        <th style="width: 15%" class="text-center">Stock A002</th>
                                        <th style="width: 14%" class="text-center">Cantidad <span class="text-danger">*</span></th>
                                        <th style="width: 10%" class="text-center"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($requestItems as $index => $item)
                                        <tr>
                                            <td class="text-center fw-bold text-muted">{{ $index + 1 }}</td>
                                            <td><span class="badge bg-light text-dark border">{{ $item['codigo'] }}</span></td>
                                            <td class="text-wrap small fw-medium">{{ $item['producto'] }}</td>
                                            <td class="text-center text-muted small">{{ $item['umb'] }}</td>
                                            <td class="text-center">
                                                @php $stock = $item['stock_a002'] ?? 0; @endphp
                                                <span class="fw-bold {{ $stock <= 0 ? 'text-danger' : ($stock < $item['cantidad'] ? 'text-warning' : 'text-success') }}">
                                                    {{ number_format($stock, 3, ',', '.') }}
                                                </span>
                                            </td>
                                            <td>
                                                <input type="text" inputmode="decimal" class="form-control form-control-sm border-primary fw-bold text-end cantidad-input" wire:model="requestItems.{{ $index }}.cantidad" required>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-link text-danger p-0" wire:click="removeItem({{ $index }})">
                                                    <i class="ph ph-trash fs-5"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-5 text-muted">
                                                <i class="ph ph-shopping-cart fs-1 d-block mb-2 opacity-25"></i>
                                                Seleccione productos usando el buscador.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold small text-uppercase text-secondary">Observaciones</label>
                            <textarea class="form-control bg-light" wire:model="reqComentarios" rows="2" placeholder="Opcional..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer bg-light border-0 py-3">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-warning fw-bold px-4 shadow-sm" wire:click="submitRequest" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="submitRequest">
                            <i class="ph ph-paper-plane-right me-1"></i> Enviar a A002
                        </span>
                        <span wire:loading wire:target="submitRequest">
                            <i class="ph ph-spinner ph-spin me-1"></i> Procesando...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    @script
    <script>
        document.addEventListener('keydown', function(e) {
            const el = e.target;
            if (!el.classList.contains('cantidad-input')) return;
            if (e.code === 'NumpadDecimal' || (e.key === '.' && e.code === 'Period' && e.location === 3)) {
                e.preventDefault();
                const start = el.selectionStart; el.value = el.value.substring(0, start) + ',' + el.value.substring(el.selectionEnd);
                el.setSelectionRange(start + 1, start + 1); el.dispatchEvent(new Event('input', { bubbles: true }));
            }
        }, true);
    </script>
    @endscript
</div>
```
