<div>
    @if (session()->has('success') || session()->has('error'))
        <div class="alerts-container mb-3">
            @if (session()->has('success'))
                <div class="alert-modern alert-success alert alert-dismissible fade show">
                    <div class="alert-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="alert-content">
                        <h4>Éxito</h4>
                        <p>{{ session('success') }}</p>
                    </div>
                    <button type="button" class="alert-close btn-close" data-bs-dismiss="alert">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif

            @if (session()->has('error'))
                <div class="alert-modern alert-error alert alert-dismissible fade show">
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

    <!-- Card: Agregar Estudios Realizados -->
    <div class="card-modern shadow-sm mb-4">
        <div class="card-header-modern border-bottom px-4 py-3 bg-white" style="border-bottom: 1px solid #E2E8F0 !important;">
            <div class="header-left d-flex align-items-center gap-3">
                <div class="header-icon d-flex align-items-center justify-content-center flex-shrink-0"
                     style="width: 40px; height: 40px; border-radius: 10px; background-color: #EFF6FF; color: #4A6FC1;">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <div>
                    <h3 class="fw-bold mb-0" style="font-size: 1.05rem; color: #1E293B;">Agregar Estudios Realizados</h3>
                    <p class="text-muted mb-0" style="font-size: 0.82rem;">Seleccione los títulos o especialidades del docente</p>
                </div>
            </div>
        </div>

        <div class="card-body-modern p-4">
            <div class="d-flex flex-column flex-md-row gap-3 align-items-stretch align-items-md-end">
                <div class="flex-grow-1" wire:ignore>
                    <label for="estudios_realizados_id" class="form-label-modern mb-1 fw-semibold" style="color: #1E293B;">
                        <i class="fas fa-graduation-cap me-1" style="color: #4A6FC1;"></i>
                        Estudios Realizados <span class="text-danger">*</span>
                    </label>

                    <select id="estudios_realizados_id"
                        class="form-control-modern selectpicker @error('estudiosId') is-invalid @enderror"
                        data-live-search="true" data-size="8" data-style="btn-default" data-width="100%"
                        wire:model.live="estudiosId">
                        <option value="">Seleccione un estudio</option>
                        @foreach ($estudios as $titulo)
                            <option value="{{ $titulo->id }}">{{ $titulo->estudios }}</option>
                        @endforeach
                    </select>

                    @error('estudiosId')
                        <div class="invalid-feedback-modern d-block">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $message }}
                        </div>
                    @enderror
                    <small class="form-text-modern d-flex align-items-center gap-1 mt-1 text-muted">
                        <i class="fas fa-plus-circle text-primary"></i>
                        ¿No encuentra el título?
                        <a href="#" class="text-primary text-decoration-none fw-medium" data-bs-toggle="modal"
                            data-bs-target="#modalCrearEstudio">
                            Crear nuevo estudio
                        </a>
                    </small>
                </div>

                <div class="flex-shrink-0" style="margin-bottom: 1.5rem;">
                    <button class="btn-primary-modern d-inline-flex align-items-center justify-content-center gap-2"
                        wire:click="agregarEstudio" wire:loading.attr="disabled" style="height: 42px; padding: 0 1.25rem;">
                        <span wire:loading.remove wire:target="agregarEstudio">
                            <i class="fas fa-plus"></i> Agregar Estudio
                        </span>
                        <span wire:loading wire:target="agregarEstudio">
                            <i class="fas fa-spinner fa-spin"></i> Agregando...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Card: Tabla de Estudios Asignados -->
    <div class="card-modern shadow-sm mb-4">
        <div class="card-header-modern border-bottom px-4 py-3 bg-white" style="border-bottom: 1px solid #E2E8F0 !important;">
            <div class="header-left d-flex align-items-center gap-3">
                <div class="header-icon d-flex align-items-center justify-content-center flex-shrink-0"
                     style="width: 40px; height: 40px; border-radius: 10px; background-color: #EFF6FF; color: #4A6FC1;">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div>
                    <h3 class="fw-bold mb-0" style="font-size: 1.05rem; color: #1E293B;">Estudios Asignados</h3>
                    <p class="text-muted mb-0" style="font-size: 0.82rem;">{{ $estudiosAsignados->count() }} títulos o especialidades registradas</p>
                </div>
            </div>
        </div>

        <div class="card-body-modern p-0">
            <div class="table-responsive">
                <table class="table-modern table-striped text-nowrap w-100 mb-0">
                    <thead>
                        <tr>
                            <th style="width: 60px; text-align: center;">#</th>
                            <th style="text-align: left;">Estudio Realizado</th>
                            <th style="width: 180px; text-align: center;">Fecha de Registro</th>
                            <th style="width: 100px; text-align: center;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($estudiosAsignados as $index => $detalle)
                            <tr>
                                <td style="text-align: center;">
                                    <span class="number-badge" style="background-color: #EFF6FF; color: #1E3A8A; font-weight: 600; padding: 2px 8px; border-radius: 6px; font-size: 0.82rem;">
                                        {{ $index + 1 }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div style="width: 32px; height: 32px; background: #EFF6FF; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #4A6FC1; font-size: 0.95rem; flex-shrink: 0;">
                                            <i class="fas fa-graduation-cap"></i>
                                        </div>
                                        <span class="fw-semibold text-dark" style="font-size: 0.92rem;">
                                            {{ $detalle->estudiosRealizado->estudios ?? 'N/A' }}
                                        </span>
                                    </div>
                                </td>
                                <td style="text-align: center;">
                                    <span class="text-muted" style="font-size: 0.88rem;">
                                        <i class="far fa-calendar-alt text-primary me-1"></i>
                                        {{ $detalle->created_at->format('d/m/Y') }}
                                    </span>
                                </td>
                                <td style="text-align: center;">
                                    <button class="btn btn-sm btn-outline-danger rounded-circle p-0"
                                        style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;"
                                        wire:click="$set('estudioSeleccionado', {{ $detalle->id }})"
                                        data-bs-toggle="modal" data-bs-target="#modalEliminarEstudio"
                                        title="Eliminar">
                                        <i class="fas fa-trash-alt" style="font-size: 0.82rem;"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">
                                    <div class="empty-state text-center py-4">
                                        <div class="empty-icon text-muted mb-2 opacity-50">
                                            <i class="fas fa-graduation-cap fa-2x" style="color: #94A3B8;"></i>
                                        </div>
                                        <h5 class="fw-bold mb-1" style="color: #334155; font-size: 1rem;">No hay estudios asignados</h5>
                                        <p class="text-muted mb-0" style="font-size: 0.82rem;">Seleccione un estudio arriba y haga clic en "+ Agregar Estudio"</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Eliminar Estudio Asignado -->
    <div wire:ignore.self class="modal fade" id="modalEliminarEstudio" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 450px;">
            <div class="modal-content modal-modern shadow-lg" style="border: 1px solid #E2E8F0; border-radius: 16px; background: #FFFFFF;">
                <div class="modal-header d-flex align-items-center justify-content-between px-4 py-3 bg-white border-bottom" style="border-bottom-color: #F1F5F9 !important;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="d-inline-flex align-items-center justify-content-center flex-shrink-0"
                             style="width: 40px; height: 40px; border-radius: 10px; background-color: #FEE2E2; color: #EF4444;">
                            <i class="fas fa-trash-alt"></i>
                        </div>
                        <div>
                            <h5 class="modal-title m-0 fw-bold" style="color: #1E293B; font-size: 1.05rem;">Confirmar Eliminación</h5>
                            <p class="text-muted m-0" style="font-size: 0.8rem;">Eliminar estudio asignado</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body px-4 py-3" style="background-color: #FFFFFF;">
                    <p class="fw-semibold mb-1" style="color: #1E293B; font-size: 0.95rem;">
                        ¿Deseas eliminar este estudio asignado?
                    </p>
                    <p class="text-danger small mb-0">Esta acción es irreversible.</p>
                </div>

                <div class="modal-footer d-flex justify-content-end align-items-center gap-2 px-4 py-3 bg-white border-top" style="border-top-color: #F1F5F9 !important;">
                    <button type="button" class="btn-secondary-modern" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button class="btn btn-danger" style="border-radius: 8px; font-weight: 600; padding: 8px 18px;"
                        wire:click="eliminarEstudio({{ $estudioSeleccionado }})"
                        wire:loading.attr="disabled" data-bs-dismiss="modal">
                        <span wire:loading.remove wire:target="eliminarEstudio({{ $estudioSeleccionado }})">
                            <i class="fas fa-trash-alt me-1"></i> Eliminar
                        </span>
                        <span wire:loading wire:target="eliminarEstudio({{ $estudioSeleccionado }})">
                            <i class="fas fa-spinner fa-spin me-1"></i> Eliminando...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Navegación entre Pasos (Wizard) -->
    <div class="d-flex justify-content-end align-items-center gap-3 mt-4 pt-3 border-top" style="border-top-color: #F1F5F9 !important;">
        <a href="{{ route('admin.docente.index') }}" class="btn-secondary-modern">
            <i class="fas fa-arrow-left me-1"></i> Volver al listado
        </a>
        <a href="{{ route('admin.docente.disponibilidad', $docente->id) }}" class="btn-primary-modern">
            <span>Continuar a Disponibilidad</span> <i class="fas fa-arrow-right ms-1"></i>
        </a>
    </div>

    @include('admin.estudios_realizados.modales.createModal')
</div>

@push('css')
    <link rel="stylesheet" href="{{ asset('css/modal-styles.css') }}">
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            $('.selectpicker').selectpicker();

            $('#estudios_realizados_id').on('changed.bs.select', function(e, clickedIndex, isSelected, previousValue) {
                @this.set('estudiosId', $(this).val());
            });
        });

        document.addEventListener('livewire:load', function() {
            Livewire.on('resetSelect', () => {
                $('#estudios_realizados_id').val('').selectpicker('refresh');
            });
        });

        setTimeout(function() {
            $('.alert-modern').fadeOut('slow', function() {
                $(this).remove();
            });
        }, 5000);
    </script>
@endpush
