{{-- Modal para editar un serial: su número, su estado y, si se anula o devuelve, el motivo --}}
@php
    $idModal = 'editarSerial' . $serial->id;
    $usarOld = old('_modal') === $idModal;
    $valor = fn ($campo, $actual) => $usarOld ? old($campo, $actual) : $actual;
    $error = fn ($campo) => $usarOld ? $errors->first($campo) : null;
    $estadoActual = $valor('estado', $serial->estado->value);
@endphp
<div class="modal fade" id="{{ $idModal }}" tabindex="-1" aria-labelledby="{{ $idModal }}Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-modern">
            <div class="modal-header-edit">
                <div class="modal-icon-edit">
                    <i class="fas fa-pen"></i>
                </div>
                <h5 class="modal-title-edit" id="{{ $idModal }}Label">Editar serial {{ $serial->serial }}</h5>
                <button type="button" class="btn-close-modal" data-bs-dismiss="modal" aria-label="Cerrar">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body-edit" style="overflow-y: auto;">
                <form action="{{ route('admin.titulo_seriales.update', $serial) }}" method="POST" class="form-serial-editar"
                    data-serial-original="{{ $serial->serial }}" data-estado-original="{{ $serial->estado->value }}">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="_modal" value="{{ $idModal }}">

                    <div class="row">
                        <div class="col-md-7 form-group-modern">
                            <label for="{{ $idModal }}_serial" class="form-label-modern">Serial</label>
                            <input type="text" class="form-control-modern" id="{{ $idModal }}_serial" name="serial" maxlength="30"
                                value="{{ $valor('serial', $serial->serial) }}" required>
                            @if ($error('serial')) <small class="mensaje-error">{{ $error('serial') }}</small> @endif
                        </div>
                        <div class="col-md-5 form-group-modern">
                            <label for="{{ $idModal }}_fecha_recepcion" class="form-label-modern">Recibido</label>
                            <input type="date" class="form-control-modern" id="{{ $idModal }}_fecha_recepcion" name="fecha_recepcion"
                                value="{{ $valor('fecha_recepcion', $serial->fecha_recepcion?->format('Y-m-d')) }}">
                            @if ($error('fecha_recepcion')) <small class="mensaje-error">{{ $error('fecha_recepcion') }}</small> @endif
                        </div>
                    </div>

                    <div class="form-group-modern">
                        <label for="{{ $idModal }}_estado" class="form-label-modern">Estado</label>
                        <select class="form-control-modern selector-estado" id="{{ $idModal }}_estado" name="estado" required>
                            @foreach (\App\Enums\EstadoSerialTitulo::cases() as $estado)
                                <option value="{{ $estado->value }}" @selected($estadoActual === $estado->value)>{{ $estado->label() }}</option>
                            @endforeach
                        </select>
                        @if ($error('estado')) <small class="mensaje-error">{{ $error('estado') }}</small> @endif
                    </div>

                    {{-- Solo se ven si el estado es Anulado o Devuelto --}}
                    <div class="form-group-modern" data-solo-estados="anulado,devuelto">
                        <label for="{{ $idModal }}_motivo_anulacion" class="form-label-modern">Motivo</label>
                        <input type="text" class="form-control-modern" id="{{ $idModal }}_motivo_anulacion" name="motivo_anulacion" maxlength="255"
                            placeholder="Ej.: formato dañado al imprimir" value="{{ $valor('motivo_anulacion', $serial->motivo_anulacion) }}">
                        @if ($error('motivo_anulacion')) <small class="mensaje-error">{{ $error('motivo_anulacion') }}</small> @endif
                    </div>

                    <div class="form-group-modern" data-solo-estados="devuelto">
                        <label for="{{ $idModal }}_fecha_liquidacion" class="form-label-modern">Fecha de devolución a la Zona Educativa</label>
                        <input type="date" class="form-control-modern" id="{{ $idModal }}_fecha_liquidacion" name="fecha_liquidacion"
                            value="{{ $valor('fecha_liquidacion', $serial->fecha_liquidacion?->format('Y-m-d')) }}">
                        @if ($error('fecha_liquidacion')) <small class="mensaje-error">{{ $error('fecha_liquidacion') }}</small> @endif
                    </div>

                    <div class="modal-footer-edit">
                        <div class="footer-buttons">
                            <button type="button" class="btn-modal-cancel" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn-modal-edit">Guardar cambios</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
