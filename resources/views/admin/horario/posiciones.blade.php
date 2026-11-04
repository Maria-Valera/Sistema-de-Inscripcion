@extends('adminlte::page')

@section('title', 'Posiciones Pedagógicas por Materia')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modal-styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/horario-styles.css') }}">
    <style>
        .posicion-card {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
            border: 1px solid rgba(0, 0, 0, 0.08);
            transition: all 0.2s ease;
        }
        .posicion-card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #edf2f7;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .posicion-card-body {
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
        .form-select-modern {
            border-radius: 8px;
            border: 1.5px solid #cbd5e1;
            padding: 0.6rem 0.85rem;
            font-size: 0.95rem;
            background-color: #fff;
            transition: all 0.15s ease-in-out;
            width: 100%;
        }
        .form-select-modern:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
            outline: none;
        }

        /* Cuadrícula de Posiciones */
        .tabla-posiciones {
            width: 100%;
            border-collapse: separate;
            border-spacing: 6px;
        }
        .tabla-posiciones th {
            text-align: center;
            padding: 0.75rem 0.5rem;
            background: #f8fafc;
            color: #334155;
            font-weight: 700;
            font-size: 0.9rem;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }
        .tabla-posiciones td.hora-col {
            background: #f1f5f9;
            font-weight: 600;
            color: #475569;
            font-size: 0.82rem;
            text-align: center;
            border-radius: 8px;
            padding: 0.75rem 0.5rem;
            border: 1px solid #e2e8f0;
            vertical-align: middle;
            width: 130px;
        }
        .celda-posicion {
            height: 64px;
            border-radius: 8px;
            padding: 0.5rem;
            text-align: center;
            vertical-align: middle;
            cursor: pointer;
            user-select: none;
            transition: all 0.15s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }
        .celda-posicion:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        /* Estados de la celda */
        .estado-disponible {
            background-color: #ffffff;
            border: 1.5px solid #e2e8f0;
            color: #64748b;
        }
        .estado-disponible:hover {
            border-color: #94a3b8;
        }

        .estado-no_preferente {
            background-color: #fef9c3;
            border: 1.5px solid #eab308;
            color: #854d0e;
        }

        .estado-no_preferente_fuerte {
            background-color: #ffedd5;
            border: 1.5px solid #f97316;
            color: #9a3412;
        }

        .estado-prohibido {
            background-color: #fee2e2;
            border: 1.5px solid #ef4444;
            color: #991b1b;
        }

        .pos-badge {
            font-size: 0.72rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            padding: 0.2rem 0.5rem;
            border-radius: 6px;
        }

        /* Leyenda */
        .legend-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 1rem 1.25rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .legend-item {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .legend-color-box {
            width: 24px;
            height: 24px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
        }
    </style>
@stop

@section('content_header')
    {{ Breadcrumbs::render('admin.horario.posiciones') }}
    <div class="content-header-modern mt-2">
        <div class="header-content">
            <div class="header-title">
                <div class="icon-wrapper">
                    <i class="fas fa-th"></i>
                </div>
                <div>
                    <h1 class="title-main">Posiciones Pedagógicas por Materia</h1>
                    <p class="title-subtitle">Define restricciones pedagógicas (preferencias y prohibiciones de horario) para cada área de formación</p>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('admin.horario.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2 px-3 py-2" style="border-radius: 6px; font-weight: 500;">
                    <i class="fas fa-arrow-left"></i>
                    <span>Volver a Horarios</span>
                </a>
                <button type="button" id="btnGuardarPosiciones" class="btn btn-primary d-inline-flex align-items-center gap-2 px-4 py-2" style="border-radius: 6px; font-weight: 600;">
                    <i class="fas fa-save"></i>
                    <span>Guardar Reglas</span>
                </button>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="main-container">
        <!-- Panel de Filtros / Selección -->
        <div class="posicion-card mb-4">
            <div class="posicion-card-header">
                <h3 class="section-title">
                    <i class="fas fa-filter text-primary"></i>
                    <span>Selección de Área y Grado</span>
                </h3>
                <span class="badge bg-primary px-3 py-2" style="font-size: 0.82rem;">
                    <i class="fas fa-info-circle me-1"></i> Haz clic en las celdas para alternar estados
                </span>
            </div>
            <div class="posicion-card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label-modern" for="selectArea">
                            <i class="fas fa-book text-muted"></i>
                            <span>Área de Formación</span>
                        </label>
                        <select id="selectArea" class="form-select-modern">
                            @foreach ($areas as $a)
                                <option value="{{ $a->id }}" {{ $areaId == $a->id ? 'selected' : '' }}>
                                    {{ $a->nombre_area_formacion }} ({{ $a->codigo_area ?? 'S/C' }} - {{ $a->bloques_semanales ?? '?' }} bloques/sem)
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label-modern" for="selectGrado">
                            <i class="fas fa-layer-group text-muted"></i>
                            <span>Nivel / Grado</span>
                        </label>
                        <select id="selectGrado" class="form-select-modern">
                            <option value="0" {{ empty($gradoId) ? 'selected' : '' }}>
                                🌐 Todos los grados (Regla General)
                            </option>
                            @foreach ($grados as $g)
                                <option value="{{ $g->id }}" {{ $gradoId == $g->id ? 'selected' : '' }}>
                                    {{ $g->numero_grado }}° Año
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted d-block mt-1">
                            * Una regla específica de grado tiene prioridad sobre la regla general.
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Leyenda de Estados -->
        <div class="legend-card mb-4 shadow-sm">
            <div class="d-flex align-items-center gap-1 text-muted fw-bold me-2" style="font-size: 0.85rem;">
                <i class="fas fa-traffic-light"></i>
                <span>Ciclo al hacer clic:</span>
            </div>
            <div class="legend-item text-secondary">
                <div class="legend-color-box estado-disponible">
                    <i class="fas fa-check"></i>
                </div>
                <span>Disponible (Costo 0)</span>
            </div>
            <i class="fas fa-arrow-right text-muted" style="font-size: 0.75rem;"></i>
            <div class="legend-item" style="color: #854d0e;">
                <div class="legend-color-box estado-no_preferente">
                    <i class="fas fa-exclamation"></i>
                </div>
                <span>No preferente (+1)</span>
            </div>
            <i class="fas fa-arrow-right text-muted" style="font-size: 0.75rem;"></i>
            <div class="legend-item" style="color: #9a3412;">
                <div class="legend-color-box estado-no_preferente_fuerte">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <span>No preferente fuerte (+3)</span>
            </div>
            <i class="fas fa-arrow-right text-muted" style="font-size: 0.75rem;"></i>
            <div class="legend-item text-danger">
                <div class="legend-color-box estado-prohibido">
                    <i class="fas fa-ban"></i>
                </div>
                <span>Prohibido (Bloqueado)</span>
            </div>

            <div class="ms-auto">
                <button type="button" id="btnLimpiarCuadricula" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1">
                    <i class="fas fa-eraser"></i>
                    <span>Restablecer Todo a Disponible</span>
                </button>
            </div>
        </div>

        <!-- Cuadrícula Días x Bloques -->
        <div class="posicion-card">
            <div class="posicion-card-body p-3">
                <div class="table-responsive">
                    <table class="tabla-posiciones" id="tablaPosiciones">
                        <thead>
                            <tr>
                                <th style="width: 130px;">
                                    <i class="far fa-clock me-1"></i> Bloque
                                </th>
                                @foreach ($dias as $dia)
                                    <th>{{ $dia->label() }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @for ($b = 1; $b <= $numeroBloques; $b++)
                                @php
                                    $infoBloque = $bloques->get($b, ['inicio' => '--:--', 'fin' => '--:--']);
                                @endphp
                                <tr>
                                    <td class="hora-col">
                                        <div class="fw-bold">Bloque {{ $b }}</div>
                                        <div class="text-muted" style="font-size: 0.75rem;">
                                            {{ $infoBloque['inicio'] }} - {{ $infoBloque['fin'] }}
                                        </div>
                                    </td>
                                    @foreach ($dias as $dia)
                                        @php
                                            $clave = $dia->value . '_' . $b;
                                            $estado = $posicionesMap[$clave] ?? 'disponible';
                                        @endphp
                                        <td class="celda-posicion estado-{{ $estado }}"
                                            data-dia="{{ $dia->value }}"
                                            data-bloque="{{ $b }}"
                                            data-tipo="{{ $estado }}"
                                            onclick="ciclarEstadoCelda(this)">
                                            <div class="contenido-celda">
                                                @if ($estado === 'disponible')
                                                    <span class="pos-badge text-muted">
                                                        <i class="fas fa-check"></i> Disponible
                                                    </span>
                                                @elseif ($estado === 'no_preferente')
                                                    <span class="pos-badge" style="background: rgba(234, 179, 8, 0.2); color: #854d0e;">
                                                        <i class="fas fa-exclamation-circle"></i> No pref. (+1)
                                                    </span>
                                                @elseif ($estado === 'no_preferente_fuerte')
                                                    <span class="pos-badge" style="background: rgba(249, 115, 22, 0.2); color: #9a3412;">
                                                        <i class="fas fa-exclamation-triangle"></i> No pref. ++ (+3)
                                                    </span>
                                                @elseif ($estado === 'prohibido')
                                                    <span class="pos-badge" style="background: rgba(239, 68, 68, 0.2); color: #991b1b;">
                                                        <i class="fas fa-ban"></i> Prohibido
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                    @endforeach
                                </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Secuencia de ciclado: disponible -> no_preferente -> no_preferente_fuerte -> prohibido -> disponible
        const ESTADOS = ['disponible', 'no_preferente', 'no_preferente_fuerte', 'prohibido'];

        const BADGES_CONFIG = {
            'disponible': {
                html: '<span class="pos-badge text-muted"><i class="fas fa-check"></i> Disponible</span>',
                clase: 'estado-disponible'
            },
            'no_preferente': {
                html: '<span class="pos-badge" style="background: rgba(234, 179, 8, 0.2); color: #854d0e;"><i class="fas fa-exclamation-circle"></i> No pref. (+1)</span>',
                clase: 'estado-no_preferente'
            },
            'no_preferente_fuerte': {
                html: '<span class="pos-badge" style="background: rgba(249, 115, 22, 0.2); color: #9a3412;"><i class="fas fa-exclamation-triangle"></i> No pref. ++ (+3)</span>',
                clase: 'estado-no_preferente_fuerte'
            },
            'prohibido': {
                html: '<span class="pos-badge" style="background: rgba(239, 68, 68, 0.2); color: #991b1b;"><i class="fas fa-ban"></i> Prohibido</span>',
                clase: 'estado-prohibido'
            }
        };

        function ciclarEstadoCelda(celda) {
            const actual = celda.getAttribute('data-tipo') || 'disponible';
            const indiceActual = ESTADOS.indexOf(actual);
            const nuevoIndice = (indiceActual + 1) % ESTADOS.length;
            const nuevoEstado = ESTADOS[nuevoIndice];

            aplicarEstadoACelda(celda, nuevoEstado);
        }

        function aplicarEstadoACelda(celda, nuevoEstado) {
            celda.setAttribute('data-tipo', nuevoEstado);

            // Remover clases de estado previas
            ESTADOS.forEach(e => celda.classList.remove('estado-' + e));
            celda.classList.add('estado-' + nuevoEstado);

            const cfg = BADGES_CONFIG[nuevoEstado] || BADGES_CONFIG['disponible'];
            celda.querySelector('.contenido-celda').innerHTML = cfg.html;
        }

        // Limpiar toda la cuadrícula a disponible
        document.getElementById('btnLimpiarCuadricula').addEventListener('click', function() {
            document.querySelectorAll('.celda-posicion').forEach(celda => {
                aplicarEstadoACelda(celda, 'disponible');
            });
        });

        // Al cambiar Área o Grado, consultar vía AJAX
        const selectArea = document.getElementById('selectArea');
        const selectGrado = document.getElementById('selectGrado');

        function cargarPosiciones() {
            const areaId = selectArea.value;
            const gradoId = selectGrado.value;

            if (!areaId) return;

            // Restablecer todas a disponible inicialmente
            document.querySelectorAll('.celda-posicion').forEach(c => aplicarEstadoACelda(c, 'disponible'));

            fetch(`{{ route('admin.horario.posiciones.consultar') }}?area_formacion_id=${areaId}&grado_id=${gradoId}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success && data.posiciones) {
                    data.posiciones.forEach(p => {
                        const selector = `.celda-posicion[data-dia="${p.dia}"][data-bloque="${p.bloque}"]`;
                        const celda = document.querySelector(selector);
                        if (celda) {
                            aplicarEstadoACelda(celda, p.tipo);
                        }
                    });
                }
            })
            .catch(err => {
                console.error('Error al cargar posiciones:', err);
            });
        }

        selectArea.addEventListener('change', cargarPosiciones);
        selectGrado.addEventListener('change', cargarPosiciones);

        // Guardar reglas en backend
        document.getElementById('btnGuardarPosiciones').addEventListener('click', function() {
            const areaId = selectArea.value;
            const gradoId = selectGrado.value;

            if (!areaId) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Atención',
                    text: 'Debes seleccionar un área de formación.'
                });
                return;
            }

            const btn = this;
            const textoOriginal = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';

            // Recopilar estado de cada celda
            const posiciones = [];
            document.querySelectorAll('.celda-posicion').forEach(c => {
                posiciones.push({
                    dia: parseInt(c.getAttribute('data-dia')),
                    bloque: parseInt(c.getAttribute('data-bloque')),
                    tipo: c.getAttribute('data-tipo') || 'disponible'
                });
            });

            fetch('{{ route('admin.horario.posiciones.guardar') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    area_formacion_id: parseInt(areaId),
                    grado_id: gradoId === '0' || gradoId === '' ? null : parseInt(gradoId),
                    posiciones: posiciones
                })
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = textoOriginal;

                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Guardado!',
                        text: data.mensaje || 'Reglas guardadas exitosamente.',
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.mensaje || 'No se pudieron guardar las reglas.'
                    });
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = textoOriginal;
                Swal.fire({
                    icon: 'error',
                    title: 'Error de Conexión',
                    text: 'Ocurrió un error al comunicarse con el servidor.'
                });
            });
        });
    </script>
@stop
