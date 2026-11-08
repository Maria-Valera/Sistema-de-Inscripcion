@extends('adminlte::page')

@section('title', 'Configuración de Bloques Horarios')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modal-styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/horario-styles.css') }}">
    <style>
        .config-card {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
            border: 1px solid rgba(0, 0, 0, 0.08);
            transition: all 0.2s ease;
        }
        .config-card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #edf2f7;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .config-card-body {
            padding: 1.5rem;
        }
        .section-title {
            font-size: 1.05rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .form-label-modern {
            font-size: 0.85rem;
            font-weight: 600;
            color: #475569;
            margin-bottom: 0.35rem;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }
        .form-control-modern {
            border-radius: 8px;
            border: 1.5px solid #cbd5e1;
            padding: 0.6rem 0.85rem;
            font-size: 0.95rem;
            transition: all 0.15s ease-in-out;
        }
        .form-control-modern:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        }
        .timeline-block-item {
            background: #f8fafc;
            border-left: 4px solid #3b82f6;
            border-radius: 8px;
            padding: 0.85rem 1.15rem;
            margin-bottom: 0.65rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: transform 0.15s ease;
        }
        .timeline-block-item:hover {
            transform: translateX(4px);
            background: #f1f5f9;
        }
        .timeline-receso-item {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            border-radius: 8px;
            padding: 0.6rem 1rem;
            margin-bottom: 0.65rem;
            color: #92400e;
            font-size: 0.88rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .receso-row {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 0.75rem 1rem;
        }
        .reservado-row {
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 8px;
            padding: 0.75rem 1rem;
        }
        .badge-kpi {
            background: #eff6ff;
            color: #1d4ed8;
            font-weight: 600;
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
        }
    </style>
@stop

@section('content_header')
    <div class="content-header-modern">
        <div class="header-content">
            <div class="header-title">
                <div class="icon-wrapper">
                    <i class="fas fa-sliders-h"></i>
                </div>
                <div>
                    <h1 class="title-main">Configuración de Bloques Horarios</h1>
                    <p class="title-subtitle">
                        Marco horario institucional y bloques reservados &bull; Año Escolar: 
                        <strong>{{ $anioActivo->nombre_completo ?? 'Año Escolar Activo' }}</strong>
                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('admin.horario.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2 px-3 py-2" style="border-radius: 6px;">
                    <i class="fas fa-arrow-left"></i>
                    <span>Volver al Horario</span>
                </a>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid px-0">
        {{-- Alertas --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm" role="alert" style="border-radius: 8px;">
                <i class="fas fa-check-circle fa-lg"></i>
                <div>{{ session('success') }}</div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm" role="alert" style="border-radius: 8px;">
                <i class="fas fa-exclamation-triangle fa-lg"></i>
                <div>{{ session('error') }}</div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert" style="border-radius: 8px;">
                <strong><i class="fas fa-times-circle"></i> Por favor corrige los siguientes errores:</strong>
                <ul class="mb-0 mt-1 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Formulario Unificado Vertical --}}
        <form action="{{ route('admin.horario.configuracion.update') }}" method="POST" id="configHorarioForm">
            @csrf
            @method('PUT')

            <div class="row g-4">
                {{-- Columna Izquierda: Parámetros, Recesos y Bloques Reservados --}}
                <div class="col-lg-6 col-xl-5">
                    {{-- 1. Parámetros de la Jornada --}}
                    <div class="config-card mb-4">
                        <div class="config-card-header">
                            <h2 class="section-title">
                                <i class="fas fa-clock text-primary"></i> Parámetros de la Jornada
                            </h2>
                            <span class="badge badge-kpi">
                                <i class="fas fa-calendar-check me-1"></i> {{ $anioActivo->status }}
                            </span>
                        </div>
                        <div class="config-card-body">
                            {{-- Hora de inicio --}}
                            <div class="mb-3">
                                <label for="hora_inicio" class="form-label-modern">
                                    <i class="fas fa-play text-muted"></i> Hora de Inicio de Jornada
                                </label>
                                <input type="time" 
                                       class="form-control form-control-modern @error('hora_inicio') is-invalid @enderror" 
                                       id="hora_inicio" 
                                       name="hora_inicio" 
                                       value="{{ old('hora_inicio', substr($configuracion->hora_inicio ?? '07:00:00', 0, 5)) }}" 
                                       required>
                                <small class="text-muted">Hora en que inicia el primer bloque lectivo.</small>
                            </div>

                            {{-- Número de bloques por día --}}
                            <div class="mb-3">
                                <label for="numero_bloques" class="form-label-modern">
                                    <i class="fas fa-th text-muted"></i> Número de Bloques por Día
                                </label>
                                <input type="number" 
                                       class="form-control form-control-modern @error('numero_bloques') is-invalid @enderror" 
                                       id="numero_bloques" 
                                       name="numero_bloques" 
                                       min="{{ max(1, $maxBloqueOcupado) }}" 
                                       max="12" 
                                       value="{{ old('numero_bloques', $configuracion->numero_bloques ?? 4) }}" 
                                       required>
                                @if ($maxBloqueOcupado > 0)
                                    <small class="text-warning d-block mt-1">
                                        <i class="fas fa-info-circle"></i> Bloque mínimo permitido: <strong>{{ $maxBloqueOcupado }}</strong> (hay registros existentes).
                                    </small>
                                @else
                                    <small class="text-muted">Cantidad de períodos/bloques de clase diarios (ej. 4 bloques).</small>
                                @endif
                            </div>

                            {{-- Duración del bloque --}}
                            <div class="mb-3">
                                <label for="duracion_bloque_min" class="form-label-modern">
                                    <i class="fas fa-hourglass-half text-muted"></i> Duración de cada Bloque (minutos)
                                </label>
                                <div class="input-group">
                                    <input type="number" 
                                           class="form-control form-control-modern @error('duracion_bloque_min') is-invalid @enderror" 
                                           id="duracion_bloque_min" 
                                           name="duracion_bloque_min" 
                                           min="15" 
                                           max="240" 
                                           step="5"
                                           value="{{ old('duracion_bloque_min', $configuracion->duracion_bloque_min ?? 90) }}" 
                                           required>
                                    <span class="input-group-text bg-light text-muted">min</span>
                                </div>
                                <small class="text-muted">Unidad estándar: 90 min (equivalente a 2 horas académicas de 45 min).</small>
                            </div>

                            {{-- Minutos entre bloques --}}
                            <div class="mb-3">
                                <label for="minutos_entre_bloques" class="form-label-modern">
                                    <i class="fas fa-exchange-alt text-muted"></i> Minutos entre Bloques (Pausa estándar)
                                </label>
                                <div class="input-group">
                                    <input type="number" 
                                           class="form-control form-control-modern @error('minutos_entre_bloques') is-invalid @enderror" 
                                           id="minutos_entre_bloques" 
                                           name="minutos_entre_bloques" 
                                           min="0" 
                                           max="60" 
                                           value="{{ old('minutos_entre_bloques', $configuracion->minutos_entre_bloques ?? 0) }}" 
                                           required>
                                    <span class="input-group-text bg-light text-muted">min</span>
                                </div>
                                <small class="text-muted">Separación automática entre un bloque y el siguiente (habitualmente 0).</small>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Descansos y Recesos --}}
                    <div class="config-card mb-4">
                        <div class="config-card-header">
                            <h2 class="section-title">
                                <i class="fas fa-coffee text-warning"></i> Descansos y Recesos
                            </h2>
                            <button type="button" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1" id="btnAgregarReceso" style="border-radius: 6px;">
                                <i class="fas fa-plus"></i>
                                <span>Agregar Receso</span>
                            </button>
                        </div>
                        <div class="config-card-body">
                            <p class="text-muted small mb-3">
                                Configura pausas especiales dentro de la jornada (ej. recreo institucional). Desplazan las horas de los bloques siguientes.
                            </p>

                            <div id="recesosContainer">
                                {{-- Filas dinámicas generadas con JS --}}
                            </div>

                            <div id="sinRecesosAlert" class="text-center py-3 text-muted" style="display: none;">
                                <i class="fas fa-mug-hot fa-2x mb-2 text-black-50"></i>
                                <p class="mb-0 small">No hay recesos configurados. Los bloques serán continuos.</p>
                            </div>
                        </div>
                    </div>

                    {{-- 3. Bloques Reservados (Grupos Estables) --}}
                    <div class="config-card mb-4">
                        <div class="config-card-header">
                            <h2 class="section-title">
                                <i class="fas fa-lock text-warning"></i> Bloques Reservados (Grupos Estables)
                            </h2>
                            <button type="button" class="btn btn-sm btn-outline-warning text-dark fw-bold d-inline-flex align-items-center gap-1" id="btnAgregarReservado" style="border-radius: 6px;">
                                <i class="fas fa-plus"></i>
                                <span>+ Agregar Bloque Reservado</span>
                            </button>
                        </div>
                        <div class="config-card-body">
                            <p class="text-muted small mb-3">
                                Franjas horarias bloqueadas para todas las secciones, como actos o reuniones (ej. Grupos Estables). Ninguna materia regular podrá asignarse en estos espacios.
                            </p>

                            <div id="reservadosContainer">
                                {{-- Filas dinámicas generadas con JS --}}
                            </div>

                            <div id="sinReservadosAlert" class="text-center py-3 text-muted" style="display: none;">
                                <i class="fas fa-unlock-alt fa-2x mb-2 text-black-50"></i>
                                <p class="mb-0 small">No hay bloques reservados. Todos los bloques están disponibles para clases regulares.</p>
                            </div>
                        </div>
                    </div>

                    {{-- Botón Guardar Todo --}}
                    @if (\App\Support\AnioEscolarSeleccionado::esActual())
                        <div class="d-grid mb-4">
                            <button type="submit" class="btn btn-primary btn-lg shadow-sm d-flex align-items-center justify-content-center gap-2" style="border-radius: 8px;">
                                <i class="fas fa-save"></i>
                                <span>Guardar Configuración de Horario</span>
                            </button>
                        </div>
                    @else
                        <div class="alert alert-info d-flex align-items-center gap-2 mb-4" role="alert">
                            <i class="fas fa-info-circle"></i>
                            <span>El año escolar seleccionado está en modo <strong>Solo consulta</strong>. No se pueden modificar los parámetros de horario.</span>
                        </div>
                    @endif
                </div>

                {{-- Columna Derecha: Vista Previa en Vivo (Estilo GHC) --}}
                <div class="col-lg-6 col-xl-7">
                    <div class="config-card" style="position: sticky; top: 1.5rem;">
                        <div class="config-card-header bg-white">
                            <div>
                                <h2 class="section-title">
                                    <i class="fas fa-eye text-info"></i> Vista Previa en Vivo (Marco Horario)
                                </h2>
                                <small class="text-muted">Cálculo dinámico en tiempo real de la distribución horaria diaria</small>
                            </div>
                            <span class="badge bg-primary px-3 py-2" id="kpiTotalHoras" style="border-radius: 20px; font-size: 0.85rem;">
                                07:00 - 13:20
                            </span>
                        </div>
                        <div class="config-card-body">
                            {{-- Resumen Estadístico Horizontal y Minimalista --}}
                            <div class="d-flex align-items-center gap-3 p-2 px-3 mb-3 border rounded bg-white shadow-sm flex-wrap" style="font-size: 0.9rem;">
                                <div class="d-flex align-items-center gap-1 text-dark fw-bold">
                                    <i class="fas fa-th text-primary"></i> 
                                    <span id="resumen-bloques">{{ $numeroBloques }}</span> Bloques
                                </div>
                                <div class="vr text-muted d-none d-sm-block"></div>
                                <div class="d-flex align-items-center gap-1 text-info fw-bold">
                                    <i class="fas fa-clock"></i> 
                                    <span id="resumen-minutos">{{ $minutosLectivos }}</span>m
                                </div>
                                <div class="vr text-muted d-none d-sm-block"></div>
                                <div class="d-flex align-items-center gap-1 text-warning fw-bold">
                                    <i class="fas fa-coffee"></i> 
                                    <span id="resumen-receso">{{ $minutosReceso }}</span>m receso
                                </div>
                            </div>

                            {{-- Timeline de bloques --}}
                            <h3 class="h6 fw-bold text-secondary text-uppercase mb-3" style="letter-spacing: 0.5px;">
                                Distribución de la Jornada Diaria:
                            </h3>
                            <div id="previewTimeline" class="pe-1" style="max-height: 540px; overflow-y: auto;">
                                {{-- Renderizado reactivo en JS --}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@stop

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Opciones de Días de la semana desde el Enum
            const diasList = [
                @foreach ($dias as $dia)
                    { val: {{ $dia->value }}, label: @json($dia->label()) },
                @endforeach
            ];

            // ==========================================================
            // 1. RECESOS DINÁMICOS
            // ==========================================================
            const initialRecesosRaw = @json(old('recesos', $recesosArray ?? []));
            const initialRecesos = Array.isArray(initialRecesosRaw) 
                ? initialRecesosRaw 
                : (initialRecesosRaw && typeof initialRecesosRaw === 'object' ? Object.values(initialRecesosRaw) : []);

            const recesosContainer = document.getElementById('recesosContainer');
            const btnAgregarReceso = document.getElementById('btnAgregarReceso');
            const sinRecesosAlert = document.getElementById('sinRecesosAlert');
            
            const inputHoraInicio = document.getElementById('hora_inicio');
            const inputNumeroBloques = document.getElementById('numero_bloques');
            const inputDuracionBloque = document.getElementById('duracion_bloque_min');
            const inputMinEntreBloques = document.getElementById('minutos_entre_bloques');

            const previewTimeline = document.getElementById('previewTimeline');
            const kpiTotalHoras = document.getElementById('kpiTotalHoras');
            const statTotalBloques = document.getElementById('resumen-bloques');
            const statMinLectivos = document.getElementById('resumen-minutos');
            const statMinReceso = document.getElementById('resumen-receso');

            let recesoIndex = 0;

            function agregarFilaReceso(despuesDe = 2, duracion = 20) {
                if (!recesosContainer) return;
                const numBloques = parseInt(inputNumeroBloques.value, 10) || 4;
                const index = recesoIndex++;

                let opcionesSelect = '';
                for (let b = 1; b < numBloques; b++) {
                    const selected = (b === parseInt(despuesDe, 10)) ? 'selected' : '';
                    opcionesSelect += `<option value="${b}" ${selected}>Bloque ${b}</option>`;
                }

                const fila = document.createElement('div');
                fila.className = 'd-flex flex-row align-items-end gap-2 mb-2 receso-row';
                fila.id = `receso_row_${index}`;
                fila.innerHTML = `
                    <div class="flex-grow-1" style="min-width: 0;">
                        <label class="form-label small text-muted mb-1">Después del Bloque:</label>
                        <select name="recesos[${index}][despues_de_bloque]" class="form-select form-select-sm select-despues-de">
                            ${opcionesSelect}
                        </select>
                    </div>
                    <div class="flex-grow-1" style="min-width: 0;">
                        <label class="form-label small text-muted mb-1">Duración (min):</label>
                        <input type="number" name="recesos[${index}][duracion_min]" min="5" max="120" step="5" value="${duracion}" class="form-control form-control-sm input-duracion-receso">
                    </div>
                    <div class="flex-shrink-0">
                        <button type="button" class="btn btn-sm btn-outline-danger btn-eliminar-receso" title="Eliminar receso" style="height: 31px; display: inline-flex; align-items: center; justify-content: center;">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                `;

                recesosContainer.appendChild(fila);

                fila.querySelector('.btn-eliminar-receso').addEventListener('click', function () {
                    fila.remove();
                    actualizarEstadoRecesos();
                    recalcularVistaPrevia();
                });

                fila.querySelector('.select-despues-de').addEventListener('change', recalcularVistaPrevia);
                fila.querySelector('.input-duracion-receso').addEventListener('input', recalcularVistaPrevia);

                actualizarEstadoRecesos();
                recalcularVistaPrevia();
            }

            function actualizarEstadoRecesos() {
                if (!recesosContainer || !sinRecesosAlert) return;
                const filas = recesosContainer.querySelectorAll('.receso-row');
                sinRecesosAlert.style.display = filas.length === 0 ? 'block' : 'none';
            }

            function actualizarOpcionesDespuesDe() {
                if (!recesosContainer) return;
                const numBloques = parseInt(inputNumeroBloques.value, 10) || 4;
                const selects = recesosContainer.querySelectorAll('.select-despues-de');

                selects.forEach(sel => {
                    const valorActual = parseInt(sel.value, 10);
                    let opciones = '';
                    for (let b = 1; b < numBloques; b++) {
                        const selected = (b === valorActual) ? 'selected' : '';
                        opciones += `<option value="${b}" ${selected}>Bloque ${b}</option>`;
                    }
                    sel.innerHTML = opciones;
                });
            }

            // ==========================================================
            // 2. BLOQUES RESERVADOS DINÁMICOS (GRUPOS ESTABLES)
            // ==========================================================
            const initialReservadosRaw = @json(old('reservados', $reservadosArray ?? []));
            const initialReservados = Array.isArray(initialReservadosRaw) 
                ? initialReservadosRaw 
                : (initialReservadosRaw && typeof initialReservadosRaw === 'object' ? Object.values(initialReservadosRaw) : []);

            const reservadosContainer = document.getElementById('reservadosContainer');
            const btnAgregarReservado = document.getElementById('btnAgregarReservado');
            const sinReservadosAlert = document.getElementById('sinReservadosAlert');

            let reservadoIndex = 0;

            function agregarFilaReservado(dia = 1, desde = 1, hasta = 1) {
                if (!reservadosContainer) return;
                const numBloques = parseInt(inputNumeroBloques.value, 10) || 4;
                const index = reservadoIndex++;

                const vDia = parseInt(dia, 10) || 1;
                const vDesde = Math.min(numBloques, Math.max(1, parseInt(desde, 10) || 1));
                const vHasta = Math.min(numBloques, Math.max(vDesde, parseInt(hasta, 10) || vDesde));

                let opcionesDias = '';
                diasList.forEach(d => {
                    const selected = (d.val === vDia) ? 'selected' : '';
                    opcionesDias += `<option value="${d.val}" ${selected}>${d.label}</option>`;
                });

                let opcionesDesde = '';
                let opcionesHasta = '';
                for (let b = 1; b <= numBloques; b++) {
                    const selDesde = (b === vDesde) ? 'selected' : '';
                    opcionesDesde += `<option value="${b}" ${selDesde}>Bloque ${b}</option>`;

                    const selHasta = (b === vHasta) ? 'selected' : '';
                    const disHasta = (b < vDesde) ? 'disabled' : '';
                    opcionesHasta += `<option value="${b}" ${selHasta} ${disHasta}>Bloque ${b}</option>`;
                }

                const fila = document.createElement('div');
                fila.className = 'd-flex flex-row align-items-end gap-2 mb-2 reservado-row';
                fila.id = `reservado_row_${index}`;
                fila.innerHTML = `
                    <div class="flex-grow-1" style="min-width: 0; flex-basis: 38%;">
                        <label class="form-label small text-muted mb-1">Día de la Semana:</label>
                        <select name="reservados[${index}][dia]" class="form-select form-select-sm select-reservado-dia">
                            ${opcionesDias}
                        </select>
                    </div>
                    <div class="flex-grow-1" style="min-width: 0; flex-basis: 27%;">
                        <label class="form-label small text-muted mb-1">Desde Bloque:</label>
                        <select name="reservados[${index}][desde]" class="form-select form-select-sm select-reservado-desde">
                            ${opcionesDesde}
                        </select>
                    </div>
                    <div class="flex-grow-1" style="min-width: 0; flex-basis: 27%;">
                        <label class="form-label small text-muted mb-1">Hasta Bloque:</label>
                        <select name="reservados[${index}][hasta]" class="form-select form-select-sm select-reservado-hasta">
                            ${opcionesHasta}
                        </select>
                    </div>
                    <div class="flex-shrink-0">
                        <button type="button" class="btn btn-sm btn-outline-danger btn-eliminar-reservado" title="Eliminar bloque reservado" style="height: 31px; display: inline-flex; align-items: center; justify-content: center;">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                `;

                reservadosContainer.appendChild(fila);

                const selectDesde = fila.querySelector('.select-reservado-desde');
                const selectHasta = fila.querySelector('.select-reservado-hasta');

                function sincronizarHasta() {
                    const curDesde = parseInt(selectDesde.value, 10) || 1;
                    const curHasta = parseInt(selectHasta.value, 10) || curDesde;

                    if (curHasta < curDesde) {
                        selectHasta.value = curDesde;
                    }

                    Array.from(selectHasta.options).forEach(opt => {
                        const val = parseInt(opt.value, 10);
                        opt.disabled = (val < curDesde);
                    });
                }

                selectDesde.addEventListener('change', sincronizarHasta);

                fila.querySelector('.btn-eliminar-reservado').addEventListener('click', function () {
                    fila.remove();
                    actualizarEstadoReservados();
                });

                actualizarEstadoReservados();
            }

            function actualizarEstadoReservados() {
                if (!reservadosContainer || !sinReservadosAlert) return;
                const filas = reservadosContainer.querySelectorAll('.reservado-row');
                sinReservadosAlert.style.display = filas.length === 0 ? 'block' : 'none';
            }

            function actualizarOpcionesBloquesReservados() {
                if (!reservadosContainer) return;
                const numBloques = parseInt(inputNumeroBloques.value, 10) || 4;
                const filas = reservadosContainer.querySelectorAll('.reservado-row');

                filas.forEach(fila => {
                    const selDesde = fila.querySelector('.select-reservado-desde');
                    const selHasta = fila.querySelector('.select-reservado-hasta');
                    if (!selDesde || !selHasta) return;

                    const curDesde = Math.min(numBloques, parseInt(selDesde.value, 10) || 1);
                    const curHasta = Math.min(numBloques, Math.max(curDesde, parseInt(selHasta.value, 10) || curDesde));

                    let optDesde = '';
                    let optHasta = '';
                    for (let b = 1; b <= numBloques; b++) {
                        optDesde += `<option value="${b}" ${b === curDesde ? 'selected' : ''}>Bloque ${b}</option>`;
                        const dis = (b < curDesde) ? 'disabled' : '';
                        optHasta += `<option value="${b}" ${b === curHasta ? 'selected' : ''} ${dis}>Bloque ${b}</option>`;
                    }

                    selDesde.innerHTML = optDesde;
                    selHasta.innerHTML = optHasta;
                });
            }

            // ==========================================================
            // 3. CÁLCULO Y VISTA PREVIA EN VIVO (MARCO HORARIO)
            // ==========================================================
            function formatTime(totalMinutes) {
                const h = Math.floor(totalMinutes / 60) % 24;
                const m = totalMinutes % 60;
                return `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}`;
            }

            function parseTimeToMinutes(timeStr) {
                if (!timeStr) return 7 * 60;
                const parts = timeStr.split(':');
                const h = parseInt(parts[0], 10) || 0;
                const m = parseInt(parts[1], 10) || 0;
                return h * 60 + m;
            }

            function recalcularVistaPrevia() {
                if (!previewTimeline || !inputHoraInicio) return;

                const horaInicioMin = parseTimeToMinutes(inputHoraInicio.value);
                const numBloques = Math.max(1, parseInt(inputNumeroBloques.value, 10) || 1);
                const duracionBloque = Math.max(1, parseInt(inputDuracionBloque.value, 10) || 90);
                const minEntreBloques = Math.max(0, parseInt(inputMinEntreBloques.value, 10) || 0);

                const recesosMap = {};
                let totalMinRecesos = 0;

                const filas = recesosContainer ? recesosContainer.querySelectorAll('.receso-row') : [];
                filas.forEach(fila => {
                    const despuesDe = parseInt(fila.querySelector('.select-despues-de')?.value, 10);
                    const duracion = parseInt(fila.querySelector('.input-duracion-receso')?.value, 10) || 0;
                    if (despuesDe >= 1 && despuesDe < numBloques && duracion > 0) {
                        recesosMap[despuesDe] = duracion;
                        totalMinRecesos += duracion;
                    }
                });

                let currentMin = horaInicioMin;
                let htmlTimeline = '';

                for (let b = 1; b <= numBloques; b++) {
                    const startMin = currentMin;
                    const endMin = startMin + duracionBloque;

                    htmlTimeline += `
                        <div class="timeline-block-item">
                            <div>
                                <span class="badge bg-primary me-2">Bloque ${b}</span>
                                <strong class="fs-6 text-dark">${formatTime(startMin)} - ${formatTime(endMin)}</strong>
                            </div>
                            <div class="text-muted small">
                                <i class="fas fa-hourglass-start me-1"></i> ${duracionBloque} min
                            </div>
                        </div>
                    `;

                    const receso = recesosMap[b] || 0;
                    if (receso > 0) {
                        htmlTimeline += `
                            <div class="timeline-receso-item">
                                <i class="fas fa-coffee"></i>
                                <span>Receso Institucional (${receso} min): ${formatTime(endMin)} - ${formatTime(endMin + receso)}</span>
                            </div>
                        `;
                    }

                    currentMin = endMin + minEntreBloques + receso;
                }

                previewTimeline.innerHTML = htmlTimeline;

                const horaFinJornada = currentMin - minEntreBloques;
                if (kpiTotalHoras) kpiTotalHoras.textContent = `${formatTime(horaInicioMin)} - ${formatTime(horaFinJornada)}`;
                if (statTotalBloques) statTotalBloques.textContent = numBloques;
                if (statMinLectivos) statMinLectivos.textContent = `${numBloques * duracionBloque}`;
                if (statMinReceso) statMinReceso.textContent = `${totalMinRecesos}`;
            }

            // ==========================================================
            // INICIALIZACIÓN
            // ==========================================================
            // Precargar Recesos
            if (Array.isArray(initialRecesos) && initialRecesos.length > 0) {
                initialRecesos.forEach(r => agregarFilaReceso(r.despues_de_bloque, r.duracion_min));
            } else {
                actualizarEstadoRecesos();
            }

            // Precargar Reservados
            if (Array.isArray(initialReservados) && initialReservados.length > 0) {
                initialReservados.forEach(r => {
                    if (r && (r.dia || r.bloque || r.desde)) {
                        const d = r.dia || 1;
                        const bDesde = r.desde || r.bloque || 1;
                        const bHasta = r.hasta || r.bloque || bDesde;
                        agregarFilaReservado(d, bDesde, bHasta);
                    }
                });
            } else {
                actualizarEstadoReservados();
            }

            if (btnAgregarReceso) {
                btnAgregarReceso.addEventListener('click', function () {
                    agregarFilaReceso(2, 20);
                });
            }

            if (btnAgregarReservado) {
                btnAgregarReservado.addEventListener('click', function () {
                    agregarFilaReservado(1, 1, 1);
                });
            }

            if (inputNumeroBloques) {
                inputNumeroBloques.addEventListener('input', function () {
                    actualizarOpcionesDespuesDe();
                    actualizarOpcionesBloquesReservados();
                    recalcularVistaPrevia();
                });
            }

            if (inputHoraInicio) inputHoraInicio.addEventListener('input', recalcularVistaPrevia);
            if (inputDuracionBloque) inputDuracionBloque.addEventListener('input', recalcularVistaPrevia);
            if (inputMinEntreBloques) inputMinEntreBloques.addEventListener('input', recalcularVistaPrevia);

            recalcularVistaPrevia();
        });
    </script>
@stop
