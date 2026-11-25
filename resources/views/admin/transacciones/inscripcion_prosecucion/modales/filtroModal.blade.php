<div class="offcanvas offcanvas-end filter-panel-saas" tabindex="-1" id="panelFiltros" aria-labelledby="panelFiltrosLabel" style="width: 420px; max-width: 92vw;">
    <!-- Header -->
    <div class="offcanvas-header filter-panel-header d-flex align-items-center justify-content-between p-3 px-4 border-bottom bg-white" style="border-bottom: 1px solid #E2E8F0 !important;">
        <div class="d-flex align-items-center gap-2">
            <span class="filter-panel-title-icon" aria-hidden="true" style="width: 36px; height: 36px; border-radius: 10px; background-color: #EFF6FF; color: #4A6FC1; display: inline-flex; align-items: center; justify-content: center;">
                <i class="fas fa-sliders-h" style="font-size: 0.95rem;"></i>
            </span>
            <h5 class="offcanvas-title mb-0 fw-bold" id="panelFiltrosLabel" style="font-size: 1.1rem; color: #1E293B;">
                Filtros de Búsqueda
            </h5>
        </div>
        <button type="button" class="btn-close shadow-none" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
    </div>

    <!-- Body & Form -->
    <form method="GET" action="{{ route('admin.transacciones.inscripcion_prosecucion.index') }}" id="formFiltros" class="d-flex flex-column flex-grow-1 overflow-hidden m-0">
        {{-- Mantener término de búsqueda si existe --}}
        @if(request('buscar'))
            <input type="hidden" name="buscar" value="{{ request('buscar') }}">
        @endif

        <div class="offcanvas-body filter-panel-body p-4" style="overflow-y: auto; background-color: #FFFFFF;">
            <!-- Estado de la Inscripción -->
            <div class="mb-4">
                <label for="status" class="form-label-modern fw-semibold mb-2" style="color: #334155; font-size: 0.88rem;">
                    <i class="fas fa-toggle-on me-1" style="color: #4A6FC1;"></i> Estado de la inscripción
                </label>
                <select name="status" id="status" class="form-select select-modern">
                    <option value="">Todos los estados</option>
                    <option value="Activo" {{ request('status', 'Activo') == 'Activo' ? 'selected' : '' }}>
                        Activo
                    </option>
                    <option value="Pendiente" {{ request('status') == 'Pendiente' ? 'selected' : '' }}>
                        Pendiente
                    </option>
                    <option value="Inactivo" {{ request('status') == 'Inactivo' ? 'selected' : '' }}>
                        Inactivo
                    </option>
                </select>
            </div>

            <!-- Niveles Académicos -->
            <div class="mb-4">
                <label for="grado_select_prosecucion" class="form-label-modern fw-semibold mb-2" style="color: #334155; font-size: 0.88rem;">
                    <i class="fas fa-layer-group me-1" style="color: #4A6FC1;"></i> Niveles académicos
                </label>
                <select name="grado_id" id="grado_select_prosecucion" class="form-select select-modern">
                    <option value="">Todos los niveles</option>
                    @foreach ($grados as $grado)
                        <option value="{{ $grado->id }}" {{ request('grado_id') == $grado->id ? 'selected' : '' }}>
                            Nivel {{ $grado->numero_grado }}º
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Sección -->
            <div class="mb-4">
                <label for="seccion_select_prosecucion" class="form-label-modern fw-semibold mb-2" style="color: #334155; font-size: 0.88rem;">
                    <i class="fas fa-th-large me-1" style="color: #4A6FC1;"></i> Sección
                </label>
                <select name="seccion_id" id="seccion_select_prosecucion" class="form-select select-modern"
                    {{ !request('grado_id') ? 'disabled' : '' }}>
                    <option value="">
                        @if (!request('grado_id'))
                            Primero seleccione un nivel académico
                        @elseif($secciones->isEmpty())
                            No hay secciones para este nivel académico
                        @else
                            Todas las secciones
                        @endif
                    </option>
                    @foreach ($secciones as $seccion)
                        <option value="{{ $seccion->id }}" {{ request('seccion_id') == $seccion->id ? 'selected' : '' }}>
                            Sección {{ $seccion->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Materias Pendientes -->
            <div class="mb-4">
                <label for="materias_pendientes" class="form-label-modern fw-semibold mb-2" style="color: #334155; font-size: 0.88rem;">
                    <i class="fas fa-book me-1" style="color: #4A6FC1;"></i> Materias Pendientes
                </label>
                <select name="materias_pendientes" id="materias_pendientes" class="form-select select-modern">
                    <option value="">Todos los estudiantes</option>
                    <option value="con_pendientes" {{ request('materias_pendientes') == 'con_pendientes' ? 'selected' : '' }}>
                        Con materias pendientes
                    </option>
                    <option value="sin_pendientes" {{ request('materias_pendientes') == 'sin_pendientes' ? 'selected' : '' }}>
                        Sin materias pendientes
                    </option>
                </select>
            </div>
        </div>

        <!-- Footer -->
        <div class="filter-panel-footer d-flex align-items-center justify-content-between p-3 px-4 border-top bg-white" style="border-top: 1px solid #E2E8F0 !important;">
            <a href="{{ route('admin.transacciones.inscripcion_prosecucion.index') }}" class="btn-secondary-modern text-decoration-none">
                <i class="fas fa-undo me-1"></i> Limpiar
            </a>
            <button type="submit" class="btn-primary-modern">
                <i class="fas fa-filter me-1"></i> Aplicar Filtros
            </button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const gradoSelect = document.getElementById('grado_select_prosecucion');
        const seccionSelect = document.getElementById('seccion_select_prosecucion');
        const baseUrl = "{{ url('admin/transacciones/secciones-por-grado') }}";

        if (gradoSelect && seccionSelect) {
            gradoSelect.addEventListener('change', async function() {
                const gradoId = this.value;

                // Reset
                seccionSelect.innerHTML = '';
                seccionSelect.disabled = true;

                if (!gradoId) {
                    seccionSelect.innerHTML = '<option value="">Primero seleccione un nivel académico</option>';
                    return;
                }

                // Loading
                seccionSelect.innerHTML = '<option value="">Cargando secciones...</option>';

                try {
                    const response = await fetch(`${baseUrl}/${gradoId}`);
                    if (!response.ok) {
                        throw new Error('Error HTTP ' + response.status);
                    }

                    const secciones = await response.json();
                    seccionSelect.innerHTML = '<option value="">Todas las secciones</option>';

                    if (!secciones.length) {
                        seccionSelect.innerHTML = '<option value="">No hay secciones para este nivel académico</option>';
                        return;
                    }

                    secciones.forEach(seccion => {
                        const option = document.createElement('option');
                        option.value = seccion.id;
                        option.textContent = `Sección ${seccion.nombre}`;
                        seccionSelect.appendChild(option);
                    });

                    seccionSelect.disabled = false;
                } catch (error) {
                    console.error('Error al cargar secciones:', error);
                    seccionSelect.innerHTML = '<option value="">Error al cargar secciones</option>';
                }
            });
        }
    });
</script>
