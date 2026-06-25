<div class="modal fade" id="modalFiltros" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content modal-modern">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title d-flex align-items-center gap-2" style="color: var(--primary);">
                    <i class="fas fa-filter"></i>
                    Filtros
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <hr>

            <div class="modal-body pt-0">
                <form id="filtroForm" action="{{ route('admin.horario.index') }}" method="GET">
                    <div class="mb-3">
                        <label class="form-label-modern">
                            <i class="fas fa-graduation-cap" style="color: var(--primary);"></i>
                            Nivel Académico
                        </label>
                        <select class="form-select" id="nivel_academico" name="nivel_academico">
                            <option value="">Todos los niveles</option>
                            <option value="primaria" {{ request('nivel_academico') == 'primaria' ? 'selected' : '' }}>
                                Primaria
                            </option>
                            <option value="secundaria" {{ request('nivel_academico') == 'secundaria' ? 'selected' : '' }}>
                                Secundaria
                            </option>
                            <option value="bachillerato" {{ request('nivel_academico') == 'bachillerato' ? 'selected' : '' }}>
                                Bachillerato
                            </option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label-modern">
                            <i class="fas fa-book" style="color: var(--primary);"></i>
                            Área de Formación
                        </label>
                        <select class="form-select" id="area_formacion" name="area_formacion">
                            <option value="">Todas las áreas</option>
                            <option value="ciencias" {{ request('area_formacion') == 'ciencias' ? 'selected' : '' }}>
                                Ciencias
                            </option>
                            <option value="humanidades" {{ request('area_formacion') == 'humanidades' ? 'selected' : '' }}>
                                Humanidades
                            </option>
                            <option value="tecnica" {{ request('area_formacion') == 'tecnica' ? 'selected' : '' }}>
                                Técnica
                            </option>
                            <option value="artistica" {{ request('area_formacion') == 'artistica' ? 'selected' : '' }}>
                                Artística
                            </option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label-modern">
                            <i class="fas fa-users" style="color: var(--primary);"></i>
                            Sección
                        </label>
                        <select class="form-select" id="seccion" name="seccion">
                            <option value="">Todas las secciones</option>
                            <option value="A" {{ request('seccion') == 'A' ? 'selected' : '' }}>
                                Sección A
                            </option>
                            <option value="B" {{ request('seccion') == 'B' ? 'selected' : '' }}>
                                Sección B
                            </option>
                            <option value="C" {{ request('seccion') == 'C' ? 'selected' : '' }}>
                                Sección C
                            </option>
                            <option value="D" {{ request('seccion') == 'D' ? 'selected' : '' }}>
                                Sección D
                            </option>
                        </select>
                    </div>

                    <div class="modal-footer p-0 pt-3">
                        <button type="submit" class="btn-modal-create w-100 mt-4">
                            <i class="fas fa-check"></i>
                            Aplicar Filtros
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
