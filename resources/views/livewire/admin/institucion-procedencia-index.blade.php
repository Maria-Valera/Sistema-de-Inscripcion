<div class="main-container">
    @include('admin.institucion_procedencia.modales.createModal')
    @include('admin.institucion_procedencia.modales.editModal')
    @if (!$anioEscolarActivo)
        <div class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                <div>
                    <h5 class="alert-heading mb-1">Atención: No hay Calendario Escolar activo</h5>
                    <p class="mb-0">
                        Puedes ver los registros, pero <strong>no podrás crear, editar o eliminar</strong> instituciones
                        hasta que se registre un Calendario Escolar activo.
                        <a href="{{ route('admin.anio_escolar.index') }}" class="alert-link">Ir a Calendario Escolar</a>
                    </p>
                </div>
            </div>
        </div>
    @endif

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

    <div class="card-modern">
        <div class="card-header-modern">
            <div class="header-left">
                <div class="header-icon">
                    <i class="fas fa-school"></i>
                </div>
                <div>
                    <h3>Listado de Instituciones de Procedencia</h3>
                    <p>{{ $instituciones->total() }} registros encontrados</p>
                </div>
            </div>

            <div class="form-group-modern mb-2">
                <div class="search-modern">
                    <i class="fas fa-search"></i>
                    <input type="text" name="buscar" id="buscar" class="form-control-modern"
                        placeholder="Buscar institución o localidad..." wire:model.live="search">
                </div>
            </div>
            <div class="header-right">
                @php
                    $anioActivo = \App\Models\AnioEscolar::activos()->first();
                    $anioExtendido = \App\Models\AnioEscolar::where('status', 'Extendido')->first();
                    $mostrarAnio = $anioActivo ?? $anioExtendido;
                @endphp

                @if ($mostrarAnio)
                    <div class="d-flex align-items-center justify-content-between bg-light rounded px-2 py-1  border">
                        <div class="d-flex align-items-center">
                            <span class="badge bg-primary rounded me-2 py-1 px-2" style="font-size: 0.7rem;">
                                <i class="fas fa-calendar-check me-1"></i>
                                Calendario Escolar
                            </span>

                            <div class="d-flex align-items-center" style="font-size: 0.8rem;">
                                <span class="text-muted me-2">
                                    <i class="fas fa-play-circle text-primary me-1"></i>
                                    {{ \Carbon\Carbon::parse($mostrarAnio->inicio_anio_escolar)->format('d/m/Y') }}
                                </span>
                                <span class="text-muted me-2">
                                    <i class="fas fa-flag-checkered text-danger me-1"></i>
                                    {{ \Carbon\Carbon::parse($mostrarAnio->cierre_anio_escolar)->format('d/m/Y') }}
                                </span>
                            </div>
                        </div>
                    </div>
                @else
                    <div
                        class="d-flex align-items-center justify-content-between bg-warning bg-opacity-10 rounded px-2 py-1  border border-warning">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-exclamation-triangle text-warning me-1" style="font-size: 0.8rem;"></i>
                            <span class="fw-semibold" style="font-size: 0.8rem;">Sin año activo</span>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="card-body-modern">
            <div class="table-wrapper">
                <table class="table-modern overflow-hidden ">
                    <thead>
                        <tr class="text-center">
                            <th>#</th>
                            <th class="text-center">Institución</th>
                            <th class="text-center">Localidad</th>
                            <th class="text-center">Municipio</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>

                    <tbody class="text-center">
                        @if ($instituciones->isEmpty())
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">
                                        <div class="empty-icon">
                                            <i class="fas fa-inbox"></i>
                                        </div>
                                        <h4>No hay instituciones registradas</h4>
                                        <p>Agrega una nueva institución con el botón superior</p>
                                    </div>
                                </td>
                            </tr>
                        @endif
                        @foreach ($instituciones as $index => $item)
                            <tr class="  row-12">
                                <td>{{ $instituciones->firstItem() + $index }}</td>
                                <td class="title-main">{{ $item->nombre_institucion }}</td>
                                <td>{{ $item->localidad->nombre_localidad }}</td>
                                <td>{{ $item->localidad->municipio->nombre_municipio }}</td>
                                <td>{{ $item->localidad->municipio->estado->nombre_estado }}</td>
                                <td>
                                    @if ($item->status)
                                        <span class="status-badge status-active">
                                            <span class="status-dot"></span> Activo
                                        </span>
                                    @else
                                        <span class="status-badge status-inactive">
                                            <span class="status-dot"></span> Inactivo
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button wire:click="edit({{ $item->id }})" class="action-btn btn-edit"
                                            data-bs-toggle="modal" data-bs-target="#modalEditar"
                                            @if (!$anioEscolarActivo) disabled @endif
                                            title="{{ !$anioEscolarActivo ? 'Requiere Calendario Escolar activo' : 'Editar' }}">
                                            <i class="fas fa-pen text-white"></i>
                                        </button>
                                        <button class="action-btn btn-delete" data-bs-toggle="modal"
                                            data-bs-target="#confirmarEliminar{{ $item->id }}"
                                            @if (!$anioEscolarActivo) disabled @endif
                                            title="{{ !$anioEscolarActivo ? 'Requiere Calendario Escolar activo' : 'Eliminar' }}">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <div wire:ignore.self class="modal fade" id="confirmarEliminar{{ $item->id }}"
                                tabindex="-1" aria-labelledby="modalLabel{{ $item->id }}" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content modal-modern">
                                        <div class="modal-header-delete">
                                            <div class="modal-icon-delete">
                                                <i class="fas fa-trash-alt"></i>
                                            </div>
                                            <h5 class="modal-title-delete">Confirmar Eliminación</h5>
                                            <button type="button" class="btn-close-modal" data-bs-dismiss="modal">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                        <div class="modal-body-delete">
                                            <p>¿Deseas eliminar esta institución?</p>
                                            <p class="delete-warning">
                                                Esta acción no se puede deshacer.
                                            </p>
                                        </div>
                                        <div class="modal-footer-delete">
                                            <div class="footer-buttons">
                                                <button type="button" class="btn-modal-cancel"
                                                    data-bs-dismiss="modal">Cancelar</button>
                                                <button class="btn-modal-delete"
                                                    wire:click="destroy({{ $item->id }})">Eliminar</button>
                                            </div>
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
