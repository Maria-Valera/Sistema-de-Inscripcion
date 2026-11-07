{{-- Modal para editar un calendario: estado y, si no tiene eventos, su año escolar --}}
<div class="modal fade" id="editarCalendario{{ $calendario->id }}" tabindex="-1"
    aria-labelledby="editarCalendarioLabel{{ $calendario->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-modern">
            <div class="modal-header-edit">
                <div class="modal-icon-edit">
                    <i class="fas fa-pen"></i>
                </div>
                <h5 class="modal-title-edit" id="editarCalendarioLabel{{ $calendario->id }}">
                    Editar estado del Calendario
                </h5>
                <button type="button" class="btn-close-modal" data-bs-dismiss="modal" aria-label="Cerrar">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body-edit">
                <form action="{{ route('admin.calendario_academico.update', $calendario) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="form-group-modern">
                        <label for="anio_escolar_id_{{ $calendario->id }}" class="form-label-modern">Año escolar</label>
                        @if ($calendario->dias_count > 0)
                            {{-- Con eventos no se cambia el año: se manda el mismo --}}
                            <input type="hidden" name="anio_escolar_id" value="{{ $calendario->anio_escolar_id }}">
                            <input type="text" class="form-control-modern" disabled
                                value="{{ $calendario->anioEscolar->inicio_anio_escolar->format('Y') }} - {{ $calendario->anioEscolar->cierre_anio_escolar->format('Y') }}">
                            <small class="text-muted">No se puede cambiar porque ya tiene {{ $calendario->dias_count }} eventos.</small>
                        @else
                            <select class="form-control-modern" id="anio_escolar_id_{{ $calendario->id }}" name="anio_escolar_id" required>
                                <option value="{{ $calendario->anio_escolar_id }}" selected>
                                    {{ $calendario->anioEscolar->inicio_anio_escolar->format('Y') }} - {{ $calendario->anioEscolar->cierre_anio_escolar->format('Y') }} (actual)
                                </option>
                                @foreach ($aniosSinCalendario as $anio)
                                    <option value="{{ $anio->id }}">
                                        {{ $anio->inicio_anio_escolar->format('Y') }} - {{ $anio->cierre_anio_escolar->format('Y') }}
                                    </option>
                                @endforeach
                            </select>
                        @endif
                    </div>

                    <div class="form-group-modern">
                        <label for="estado_{{ $calendario->id }}" class="form-label-modern">Estado</label>
                        <select class="form-control-modern" id="estado_{{ $calendario->id }}" name="estado" required>
                            @foreach (\App\Enums\EstadoCalendario::cases() as $estado)
                                <option value="{{ $estado->value }}" @selected($calendario->estado === $estado)>
                                    {{ $estado->label() }}
                                </option>
                            @endforeach
                        </select>
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
