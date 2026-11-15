<div class="main-container">
    @include('admin.institucion_procedencia.modales.createModal')
    @include('admin.institucion_procedencia.modales.editModal')
    @include('partials.alerta-modo-consulta')

    @if (session('success') || session('error'))
        <div class="alerts-container">
            @if (session('success'))
                <div class="alert-modern alert-success alert alert-dismissible fade show">
                    <div class="alert-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="alert-content">
                        <h4>Éxito</h4>
                        <p>{{ session('success') }}</p>
                    </div>
                    <button type="button" class="alert-close btn-close" data-bs-dismiss="alert">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert-modern alert-error alert alert-dismissible fade show">
                    <div class="alert-icon">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <div class="alert-content">
                        <h4>Error</h4>
                        <p>{{ session('error') }}</p>
                    </div>
                    <button type="button" class="alert-close btn-close" data-bs-dismiss="alert">
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
                    <i class="fas fa-school"></i>
                </div>
                <div>
                    <h3 class="fw-bold mb-0" style="font-size: 1.05rem; color: #1E293B;">Listado de Instituciones de Procedencia</h3>
                    <p class="text-muted mb-0" style="font-size: 0.82rem;">{{ $instituciones->total() }} registros encontrados</p>
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
                           placeholder="Buscar institución o localidad..."
                           wire:model.live="search"
                           style="padding-left: 40px !important; border-radius: 10px; height: 38px; font-size: 0.85rem;">
                </div>
            </div>
        </div>

        <div class="card-body-modern p-0">
            <div class="table-wrapper">
                <table class="table-modern">
                    <thead>
                        <tr class="text-center">
                            <th style="width: 70px;">#</th>
                            <th class="text-start">Institución</th>
                            <th style="width: 170px;">Localidad</th>
                            <th style="width: 170px;">Municipio</th>
                            <th style="width: 150px;">Estado</th>
                            <th style="width: 130px;">Estado</th>
                            <th style="width: 100px;">Acciones</th>
                        </tr>
                    </thead>

                    <tbody class="text-center">
                        @if ($instituciones->isEmpty())
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state text-center py-5">
                                        <div class="empty-icon text-muted mb-2 opacity-50">
                                            <i class="fas fa-school fa-3x"></i>
                                        </div>
                                        <h4 class="fw-bold" style="color: #1E293B; font-size: 1.1rem;">No hay instituciones registradas</h4>
                                        <p class="text-muted" style="font-size: 0.88rem;">Agrega una nueva institución con el botón superior.</p>
                                    </div>
                                </td>
                            </tr>
                        @endif
                        @foreach ($instituciones as $index => $item)
                            <tr wire:key="institucion-{{ $item->id }}">
                                <td class="text-muted" style="font-size: 0.85rem;">{{ $instituciones->firstItem() + $index }}</td>
                                <td class="text-start">
                                    <span class="fw-bold text-dark" style="font-size: 0.93rem;">
                                        <i class="fas fa-school text-primary me-2"></i>
                                        {{ $item->nombre_institucion }}
                                    </span>
                                </td>
                                <td>
                                    <span class="text-muted" style="font-size: 0.88rem;">
                                        {{ $item->localidad_nombre ?? ($item->localidad->nombre_localidad ?? '—') }}
                                    </span>
                                </td>
                                <td>
                                    <span class="text-muted" style="font-size: 0.88rem;">
                                        {{ $item->municipio_nombre ?? ($item->localidad->municipio->nombre_municipio ?? '—') }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge" style="background-color: #EFF6FF; color: #1E3A8A; font-weight: 600; padding: 0.35rem 0.75rem; border-radius: 8px; font-size: 0.82rem; border: 1px solid #DBEAFE;">
                                        {{ $item->estado_nombre ?? ($item->localidad->municipio->estado->nombre_estado ?? '—') }}
                                    </span>
                                </td>
                                <td>
                                    @if ($item->status)
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
                                                        <button wire:click="edit({{ $item->id }})"
                                                            wire:loading.attr="disabled"
                                                            wire:target="edit"
                                                            class="dropdown-item dropdown-item-modern item-edit"
                                                            type="button"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#modalEditar"
                                                            title="Editar">
                                                            <i class="fas fa-pen me-2" wire:loading.remove wire:target="edit({{ $item->id }})"></i>
                                                            <i class="fas fa-spinner fa-spin me-2 text-primary" wire:loading wire:target="edit({{ $item->id }})"></i>
                                                            <span>Editar</span>
                                                        </button>
                                                    </li>
                                                    <li>
                                                        <button class="dropdown-item dropdown-item-modern item-delete"
                                                            type="button"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#confirmarEliminar{{ $item->id }}"
                                                            title="Eliminar">
                                                            <i class="fas fa-trash-alt me-2"></i>
                                                            <span>Eliminar</span>
                                                        </button>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    @endif
                                </td>
                            </tr>

                            {{-- Modal Confirmar Eliminación (Design System) --}}
                            <div wire:ignore.self class="modal fade modal-danger-delete" id="confirmarEliminar{{ $item->id }}"
                                tabindex="-1" aria-labelledby="modalLabel{{ $item->id }}" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered" style="max-width: 480px;">
                                    <div class="modal-content modal-modern shadow-lg" style="border: 1px solid #E2E8F0; border-radius: 16px; overflow: hidden; background: #FFFFFF;">
                                        <div class="modal-header d-flex align-items-center justify-content-between px-4 py-3 bg-white border-bottom" style="border-bottom-color: #F1F5F9 !important;">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="d-inline-flex align-items-center justify-content-center flex-shrink-0"
                                                     style="width: 44px; height: 44px; border-radius: 12px; background-color: #FEE2E2; color: #EF4444;">
                                                    <i class="fas fa-trash-alt" style="font-size: 1.15rem;"></i>
                                                </div>
                                                <div>
                                                    <h5 class="modal-title m-0 fw-bold" id="modalLabel{{ $item->id }}" style="color: #1E293B; font-size: 1.1rem;">
                                                        Confirmar Eliminación
                                                    </h5>
                                                    <p class="text-muted m-0" style="font-size: 0.82rem;">Acción destructiva sobre institución</p>
                                                </div>
                                            </div>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                        </div>

                                        <div class="modal-body px-4 py-3" style="background-color: #FFFFFF;">
                                            <p class="fw-semibold mb-2" style="color: #1E293B; font-size: 0.95rem;">
                                                ¿Deseas eliminar esta institución del sistema?
                                            </p>
                                            <p class="text-muted mb-3" style="font-size: 0.85rem;">
                                                Esta acción no se puede deshacer.
                                            </p>
                                            <div class="p-3 d-flex align-items-center gap-3"
                                                 style="background-color: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px;">
                                                <div class="d-flex align-items-center justify-content-center rounded-3 flex-shrink-0"
                                                     style="width: 40px; height: 40px; background-color: #EFF6FF; color: #4A6FC1; font-size: 1.1rem;">
                                                    <i class="fas fa-school"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-bold text-dark" style="font-size: 0.9rem;">{{ $item->nombre_institucion }}</div>
                                                    <div class="text-muted" style="font-size: 0.8rem;">
                                                        {{ $item->localidad_nombre ?? ($item->localidad->nombre_localidad ?? '') }} - {{ $item->estado_nombre ?? ($item->localidad->municipio->estado->nombre_estado ?? '') }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="modal-footer d-flex justify-content-end align-items-center gap-2 px-4 py-3 bg-white border-top" style="border-top-color: #F1F5F9 !important;">
                                            <button type="button" class="btn-secondary-modern" data-bs-dismiss="modal">
                                                <i class="fas fa-times me-1"></i> Cancelar
                                            </button>
                                            <button type="button" class="btn"
                                                    style="background-color: #EF4444; color: #FFF; border-radius: 8px; font-weight: 600; padding: 9px 18px; border: none;"
                                                    wire:click="destroy({{ $item->id }})"
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

        <div class="mt-3">
            {{ $instituciones->links() }}
        </div>
    </div>

</div>
