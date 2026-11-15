{{--
    Modal para registrar seriales. Primero pregunta cómo:
     - Manual: se pega la lista que entrega la Zona Educativa, uno por línea.
     - Por rango: prefijo + número inicial y final (ej. A- del 0012345 al 0012400).
    Las dos formas guardan igual: cada serial queda disponible en el año escolar seleccionado.
--}}
@php
    $usarOld = old('_modal') === 'crearSeriales';
    $error = fn ($campo) => $usarOld ? $errors->first($campo) : null;
    $modo = $usarOld ? old('modo') : null;
    // Los errores de cada serial vienen como lista.0, lista.1...: se muestran todos juntos y sin repetir.
    $erroresLista = $usarOld
        ? collect($errors->get('lista'))->merge(collect($errors->get('lista.*'))->flatten())->unique()
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
                    <input type="hidden" name="modo" value="{{ $modo }}">

                    {{-- 1. ¿Cómo se van a registrar? --}}
                    <div class="form-group-modern">
                        <label class="form-label-modern">¿Cómo quieres registrar los seriales?</label>
                        <div class="d-flex gap-2 flex-wrap">
                            <button type="button" class="btn btn-outline-primary opcion-modo {{ $modo === 'manual' ? 'active' : '' }}" data-modo="manual">
                                <i class="fas fa-keyboard me-1"></i> Manual (uno por línea)
                            </button>
                            <button type="button" class="btn btn-outline-primary opcion-modo {{ $modo === 'rango' ? 'active' : '' }}" data-modo="rango">
                                <i class="fas fa-arrows-alt-h me-1"></i> Por rango (desde / hasta)
                            </button>
                        </div>
                        @if ($error('modo')) <small class="mensaje-error">{{ $error('modo') }}</small> @endif
                    </div>

                    {{-- 2a. Manual --}}
                    <div class="seccion-modo" data-seccion="manual" @if ($modo !== 'manual') style="display: none" @endif>
                        <div class="form-group-modern">
                            <label for="crear_seriales" class="form-label-modern">Seriales (uno por línea)</label>
                            <textarea class="form-control-modern" id="crear_seriales" name="seriales" rows="8"
                                placeholder="Ejemplo:&#10;A-0012345&#10;A-0012346&#10;A-0012347">{{ $usarOld ? old('seriales') : '' }}</textarea>
                            @if ($modo === 'manual')
                                @foreach (collect($errors->get('seriales'))->merge($erroresLista)->unique() as $mensaje)
                                    <small class="mensaje-error">{{ $mensaje }}</small>
                                @endforeach
                            @endif
                        </div>
                    </div>

                    {{-- 2b. Por rango --}}
                    <div class="seccion-modo" data-seccion="rango" @if ($modo !== 'rango') style="display: none" @endif>
                        <div class="row">
                            <div class="col-md-4 form-group-modern">
                                <label for="crear_prefijo" class="form-label-modern">Prefijo <span class="text-muted">(opcional)</span></label>
                                <input type="text" class="form-control-modern" id="crear_prefijo" name="prefijo" maxlength="20"
                                    placeholder="Ej.: A-" value="{{ $usarOld ? old('prefijo') : '' }}">
                                @if ($error('prefijo')) <small class="mensaje-error">{{ $error('prefijo') }}</small> @endif
                            </div>
                            <div class="col-md-4 form-group-modern">
                                <label for="crear_desde" class="form-label-modern">Desde</label>
                                <input type="text" inputmode="numeric" class="form-control-modern" id="crear_desde" name="desde" maxlength="15"
                                    placeholder="Ej.: 0012345" value="{{ $usarOld ? old('desde') : '' }}">
                                @if ($error('desde')) <small class="mensaje-error">{{ $error('desde') }}</small> @endif
                            </div>
                            <div class="col-md-4 form-group-modern">
                                <label for="crear_hasta" class="form-label-modern">Hasta</label>
                                <input type="text" inputmode="numeric" class="form-control-modern" id="crear_hasta" name="hasta" maxlength="15"
                                    placeholder="Ej.: 0012400" value="{{ $usarOld ? old('hasta') : '' }}">
                                @if ($error('hasta') ?: $error('rango_total'))
                                    <small class="mensaje-error">{{ $error('hasta') ?: $error('rango_total') }}</small>
                                @endif
                            </div>
                        </div>
                        @if ($modo === 'rango')
                            @foreach ($erroresLista as $mensaje)
                                <small class="mensaje-error d-block">{{ $mensaje }}</small>
                            @endforeach
                        @endif
                    </div>

                    <div class="seccion-modo-comun" @if (! $modo) style="display: none" @endif>
                        <small class="text-muted d-block mb-3" id="crear_seriales_contador">Se registrarán como disponibles en el año escolar seleccionado.</small>

                        <div class="form-group-modern">
                            <label for="crear_fecha_recepcion" class="form-label-modern">Fecha de recepción <span class="text-muted">(opcional)</span></label>
                            <input type="date" class="form-control-modern" id="crear_fecha_recepcion" name="fecha_recepcion"
                                value="{{ $usarOld ? old('fecha_recepcion') : now()->toDateString() }}">
                            @if ($error('fecha_recepcion')) <small class="mensaje-error">{{ $error('fecha_recepcion') }}</small> @endif
                        </div>
                    </div>

                    <div class="modal-footer-create">
                        <div class="footer-buttons">
                            <button type="button" class="btn-modal-cancel" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn-modal-create" @if (! $modo) style="display: none" @endif>Registrar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
