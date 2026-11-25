@extends('adminlte::page')

{{ Breadcrumbs::render('aulas.index') }}

@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modal-styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pagination.css') }}">
    <link rel="stylesheet" href="{{ asset('css/design-system.css') }}?v={{ @filemtime(public_path('css/design-system.css')) }}">
@stop

@section('title', 'Gestión de Aulas')

@section('content_header')
    <div class="content-header-modern hero-header-horario">
        <div class="header-content">
            <div class="header-title">
                <div class="icon-wrapper">
                    <i class="fas fa-door-open"></i>
                </div>
                <div>
                    <h1 class="title-main">Gestión de Aulas</h1>
                    <p class="title-subtitle">Administración de espacios físicos, laboratorios y salas académicas</p>
                </div>
            </div>

            <button type="button" class="btn-hero-white" data-bs-toggle="modal" data-bs-target="#modalCrear">
                <i class="fas fa-plus"></i>
                <span>Nueva Aula</span>
            </button>
        </div>
    </div>
@stop

@section('content')
    <div class="main-container">

        @include('aulas.modales.createModal')

        @if (session('success') || session('error'))
            <div class="alerts-container">
                @if (session('success'))
                    <div class="alert-modern alert-success alert alert-dismissible fade show" role="alert">
                        <div class="alert-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="alert-content">
                            <h4>Éxito</h4>
                            <p>{{ session('success') }}</p>
                        </div>
                        <button type="button" class="alert-close btn-close" data-bs-dismiss="alert" aria-label="Cerrar">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert-modern alert-error alert alert-dismissible fade show" role="alert">
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

        <div class="card-modern shadow-sm">
            <div class="card-header-modern d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div class="header-left d-flex align-items-center gap-3">
                    <div class="header-icon d-flex align-items-center justify-content-center"
                         style="width: 40px; height: 40px; border-radius: 10px; background-color: #EFF6FF; color: #4A6FC1;">
                        <i class="fas fa-list-ul"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-0" style="font-size: 1.05rem; color: #1E293B;">Listado de Aulas</h3>
                        <p class="text-muted mb-0" style="font-size: 0.82rem;">{{ $aulas->total() }} registros encontrados</p>
                    </div>
                </div>

                {{-- Buscador Redondeado (Design System) --}}
                <div class="header-right">
                    <div class="search-wrapper position-relative" style="width: 260px;">
                        <i class="fas fa-search position-absolute text-muted" style="left: 15px; top: 50%; transform: translateY(-50%); z-index: 10; font-size: 0.85rem;"></i>
                        <input type="text"
                               id="buscarAula"
                               class="form-control search-input-modern pl-5"
                               placeholder="Buscar aula..."
                               style="padding-left: 40px !important; border-radius: 10px; height: 38px; font-size: 0.85rem;">
                    </div>
                </div>
            </div>

            <div class="card-body-modern p-0">
                <div class="table-wrapper">
                    <table class="table-modern" id="tablaAulas">
                        <thead>
                            <tr class="text-center">
                                <th style="width: 70px;">#</th>
                                <th class="text-start">Nombre del Aula</th>
                                <th>Tipo de Aula</th>
                                <th style="width: 100px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="text-center">
                            @if ($aulas->isEmpty())
                                <tr>
                                    <td colspan="4">
                                        <div class="empty-state text-center py-5">
                                            <div class="empty-icon text-muted mb-2 opacity-50">
                                                <i class="fas fa-door-open fa-3x"></i>
                                            </div>
                                            <h4 class="fw-bold" style="color: #1E293B; font-size: 1.1rem;">No hay aulas registradas</h4>
                                            <p class="text-muted" style="font-size: 0.88rem;">Agrega una nueva aula con el botón superior.</p>
                                        </div>
                                    </td>
                                </tr>
                            @else
                                @foreach ($aulas as $index => $aula)
                                    <tr>
                                        <td class="text-muted" style="font-size: 0.85rem;">{{ $aulas->firstItem() ? ($aulas->firstItem() + $index) : ($index + 1) }}</td>
                                        <td class="text-start">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="d-inline-flex align-items-center justify-content-center rounded-2"
                                                     style="width: 32px; height: 32px; background-color: #EFF6FF; color: #4A6FC1; font-size: 0.9rem;">
                                                    <i class="fas fa-chalkboard"></i>
                                                </div>
                                                <span class="fw-bold text-dark" style="font-size: 0.93rem;">
                                                    {{ $aula->nombre_aula }}
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge" style="background-color: #F8FAFC; color: #334155; font-weight: 600; padding: 0.45rem 0.85rem; border-radius: 8px; font-size: 0.85rem; border: 1px solid #E2E8F0;">
                                                <i class="fas fa-layer-group text-primary me-1"></i>
                                                {{ $aula->tipo_aula }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-buttons justify-content-center">
                                                <div class="dropdown text-center">
                                                    <button class="action-btn"
                                                            type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Acciones">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-modern">
                                                        <li>
                                                            <button class="dropdown-item dropdown-item-modern item-edit"
                                                                type="button"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#modalEditar{{ $aula->id_aula }}"
                                                                title="Editar">
                                                                <i class="fas fa-pen me-2"></i>
                                                                <span>Editar</span>
                                                            </button>
                                                        </li>
                                                        <li>
                                                            <button class="dropdown-item dropdown-item-modern item-delete"
                                                                type="button"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#confirmarEliminar{{ $aula->id_aula }}"
                                                                title="Eliminar">
                                                                <i class="fas fa-trash-alt me-2"></i>
                                                                <span>Eliminar</span>
                                                            </button>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>

                                    @include('aulas.modales.editModal')

                                    {{-- Modal Confirmar Eliminar con Design System --}}
                                    <div class="modal fade modal-danger-delete" id="confirmarEliminar{{ $aula->id_aula }}" tabindex="-1"
                                        aria-labelledby="modalLabel{{ $aula->id_aula }}" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered" style="max-width: 480px;">
                                            <div class="modal-content modal-modern shadow-lg" style="border: 1px solid #E2E8F0; border-radius: 16px; overflow: hidden; background: #FFFFFF;">
                                                <div class="modal-header d-flex align-items-center justify-content-between px-4 py-3 bg-white border-bottom" style="border-bottom-color: #F1F5F9 !important;">
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div class="d-inline-flex align-items-center justify-content-center flex-shrink-0"
                                                             style="width: 44px; height: 44px; border-radius: 12px; background-color: #FEE2E2; color: #EF4444;">
                                                            <i class="fas fa-trash-alt" style="font-size: 1.15rem;"></i>
                                                        </div>
                                                        <div>
                                                            <h5 class="modal-title m-0 fw-bold" id="modalLabel{{ $aula->id_aula }}" style="color: #1E293B; font-size: 1.1rem;">
                                                                Confirmar Eliminación
                                                            </h5>
                                                            <p class="text-muted m-0" style="font-size: 0.82rem;">Acción destructiva sobre aula</p>
                                                        </div>
                                                    </div>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                                </div>

                                                <form action="{{ route('aulas.destroy', $aula) }}" method="POST" class="w-100 m-0">
                                                    @csrf
                                                    @method('DELETE')
                                                    <div class="modal-body px-4 py-3" style="background-color: #FFFFFF;">
                                                        <p class="fw-semibold mb-2" style="color: #1E293B; font-size: 0.95rem;">
                                                            ¿Deseas eliminar el aula <strong>{{ $aula->nombre_aula }}</strong>?
                                                        </p>
                                                        <p class="text-muted mb-3" style="font-size: 0.85rem;">
                                                            El aula quedará inactiva y no aparecerá en el sistema.
                                                        </p>
                                                        <div class="p-3 d-flex align-items-center gap-3"
                                                             style="background-color: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px;">
                                                            <div class="d-flex align-items-center justify-content-center rounded-3 flex-shrink-0"
                                                                 style="width: 40px; height: 40px; background-color: #EFF6FF; color: #4A6FC1; font-size: 1.1rem;">
                                                                <i class="fas fa-door-open"></i>
                                                            </div>
                                                            <div>
                                                                <div class="fw-bold text-dark" style="font-size: 0.9rem;">{{ $aula->nombre_aula }}</div>
                                                                <div class="text-muted" style="font-size: 0.8rem;">Tipo: {{ $aula->tipo_aula }}</div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="modal-footer d-flex justify-content-end align-items-center gap-2 px-4 py-3 bg-white border-top" style="border-top-color: #F1F5F9 !important;">
                                                        <button type="button" class="btn-secondary-modern" data-bs-dismiss="modal">
                                                            <i class="fas fa-times me-1"></i> Cancelar
                                                        </button>
                                                        <button type="submit" class="btn" style="background-color: #EF4444; color: #FFF; border-radius: 8px; font-weight: 600; padding: 9px 18px; border: none;">
                                                            <i class="fas fa-trash-alt me-1"></i> Eliminar
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <x-pagination :paginator="$aulas" />

    </div>

@section('js')
<script>
    const urlVerificar = "{{ route('aulas.verificarExistencia') }}";
    const csrfToken    = "{{ csrf_token() }}";

    // ─── Utilidades ───────────────────────────────────────────
    function ocultarTodos(prefijo) {
        ['vacio', 'duplicado'].forEach(tipo => {
            const el = document.getElementById(`error-${prefijo}-${tipo}`);
            if (el) el.classList.remove('show'), el.style.display = '';
        });
        const ok = document.getElementById(`ok-${prefijo}`);
        if (ok) ok.style.display = 'none';
    }

    function ocultarTodosEditar(id) {
        ['vacio', 'duplicado'].forEach(tipo => {
            const el = document.getElementById(`error-editar-${tipo}-${id}`);
            if (el) el.classList.remove('show'), el.style.display = '';
        });
        const ok = document.getElementById(`ok-editar-${id}`);
        if (ok) ok.style.display = 'none';
    }

    function mostrar(id) {
        const el = document.getElementById(id);
        if (el) { el.classList.add('show'); el.style.display = 'flex'; }
    }

    function setBoton(btnId, habilitado) {
        const btn = document.getElementById(btnId);
        if (btn) btn.disabled = !habilitado;
    }

    // ─── Debounce ─────────────────────────────────────────────
    function debounce(fn, ms = 500) {
        let timer;
        return (...args) => { clearTimeout(timer); timer = setTimeout(() => fn(...args), ms); };
    }

    // ─── Verificar via AJAX ───────────────────────────────────
    async function verificar(nombre, idAula = null) {
        if (!nombre) return null;
        const body = new FormData();
        body.append('nombre_aula', nombre);
        body.append('_token', csrfToken);
        if (idAula) body.append('id_aula', idAula);

        const res  = await fetch(urlVerificar, { method: 'POST', body });
        const data = await res.json();
        return data.existe;
    }

    // ─── MODAL CREAR ──────────────────────────────────────────
    const inputCrear = document.getElementById('nombre_aula_crear');

    if (inputCrear) {
        const validarCrear = debounce(async () => {
            const valor = inputCrear.value.trim();
            ocultarTodos('crear');
            setBoton('btnGuardarCrear', false);
            inputCrear.classList.remove('is-invalid');

            if (!valor) {
                mostrar('error-crear-vacio');
                return;
            }

            const existe = await verificar(valor);

            if (existe) {
                mostrar('error-crear-duplicado');
                inputCrear.classList.add('is-invalid');
            } else {
                mostrar('ok-crear');
                setBoton('btnGuardarCrear', true);
            }
        });

        inputCrear.addEventListener('input', validarCrear);

        // Función para limpiar modal crear
        function limpiarModalCrear() {
            const form = document.getElementById('formCrearAula');
            if (form) form.reset();
            if (inputCrear) {
                inputCrear.value = '';
                inputCrear.classList.remove('is-invalid');
            }
            const tipoAulaCrear = document.getElementById('tipo_aula_crear');
            if (tipoAulaCrear) {
                tipoAulaCrear.value = '';
                tipoAulaCrear.classList.remove('is-invalid');
            }
            ocultarTodos('crear');
            const errTipo = document.getElementById('error-tipo-crear-vacio');
            if (errTipo) errTipo.style.display = 'none';
            setBoton('btnGuardarCrear', true);
        }

        // Limpiar modal al abrir y al cerrar
        const modalCrearEl = document.getElementById('modalCrear');
        if (modalCrearEl) {
            modalCrearEl.addEventListener('show.bs.modal', limpiarModalCrear);
            modalCrearEl.addEventListener('hidden.bs.modal', limpiarModalCrear);
        }

        // Bloquear envío si hay error
        document.getElementById('formCrearAula').addEventListener('submit', (e) => {
            const valor = inputCrear.value.trim();
            if (!valor) {
                e.preventDefault();
                ocultarTodos('crear');
                mostrar('error-crear-vacio');
                inputCrear.classList.add('is-invalid');
            }
        });
    }

    // ─── MODALES EDITAR ───────────────────────────────────────
    document.querySelectorAll('[id^="nombre_aula_editar_"]').forEach(inputEditar => {
        const id = inputEditar.dataset.id;

        const validarEditar = debounce(async () => {
            const valor = inputEditar.value.trim();
            ocultarTodosEditar(id);
            setBoton(`btnGuardarEditar${id}`, false);
            inputEditar.classList.remove('is-invalid');

            if (!valor) {
                mostrar(`error-editar-vacio-${id}`);
                return;
            }

            const existe = await verificar(valor, id);

            if (existe) {
                mostrar(`error-editar-duplicado-${id}`);
                inputEditar.classList.add('is-invalid');
            } else {
                mostrar(`ok-editar-${id}`);
                setBoton(`btnGuardarEditar${id}`, true);
            }
        });

        inputEditar.addEventListener('input', validarEditar);

        // Bloquear envío si hay error
        document.getElementById(`formEditarAula${id}`).addEventListener('submit', (e) => {
            const valor = inputEditar.value.trim();
            if (!valor) {
                e.preventDefault();
                ocultarTodosEditar(id);
                mostrar(`error-editar-vacio-${id}`);
                inputEditar.classList.add('is-invalid');
            }
        });
    });

    // Limpiar errores en modales de edición al cerrarse
    document.querySelectorAll('[id^="modalEditar"]').forEach(modal => {
        modal.addEventListener('hidden.bs.modal', () => {
            modal.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            modal.querySelectorAll('.error-message').forEach(el => el.style.display = 'none');
        });
    });

    // ─── Buscador en tiempo real ─────────────────────────────
    const inputBuscarAula = document.getElementById('buscarAula');
    if (inputBuscarAula) {
        inputBuscarAula.addEventListener('input', function () {
            const query = this.value.toLowerCase().trim();
            document.querySelectorAll('#tablaAulas tbody tr').forEach(row => {
                if (row.cells.length === 1) return;
                const text = row.innerText.toLowerCase();
                row.style.display = text.includes(query) ? '' : 'none';
            });
        });
    }
</script>
@endsection
@endsection
