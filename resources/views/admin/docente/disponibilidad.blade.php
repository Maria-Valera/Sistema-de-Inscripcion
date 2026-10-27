@extends('adminlte::page')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/view-styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <style>
        .disponibilidad-grid {
            border-collapse: collapse;
            width: 100%;
            margin-top: 1rem;
        }
        .disponibilidad-grid th,
        .disponibilidad-grid td {
            border: 1px solid #dee2e6;
            padding: 0.75rem;
            text-align: center;
        }
        .disponibilidad-grid th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        .disponibilidad-cell {
            cursor: pointer;
            transition: background-color 0.2s;
            min-width: 80px;
            min-height: 40px;
        }
        .disponibilidad-cell:hover {
            background-color: #e9ecef;
        }
        .disponibilidad-cell.no-disponible {
            background-color: #dc3545;
            color: white;
        }
        .disponibilidad-cell.no-disponible:hover {
            background-color: #c82333;
        }
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
        .loading-spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
@stop

@section('title', 'Disponibilidad del Docente')

@section('content_header')
    <div class="content-header-modern">
        <div class="header-content">
            <div class="header-title">
                <div class="icon-wrapper">
                    <i class="fas fa-calendar-times"></i>
                </div>
                <div>
                    <h1 class="title-main">Disponibilidad del Docente</h1>
                    <p class="title-subtitle">Gestione los horarios no disponibles</p>
                </div>
            </div>
            <div class="header-actions">
                <a href="{{ route('admin.docente.estudios', $docente->id) }}" class="btn-create" style="background: var(--gray-500);">
                    <i class="fas fa-arrow-left"></i>
                    <span>Volver a Estudios</span>
                </a>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="main-container">
        <div class="card-modern">
            <div class="card-header-modern">
                <div class="header-left">
                    <div class="header-icon">
                        <i class="fas fa-user-clock"></i>
                    </div>
                    <div>
                        <h3>Docente: {{ $docente->persona->primer_nombre }} {{ $docente->persona->primer_apellido }}</h3>
                        <p>Cédula: {{ $docente->persona->tipoDocumento->nombre }}-{{ $docente->persona->numero_documento }}</p>
                    </div>
                </div>
            </div>

            <div class="card-body-modern" style="padding: 2rem;">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label-modern">
                            <i class="fas fa-calendar-alt"></i>
                            Año Escolar
                        </label>
                        <select id="anioEscolarSelect" class="form-control-modern">
                            @foreach($aniosEscolares as $anio)
                                <option value="{{ $anio->id }}" 
                                    {{ $anioEscolarActivo && $anio->id == $anioEscolarActivo->id ? 'selected' : '' }}>
                                    {{ $anio->inicio_anio_escolar->format('Y') }} - {{ $anio->cierre_anio_escolar->format('Y') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="alert-modern alert-info alert alert-dismissible fade show" role="alert">
                    <div class="alert-icon">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <div class="alert-content">
                        <h4>Instrucciones</h4>
                        <p style="margin: 0;">Haga clic en una celda para marcarla como "no disponible" (rojo). Haga clic nuevamente para desmarcarla.</p>
                    </div>
                </div>

                <div id="disponibilidadContainer">
                    <table class="disponibilidad-grid">
                        <thead>
                            <tr>
                                <th style="vertical-align: middle;">Bloque</th>
                                @foreach($diasSemana as $dia)
                                    <th>
                                        <div class="fw-bold mb-1">{{ $dia->nombre_dia ?? $dia->nombre }}</div>
                                        <button type="button" class="btn btn-xs btn-outline-secondary btn-dia-completo" style="font-size: 0.72rem; padding: 2px 8px; border-radius: 4px;" onclick="toggleDiaCompleto({{ $dia->id ?? $dia->dia }})">
                                            <i class="fas fa-calendar-check me-1"></i>Día completo
                                        </button>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody id="disponibilidadBody">
                            <!-- Las filas se generarán dinámicamente con JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner"></div>
    </div>
@endsection

@section('js')
    <script>
        const docenteId = {{ $docente->id }};
        const cedulaPersona = '{{ $docente->persona->numero_documento }}';
        const diasSemana = @json($diasSemana);
        const bloquesHorario = @json($bloquesHorario);
        let anioEscolarId = {{ $anioEscolarActivo ? $anioEscolarActivo->id : ($aniosEscolares->first()->id ?? 0) }};
        let noDisponibilidades = [];

        // Cargar disponibilidades al cambiar el año escolar
        document.getElementById('anioEscolarSelect').addEventListener('change', function() {
            anioEscolarId = this.value;
            cargarNoDisponibilidades();
        });

        // Función para mostrar/ocultar loading
        function showLoading(show) {
            document.getElementById('loadingOverlay').style.display = show ? 'flex' : 'none';
        }

        // Función para cargar las no disponibilidades
        async function cargarNoDisponibilidades() {
            showLoading(true);
            try {
                const response = await fetch(`/api/docente-no-disponibilidad?cedula_persona=${cedulaPersona}&anio_escolar=${anioEscolarId}`);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                noDisponibilidades = await response.json();
                renderizarGrilla();
            } catch (error) {
                console.error('Error al cargar disponibilidades:', error);
                alert('Error al cargar las disponibilidades. Por favor, intente nuevamente.');
            } finally {
                showLoading(false);
            }
        }

        // Función para renderizar la grilla
        function renderizarGrilla() {
            const tbody = document.getElementById('disponibilidadBody');
            tbody.innerHTML = '';

            bloquesHorario.forEach((bloque, idx) => {
                const row = document.createElement('tr');
                const numBloque = Number(bloque.bloque || bloque.id || (idx + 1));
                
                // Celda del bloque
                const bloqueCell = document.createElement('td');
                bloqueCell.innerHTML = `<strong>Bloque ${numBloque}</strong><br><small class="text-muted">${bloque.hora_inicio || ''} - ${bloque.hora_fin || ''}</small>`;
                row.appendChild(bloqueCell);

                // Celdas de cada día
                diasSemana.forEach(dia => {
                    const numDia = Number(dia.dia || dia.id);
                    const cell = document.createElement('td');
                    cell.className = 'disponibilidad-cell';
                    cell.dataset.diaId = numDia;
                    cell.dataset.bloqueId = numBloque;

                    // Verificar si esta celda está marcada como no disponible
                    const match = noDisponibilidades.find(nd => 
                        Number(nd.dia || nd.dias_semana_id) === numDia &&
                        Number(nd.bloque || nd.id_bloque_hora) === numBloque
                    );

                    if (match) {
                        cell.classList.add('no-disponible');
                        cell.dataset.registroId = match.id;
                        cell.innerHTML = '<span class="badge bg-danger" style="font-size: 0.75rem;"><i class="fas fa-ban me-1"></i>No Disp.</span>';
                    } else {
                        cell.innerHTML = '';
                    }

                    cell.addEventListener('click', () => toggleDisponibilidad(cell));
                    row.appendChild(cell);
                });

                tbody.appendChild(row);
            });
        }

        // Función para alternar disponibilidad de una sola celda
        async function toggleDisponibilidad(cell) {
            const diaId = cell.dataset.diaId;
            const bloqueId = cell.dataset.bloqueId;
            const isNoDisponible = cell.classList.contains('no-disponible');

            showLoading(true);

            try {
                if (isNoDisponible) {
                    // Eliminar registro
                    const registroId = cell.dataset.registroId;
                    const response = await fetch(`/api/docente-no-disponibilidad/${registroId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });
                    
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    
                    cell.classList.remove('no-disponible');
                    cell.innerHTML = '';
                    delete cell.dataset.registroId;
                } else {
                    // Crear registro
                    const response = await fetch('/api/docente-no-disponibilidad', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            cedula_persona: cedulaPersona,
                            dia: diaId,
                            bloque: bloqueId,
                            id_dias_semana: diaId,
                            id_bloque: bloqueId,
                            anio_escolar: anioEscolarId,
                            tipo: 'prohibido',
                            estado: 'aprobada',
                            motivo: 'Marcado por administrador'
                        })
                    });
                    
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    
                    const data = await response.json();
                    cell.classList.add('no-disponible');
                    cell.dataset.registroId = data.id;
                    cell.innerHTML = '<span class="badge bg-danger" style="font-size: 0.75rem;"><i class="fas fa-ban me-1"></i>No Disp.</span>';
                }
            } catch (error) {
                console.error('Error al cambiar disponibilidad:', error);
                alert('Error al cambiar la disponibilidad. Por favor, intente nuevamente.');
            } finally {
                showLoading(false);
            }
        }

        // Función para alternar día completo (todos los bloques de ese día)
        window.toggleDiaCompleto = async function(diaId) {
            const celdasDelDia = document.querySelectorAll(`.disponibilidad-cell[data-dia-id="${diaId}"]`);
            if (!celdasDelDia.length) return;

            const todasMarcadas = Array.from(celdasDelDia).every(c => c.classList.contains('no-disponible'));
            showLoading(true);

            try {
                if (todasMarcadas) {
                    // Desmarcar todas las celdas de ese día
                    for (const cell of celdasDelDia) {
                        const registroId = cell.dataset.registroId;
                        if (registroId) {
                            await fetch(`/api/docente-no-disponibilidad/${registroId}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                }
                            });
                        }
                    }
                } else {
                    // Marcar todas las celdas faltantes de ese día
                    for (const cell of celdasDelDia) {
                        if (!cell.classList.contains('no-disponible')) {
                            const bloqueId = cell.dataset.bloqueId;
                            await fetch('/api/docente-no-disponibilidad', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                },
                                body: JSON.stringify({
                                    cedula_persona: cedulaPersona,
                                    dia: diaId,
                                    bloque: bloqueId,
                                    id_dias_semana: diaId,
                                    id_bloque: bloqueId,
                                    anio_escolar: anioEscolarId,
                                    tipo: 'prohibido',
                                    estado: 'aprobada',
                                    motivo: 'Día completo marcado por administrador'
                                })
                            });
                        }
                    }
                }
                await cargarNoDisponibilidades();
            } catch (error) {
                console.error('Error al alternar día completo:', error);
                alert('Ocurrió un error al procesar el día completo.');
            } finally {
                showLoading(false);
            }
        };

        // Cargar disponibilidades al iniciar
        cargarNoDisponibilidades();
    </script>
@endsection