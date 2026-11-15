<div class="main-container">
    @include('admin.localidad.modales.createModal')
    @include('admin.localidad.modales.editModal')
    @include('partials.alerta-modo-consulta')

    @if (session('success') || session('error'))
        <div class="alerts-container">
            @if (session('success'))
                <div class="alert-modern alert-success alert alert-dismissible fade show" role="alert">
                    <div class="alert-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="alert-content">
                        <h4>Éxito</h4>
                        <p>{{ session('success') }}</p>
                    </div>
                    <button type="button" class="alert-close btn-close" data-bs-dismiss="alert" aria-label="Cerrar">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert-modern alert-error alert alert-dismissible fade show" role="alert">
                    <div class="alert-icon">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <div class="alert-content">
                        <h4>Error</h4>
                        <p>{{ session('error') }}</p>
                    </div>
                    <button type="button" class="alert-close btn-close" data-bs-dismiss="alert" aria-label="Cerrar">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif
        </div>
    @endif

    <div class="card-modern shadow-sm">
        <div class="card-header-modern d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="header-left d-flex align-items-center gap-3">
                <div class="header-icon d-flex align-items-center justify-content-center"
                     style="width: 40px; height: 40px; border-radius: 10px; background-color: #EFF6FF; color: #4A6FC1;">
                    <i class="fas fa-list-ul"></i>
                </div>
                <div>
                    <h3 class="fw-bold mb-0" style="font-size: 1.05rem; color: #1E293B;">Listado de Localidades</h3>
                    <p class="text-muted mb-0" style="font-size: 0.82rem;">{{ $localidades->total() }} registros encontrados</p>
                </div>
            </div>

            {{-- Buscador Redondeado con wire:model.live (Design System) --}}
            <div class="header-right">
                <div class="search-wrapper position-relative" style="width: 260px;">
                    <i class="fas fa-search position-absolute text-muted" style="left: 15px; top: 50%; transform: translateY(-50%); z-index: 10; font-size: 0.85rem;"></i>
                    <input type="text"
                           name="buscar"
                           id="buscar"
                           class="form-control search-input-modern pl-5"
                           placeholder="Buscar localidad..."
                           wire:model.live="search"
                           style="padding-left: 40px !important; border-radius: 10px; height: 38px; font-size: 0.85rem;">
                </div>
            </div>
        </div>

        <div class="card-body-modern p-0">
            <div class="table-wrapper">
                <table class="table-modern" id="tablaLocalidades">
                    <thead>
                        <tr class="text-center">
                            <th style="width: 70px;">#</th>
                            <th class="text-start">Nombre de la Localidad</th>
                            <th style="width: 220px;">Municipio</th>
                            <th style="width: 140px;">Estado</th>
                            <th style="width: 100px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="text-center">
                        @if ($localidades->isEmpty())
                            <tr>
                                <td colspan="5">
                                    <div class="empty-state text-center py-5">
                                        <div class="empty-icon text-muted mb-2 opacity-50">
                                            <i class="fas fa-map-pin fa-3x"></i>
                                        </div>
                                        <h4 class="fw-bold" style="color: #1E293B; font-size: 1.1rem;">No hay localidades registradas</h4>
                                        <p class="text-muted" style="font-size: 0.88rem;">Agrega una nueva localidad con el botón superior.</p>
                                    </div>
                                </td>
                            </tr>
                        @endif

                        @foreach ($localidades as $index => $datos)
                            <tr>
                                <td class="text-muted" style="font-size: 0.85rem;">{{ $localidades->firstItem() + $index }}</td>
                                <td class="text-start">
                                    <span class="fw-bold text-dark" style="font-size: 0.93rem;">
                                        {{ $datos->nombre_localidad }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge" style="background-color: #EFF6FF; color: #1E3A8A; font-weight: 600; padding: 0.4rem 0.8rem; border-radius: 8px; font-size: 0.85rem; border: 1px solid #DBEAFE;">
                                        <i class="fas fa-city text-primary me-1"></i>
                                        {{ $datos->nombre_municipio }}
                                    </span>
                                </td>
                                <td>
                                    @if ($datos->status)
                                        <span class="badge badge-success px-3 py-1">
                                            <i class="fas fa-check-circle me-1" style="font-size: 0.75rem;"></i> Activo
                                        </span>
                                    @else
                                        <span class="badge badge-danger px-3 py-1">
                                            <i class="fas fa-times-circle me-1" style="font-size: 0.75rem;"></i> Inactivo
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if (\App\Support\AnioEscolarSeleccionado::esActual())
                                        <div class="action-buttons justify-content-center">
                                            <div class="dropdown text-center">
                                                <button class="action-btn"
                                                        type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Acciones">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-modern">
                                                    <li>
                                                        <button wire:click="edit({{ $datos->id }})"
                                                            class="dropdown-item dropdown-item-modern item-edit"
                                                            type="button"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#modalEditar"
                                                            title="Editar">
                                                            <i class="fas fa-pen me-2"></i>
                                                            <span>Editar</span>
                                                        </button>
                                                    </li>
                                                    <li>
                                                        <button class="dropdown-item dropdown-item-modern item-delete"
                                                            type="button"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#confirmarEliminar{{ $datos->id }}"
                                                            title="Eliminar">
                                                            <i class="fas fa-trash-alt me-2"></i>
                                                            <span>Eliminar</span>
                                                        </button>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-muted" style="font-size: 0.8rem;">Solo consulta</span>
                                    @endif
                                </td>
                            </tr>

                            {{-- Modal Confirmar Eliminación con Design System --}}
                            <div wire:ignore.self class="modal fade modal-danger-delete" id="confirmarEliminar{{ $datos->id }}"
                                tabindex="-1" aria-labelledby="modalLabel{{ $datos->id }}" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered" style="max-width: 480px;">
                                    <div class="modal-content modal-modern shadow-lg" style="border: 1px solid #E2E8F0; border-radius: 16px; overflow: hidden; background: #FFFFFF;">
                                        <div class="modal-header d-flex align-items-center justify-content-between px-4 py-3 bg-white border-bottom" style="border-bottom-color: #F1F5F9 !important;">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="d-inline-flex align-items-center justify-content-center flex-shrink-0"
                                                     style="width: 44px; height: 44px; border-radius: 12px; background-color: #FEE2E2; color: #EF4444;">
                                                    <i class="fas fa-trash-alt" style="font-size: 1.15rem;"></i>
                                                </div>
                                                <div>
                                                    <h5 class="modal-title m-0 fw-bold" id="modalLabel{{ $datos->id }}" style="color: #1E293B; font-size: 1.1rem;">
                                                        Confirmar Eliminación
                                                    </h5>
                                                    <p class="text-muted m-0" style="font-size: 0.82rem;">Acción destructiva sobre localidad</p>
                                                </div>
                                            </div>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                        </div>

                                        <div class="modal-body px-4 py-3" style="background-color: #FFFFFF;">
                                            <p class="fw-semibold mb-2" style="color: #1E293B; font-size: 0.95rem;">
                                                ¿Deseas eliminar esta localidad del sistema?
                                            </p>
                                            <p class="text-muted mb-3" style="font-size: 0.85rem;">
                                                Esta acción no se puede deshacer.
                                            </p>
                                            <div class="p-3 d-flex align-items-center gap-3"
                                                 style="background-color: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px;">
                                                <div class="d-flex align-items-center justify-content-center rounded-3 flex-shrink-0"
                                                     style="width: 40px; height: 40px; background-color: #EFF6FF; color: #4A6FC1; font-size: 1.1rem;">
                                                    <i class="fas fa-map-pin"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-bold text-dark" style="font-size: 0.9rem;">{{ $datos->nombre_localidad }}</div>
                                                    <div class="text-muted" style="font-size: 0.8rem;">Municipio: {{ $datos->nombre_municipio }}</div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="modal-footer d-flex justify-content-end align-items-center gap-2 px-4 py-3 bg-white border-top" style="border-top-color: #F1F5F9 !important;">
                                            <button type="button" class="btn-secondary-modern" data-bs-dismiss="modal">
                                                <i class="fas fa-times me-1"></i> Cancelar
                                            </button>
                                            <button type="button" class="btn"
                                                    style="background-color: #EF4444; color: #FFF; border-radius: 8px; font-weight: 600; padding: 9px 18px; border: none;"
                                                    wire:click="destroy({{ $datos->id }})"
                                                    data-bs-dismiss="modal">
                                                <i class="fas fa-trash-alt me-1"></i> Eliminar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">
        {{ $localidades->links() }}
    </div>
</div>
