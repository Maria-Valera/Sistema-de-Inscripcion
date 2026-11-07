{{-- Modal para registrar seriales: se pega la lista que entrega la Zona Educativa, uno por línea --}}
@php
    $usarOld = old('_modal') === 'crearSeriales';
    $error = fn ($campo) => $usarOld ? $errors->first($campo) : null;
    // Los errores de cada serial vienen como lista.0, lista.1...: se muestran todos juntos y sin repetir.
    $erroresSeriales = $usarOld
        ? collect($errors->get('seriales'))->merge($errors->get('lista'))->merge(collect($errors->get('lista.*'))->flatten())->unique()
        : collect();
@endphp
<div class="modal fade" id="crearSeriales" tabindex="-1" aria-labelledby="crearSerialesLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content modal-modern">
            <div class="modal-header-create">
                <div class="modal-icon-create">
                    <i class="fas fa-plus"></i>
                </div>
                <h5 class="modal-title-create" id="crearSerialesLabel">Registrar seriales</h5>
                <button type="button" class="btn-close-modal" data-bs-dismiss="modal" aria-label="Cerrar">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body-create" style="overflow-y: auto;">
                <form action="{{ route('admin.titulo_seriales.store') }}" method="POST" class="form-serial-crear">
                    @csrf
                    <input type="hidden" name="_modal" value="crearSeriales">

                    <div class="form-group-modern">
                        <label for="crear_seriales" class="form-label-modern">Seriales (uno por línea)</label>
                        <textarea class="form-control-modern" id="crear_seriales" name="seriales" rows="8"
                            placeholder="Ejemplo:&#10;A-0012345&#10;A-0012346&#10;A-0012347" required>{{ $usarOld ? old('seriales') : '' }}</textarea>
                        @foreach ($erroresSeriales as $mensaje)
                            <small class="mensaje-error">{{ $mensaje }}</small>
                        @endforeach
                        <small class="text-muted d-block mt-1" id="crear_seriales_contador">Se registrarán como disponibles en el año escolar seleccionado.</small>
                    </div>

                    <div class="form-group-modern">
                        <label for="crear_fecha_recepcion" class="form-label-modern">Fecha de recepción <span class="text-muted">(opcional)</span></label>
                        <input type="date" class="form-control-modern" id="crear_fecha_recepcion" name="fecha_recepcion"
                            value="{{ $usarOld ? old('fecha_recepcion') : now()->toDateString() }}">
                        @if ($error('fecha_recepcion')) <small class="mensaje-error">{{ $error('fecha_recepcion') }}</small> @endif
                    </div>

                    <div class="modal-footer-create">
                        <div class="footer-buttons">
                            <button type="button" class="btn-modal-cancel" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn-modal-create">Registrar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
