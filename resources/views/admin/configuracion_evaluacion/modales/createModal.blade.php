<div class="modal fade" id="modalCrear" tabindex="-1" aria-labelledby="modalCrearLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content modal-modern">
            <div class="modal-header-create">
                <div class="modal-icon-create">
                    <i class="fas fa-plus"></i>
                </div>
                <h5 class="modal-title-create" id="modalCrearLabel">Nueva configuración de evaluación</h5>
                <button type="button" class="btn-close-modal" data-bs-dismiss="modal" aria-label="Cerrar">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body-create" style="overflow-y: auto;">
                <form action="{{ route('admin.configuracion_evaluacion.store') }}" method="POST">
                    @csrf

                    <div class="form-group-modern">
                        <label for="crear_anio_escolar_id" class="form-label-modern">Año escolar</label>
                        <select class="form-control-modern" id="crear_anio_escolar_id" name="anio_escolar_id" required>
                            @foreach ($aniosSinConfiguracion as $anio)
                                <option value="{{ $anio->id }}" @selected(old('anio_escolar_id') == $anio->id)>
                                    {{ $anio->inicio_anio_escolar->format('Y') }} - {{ $anio->cierre_anio_escolar->format('Y') }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @include('admin.configuracion_evaluacion.modales._campos', ['config' => null, 'prefijo' => 'crear'])

                    <div class="modal-footer-create">
                        <div class="footer-buttons">
                            <button type="button" class="btn-modal-cancel" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn-modal-create">Guardar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
