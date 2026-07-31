@extends('adminlte::page')

{{ Breadcrumbs::render('aulas.index') }}

@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modal-styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pagination.css') }}">
@stop

@section('title', 'Gestión de Aulas')

@section('content_header')
    <div class="content-header-modern">
        <div class="header-content">
            <div class="header-title">
                <div class="icon-wrapper">
                    <i class="fas fa-door-open"></i>
                </div>
                <div>
                    <h1 class="title-main">Gestión de Aulas</h1>

                </div>
            </div>

            <button type="button" class="btn-create" data-bs-toggle="modal" data-bs-target="#modalCrear">
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

        <div class="card-modern">
            <div class="card-header-modern">
                <div class="header-left">
                    <div class="header-icon">
                        <i class="fas fa-list-ul"></i>
                    </div>
                    <div>
                        <h3>Listado de Aulas</h3>
                        <p>{{ $aulas->total() }} registros encontrados</p>
                    </div>
                </div>
            </div>

            <div class="card-body-modern">
                <div class="table-wrapper">
                    <table class="table-modern overflow-hidden">
                        <thead>
                            <tr style="text-align: center">
                                <th style="text-align: center">#</th>
                                <th style="text-align: center">Nombre del Aula</th>
                                <th style="text-align: center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody style="text-align: center">
                            @if ($aulas->isEmpty())
                                <tr>
                                    <td colspan="3">
                                        <div class="empty-state">
                                            <div class="empty-icon">
                                                <i class="fas fa-inbox"></i>
                                            </div>
                                            <h4>No hay aulas registradas</h4>
                                            <p>Agrega una nueva aula con el botón superior</p>
                                        </div>
                                    </td>
                                </tr>
                            @else
                                @foreach ($aulas as $index => $aula)
                                    <tr class="row-12" style="text-align: center">
                                        <td>{{ $index + 1 }}</td>
                                        <td class="title-main">{{ $aula->nombre_aula }}</td>
                                        <td>
                                            <div class="action-buttons">

                                                <button class="action-btn btn-edit" data-bs-toggle="modal"
                                                    data-bs-target="#modalEditar{{ $aula->id_aula }}"
                                                    title="Editar">
                                                    <i class="fas fa-pen"></i>
                                                </button>

                                                <button class="action-btn btn-delete" data-bs-toggle="modal"
                                                    data-bs-target="#confirmarEliminar{{ $aula->id_aula }}"
                                                    title="Eliminar">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>

                                            </div>
                                        </td>
                                    </tr>

                                    @include('aulas.modales.editModal')

                                    {{-- Modal Confirmar Eliminar --}}
                                    <div class="modal fade" id="confirmarEliminar{{ $aula->id_aula }}" tabindex="-1"
                                        aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content modal-modern">
                                                <div class="modal-header-delete">
                                                    <div class="modal-icon-delete">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </div>
                                                    <h5 class="modal-title-delete">Confirmar Eliminación</h5>
                                                    <button type="button" class="btn-close-modal"
                                                        data-bs-dismiss="modal" aria-label="Cerrar">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                                <div class="modal-body-delete">
                                                    <p>¿Deseas Eliminar el aula <strong>{{ $aula->nombre_aula }}</strong>?</p>
                                                    <p class="delete-warning">El aula quedará inactiva y no aparecerá en el sistema.</p>
                                                </div>
                                                <div class="modal-footer-delete">
                                                    <form action="{{ route('aulas.destroy', $aula) }}" method="POST"
                                                        class="w-100">
                                                        @csrf
                                                        @method('DELETE')
                                                        <div class="footer-buttons">
                                                            <button type="button" class="btn-modal-cancel"
                                                                data-bs-dismiss="modal">Cancelar</button>
                                                            <button type="submit"
                                                                class="btn-modal-delete">Eliminar</button>
                                                        </div>
                                                    </form>
                                                </div>
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

        // Limpiar modal al cerrar
        document.getElementById('modalCrear').addEventListener('hidden.bs.modal', () => {
            inputCrear.value = '';
            inputCrear.classList.remove('is-invalid');
            ocultarTodos('crear');
            setBoton('btnGuardarCrear', true);
        });

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
</script>
@endsection
@endsection
