@extends('adminlte::page')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modal-styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pagination.css') }}">
    <link rel="stylesheet" href="{{ asset('css/horario-styles.css') }}">
@stop

@section('title', 'Gestión de Horarios')

@section('content_header')
    <div class="content-header-modern">
        <div class="header-content">
            <div class="header-title">
                <div class="icon-wrapper">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div>
                    <h1 class="title-main">Gestión de Horarios</h1>
                    <p class="title-subtitle">Administración de horarios académicos</p>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                @can('horarios.gestionar')
                    <a href="{{ route('admin.horario.configuracion') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2 px-3 py-2" style="border-radius: 6px; font-weight: 500;">
                        <i class="fas fa-sliders-h"></i>
                        <span>Configurar Bloques</span>
                    </a>
                    <a href="{{ route('admin.horario.posiciones') }}" class="btn btn-outline-primary d-inline-flex align-items-center gap-2 px-3 py-2" style="border-radius: 6px; font-weight: 500;">
                        <i class="fas fa-th"></i>
                        <span>Posiciones por Materia</span>
                    </a>
                @endcan

                @if ($hayErroresCriticos ?? false)
                    <div class="d-inline-flex flex-column align-items-end text-end">
                        <button type="button" class="btn btn-secondary d-inline-flex align-items-center gap-2 px-3 py-2 text-white shadow-none" disabled style="border-radius: 6px; font-weight: 600; cursor: not-allowed; background-color: #94a3b8; border: none; opacity: 0.85;">
                            <i class="fas fa-plus"></i>
                            <span>Nuevo Horario</span>
                        </button>
                        <span class="text-danger fw-semibold mt-1 d-inline-flex align-items-center gap-1" style="font-size: 0.8rem; line-height: 1.2;">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span>⚠️ No puedes crear un horario porque hay errores en la carga académica.</span>
                        </span>
                        <span class="text-muted" style="font-size: 0.74rem; line-height: 1.2;">
                            Corrige los errores en el panel inferior para habilitar este botón.
                        </span>
                    </div>
                @else
                    <a href="{{ route('admin.horario.create') }}" class="btn-create">
                        <i class="fas fa-plus"></i>
                        <span>Nuevo Horario</span>
                    </a>
                @endif
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="main-container">
        @php
            $erroresMostrar = session('errores_validacion') ?? ($erroresCriticos ?? []);
        @endphp

        @if (session('success') || session('error') || session('warning') || (($hayErroresCriticos ?? false) && !empty($erroresMostrar)))
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

                @if (session('warning') || (($hayErroresCriticos ?? false) && !empty($erroresMostrar)))
                    <div class="alert alert-warning alert-dismissible fade show shadow-sm mb-4" role="alert" style="background-color: #fffbeb; border-left: 4px solid #f59e0b; padding: 1.25rem;">
                        <div class="d-flex align-items-start gap-3">
                            <div style="color: #d97706; font-size: 1.5rem; margin-top: -2px;">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="fw-bold mb-1" style="color: #92400e;">
                                    <i class="fas fa-lock me-1"></i> Creación de Horarios Bloqueada
                                </h5>
                                <p class="mb-2" style="color: #78350f;">
                                    {{ session('warning') ?? 'Se han detectado ' . count($erroresMostrar) . ' inconsistencia(s) crítica(s) en la carga académica que impiden crear el horario.' }}
                                </p>
                                @if (!empty($erroresMostrar))
                                    <div>
                                        <button class="btn btn-sm btn-outline-warning text-dark fw-semibold d-inline-flex align-items-center gap-2 mt-1 shadow-sm"
                                                type="button"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#collapseErrores"
                                                aria-expanded="false"
                                                aria-controls="collapseErrores"
                                                id="btnToggleErrores"
                                                style="border-color: #d97706; background-color: #fef3c7;">
                                            <i class="fas fa-eye" id="iconoToggleErrores"></i>
                                            <span id="textoToggleErrores">Ver detalles de los errores ({{ count($erroresMostrar) }})</span>
                                        </button>
                                    </div>

                                    <div class="collapse mt-3" id="collapseErrores">
                                        <div class="p-3 bg-white rounded border border-warning shadow-sm" style="max-height: 380px; overflow-y: auto;">
                                            <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                                <h6 class="fw-bold text-danger mb-0">
                                                    <i class="fas fa-times-circle me-1"></i> Errores críticos que deben solucionarse para habilitar la creación:
                                                </h6>
                                                <span class="badge bg-danger rounded-pill">{{ count($erroresMostrar) }}</span>
                                            </div>
                                            <ul class="mb-0 ps-3 text-danger small">
                                                @foreach ($erroresMostrar as $err)
                                                    <li class="mb-2"><strong>{{ $err['mensaje'] }}</strong>: {{ $err['detalle'] }}</li>
                                                @endforeach
                                            </ul>
                                            <div class="text-end mt-2 pt-2 border-top">
                                                <button class="btn btn-sm btn-link text-muted p-0 text-decoration-none"
                                                        type="button"
                                                        data-bs-toggle="collapse"
                                                        data-bs-target="#collapseErrores">
                                                    <i class="fas fa-chevron-up me-1"></i> Ocultar detalles
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                        </div>
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

        <!-- Encabezado de Filtros Inteligentes -->
        <div class="card-modern mb-4">
            <div class="card-header-modern">
                <div class="header-left">
                    <div class="header-icon">
                        <i class="fas fa-filter"></i>
                    </div>
                    <div>
                        <h3>Filtros de Consulta</h3>
                        <p>Selecciona los parámetros para visualizar horarios (opcional)</p>
                    </div>
                </div>
            </div>
            <div class="card-body-modern p-3">
                <form id="filtroHorarioForm" action="{{ route('admin.horario.index') }}" method="GET" class="filtro-form py-2">
                    <div class="row g-3 justify-content-center align-items-end">
                        <div class="col-md-3">
                            <label class="form-label-modern text-center d-block">
                                <i class="fas fa-graduation-cap" style="color: var(--primary);"></i>
                                Año / Grado
                            </label>
                            <select class="form-select text-center" id="grado_id" name="grado_id">
                                <option value="">Todos los años</option>
                                @foreach($grados as $grado)
                                    <option value="{{ $grado->id }}" {{ request('grado_id') == $grado->id ? 'selected' : '' }}>
                                        {{ $grado->numero_grado }}° Año
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label-modern text-center d-block">
                                <i class="fas fa-users" style="color: var(--primary);"></i>
                                Sección
                            </label>
                            <select class="form-select text-center" id="seccion_id" name="seccion_id">
                                <option value="">Todas las secciones</option>
                                @foreach($secciones as $seccion)
                                    <option value="{{ $seccion->id }}" {{ request('seccion_id') == $seccion->id ? 'selected' : '' }}>
                                        Sección {{ $seccion->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label-modern text-center d-block">
                                <i class="fas fa-book" style="color: var(--primary);"></i>
                                Área de Formación
                            </label>
                            <select class="form-select text-center" id="area_formacion_id" name="area_formacion_id">
                                <option value="">Todas las áreas</option>
                                @foreach($areasFormacion as $area)
                                    <option value="{{ $area->id }}" {{ request('area_formacion_id') == $area->id ? 'selected' : '' }}>
                                        {{ $area->nombre_area_formacion }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3 d-flex justify-content-center gap-2">
                            <button type="submit" class="btn-generate">
                                <i class="fas fa-search"></i>
                                <span>Filtrar</span>
                            </button>
                            @if(request()->hasAny(['grado_id', 'seccion_id', 'area_formacion_id']))
                                <a href="{{ route('admin.horario.index') }}" class="btn btn-light d-flex align-items-center justify-content-center" title="Limpiar filtros">
                                    <i class="fas fa-undo"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Listado de Horarios Generados -->
        <div class="card-modern">
            <div class="card-header-modern">
                <div class="header-left">
                    <div class="header-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div>
                        <h3 class="mb-0">Listado de Horarios</h3>
                        <p class="mb-0">{{ $horariosPaginados->total() }} registros encontrados</p>
                    </div>
                </div>
            </div>
            <div class="card-body-modern">
                <div class="table-wrapper">
                    <table class="table-modern overflow-hidden">
                        <thead>
                            <tr style="text-align: center">
                                <th style="text-align: center">#</th>
                                <th style="text-align: center">Año / Grado</th>
                                <th style="text-align: center">Sección</th>
                                <th style="text-align: center">Áreas de Formación</th>
                                <th style="text-align: center">Total Clases</th>
                                <th style="text-align: center">Estado</th>
                                <th style="text-align: center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody style="text-align: center">
                            @if($horariosPaginados->isEmpty())
                                <tr>
                                    <td colspan="7">
                                        <div class="empty-state">
                                            <div class="empty-icon mb-2">
                                                <i class="fas fa-calendar-times fa-3x text-muted"></i>
                                            </div>
                                            <h4>No hay horarios asignados</h4>
                                            <p class="text-muted">Genera o registra un nuevo horario para comenzar.</p>
                                        </div>
                                    </td>
                                </tr>
                            @else
                                @foreach($horariosPaginados as $index => $horario)
                                    <tr class="row-12" style="text-align: center">
                                        <td>{{ ($horariosPaginados->currentPage() - 1) * $horariosPaginados->perPage() + $index + 1 }}</td>
                                        <td class="title-main fw-bold">{{ $horario['grado'] }}° Año</td>
                                        <td>
                                            <span class="badge bg-light text-dark border px-2 py-1">
                                                Sección {{ $horario['seccion'] }}
                                            </span>
                                        </td>
                                        <td class="text-truncate" style="max-width: 320px;" title="{{ $horario['areas'] }}">
                                            {{ $horario['areas'] }}
                                        </td>
                                        <td>
                                            <span class="badge bg-primary px-2 py-1">
                                                {{ $horario['total_asignaciones'] }} bloque(s)
                                            </span>
                                        </td>
                                        <td>
                                            <span class="status-badge status-active">
                                                <span class="status-dot"></span>
                                                Activo
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <div class="dropdown dropstart text-center">
                                                    <button class="btn btn-light btn-sm rounded-circle shadow-sm action-btn"
                                                        type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                                        <li>
                                                            <button class="dropdown-item d-flex align-items-center text-primary"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#viewModalHorario"
                                                                onclick="cargarHorarioEnModal({{ $horario['grado_id'] }}, {{ $horario['seccion_id'] }}, '{{ $horario['grado'] }}', '{{ $horario['seccion'] }}')">
                                                                <i class="fas fa-eye me-2"></i>
                                                                Ver Horario
                                                            </button>
                                                        </li>
                                                        <li>
                                                            <a href="{{ route('admin.horario.edit', ['grado_id' => $horario['grado_id'], 'seccion_id' => $horario['seccion_id']]) }}"
                                                                class="dropdown-item d-flex align-items-center text-warning">
                                                                <i class="fas fa-pen me-2"></i>
                                                                Editar
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <button class="dropdown-item d-flex align-items-center text-danger"
                                                                onclick="generarPDF({{ $horario['grado_id'] }}, {{ $horario['seccion_id'] }})">
                                                                <i class="fas fa-file-pdf me-2"></i>
                                                                PDF Horario
                                                            </button>
                                                        </li>
                                                        <li>
                                                            <button class="dropdown-item d-flex align-items-center text-danger"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#confirmarEliminar{{ $horario['id'] }}">
                                                                <i class="fas fa-trash me-2"></i>
                                                                Eliminar
                                                            </button>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <x-pagination :paginator="$horariosPaginados" />

        <!-- Incluir modales -->
        @include('admin.horario.modales.showModal')
        @foreach($horariosPaginados as $horario)
            @include('admin.horario.modales.inactivarModal', ['horario' => $horario])
        @endforeach
    </div>

    <script>
        async function generarPDF(gradoId, seccionId) {
            try {
                const response = await fetch(`/admin/horario/${gradoId}/${seccionId}/show-api`);
                if (!response.ok) {
                    throw new Error('Error al obtener los datos del horario');
                }
                const data = await response.json();

                const dias = data.dias || [];
                const bloques = data.bloques || [];
                const asignaciones = data.asignaciones || [];
                const reservados = data.reservados || [];
                const gradoNombre = data.grado ? `${data.grado.numero_grado}° Grado` : 'Grado N/A';
                const seccionNombre = data.seccion ? `Sección "${data.seccion.nombre}"` : 'Sección N/A';
                const anioEscolar = data.anio_escolar || 'Año Escolar Activo';

                // Construir filas de la tabla
                let filasHtml = '';
                bloques.forEach(bloque => {
                    const numBloque = bloque.bloque || bloque.id;
                    const horas = (bloque.hora_inicio && bloque.hora_fin) ? `${bloque.hora_inicio} - ${bloque.hora_fin}` : `Bloque ${numBloque}`;

                    let celdasDias = '';
                    dias.forEach(dia => {
                        const numDia = dia.dia || dia.id;
                        const esReservado = reservados.some(r => Number(r.dia) === Number(numDia) && Number(r.bloque) === Number(numBloque));

                        if (esReservado) {
                            celdasDias += `<td style="background-color: #f1f5f9; color: #64748b; font-size: 11px; text-align: center; vertical-align: middle; font-weight: 600;">RESERVADO</td>`;
                        } else {
                            const asig = asignaciones.find(a => Number(a.dia) === Number(numDia) && Number(a.bloque) === Number(numBloque));
                            if (asig) {
                                const lock = asig.fijada ? ' 🔒' : '';
                                celdasDias += `
                                    <td style="padding: 6px; vertical-align: top; font-size: 11px; background-color: #f8fafc;">
                                        <div style="font-weight: bold; color: #1e293b; margin-bottom: 2px;">${asig.materia || 'Materia'}${lock}</div>
                                        <div style="color: #475569; font-size: 10px;">${asig.docente || 'Docente N/A'}</div>
                                        <div style="color: #64748b; font-size: 9px; margin-top: 2px;">📍 ${asig.aula || 'Aula regular'}</div>
                                    </td>
                                `;
                            } else {
                                celdasDias += `<td style="text-align: center; color: #cbd5e1; font-size: 12px; vertical-align: middle;">-</td>`;
                            }
                        }
                    });

                    filasHtml += `
                        <tr>
                            <td style="font-weight: 600; text-align: center; vertical-align: middle; background-color: #f8fafc; font-size: 11px; white-space: nowrap; padding: 6px 10px;">
                                Bloque ${numBloque}<br><span style="font-size: 9px; color: #64748b; font-weight: normal;">${horas}</span>
                            </td>
                            ${celdasDias}
                        </tr>
                    `;
                });

                let cabecerasDias = dias.map(d => `<th style="padding: 8px; text-align: center; background-color: #0f172a; color: #ffffff; font-size: 12px; font-weight: 600;">${d.nombre_dia || d.nombre}</th>`).join('');

                const printWindow = window.open('', '_blank', 'width=950,height=650');
                if (!printWindow) {
                    alert('Por favor, permita las ventanas emergentes en su navegador para imprimir el horario.');
                    return;
                }

                printWindow.document.write(`
                    <!DOCTYPE html>
                    <html lang="es">
                    <head>
                        <meta charset="UTF-8">
                        <title>Horario Escolar - ${gradoNombre} ${seccionNombre}</title>
                        <style>
                            @page { size: landscape; margin: 12mm; }
                            body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 20px; color: #1e293b; }
                            .header { text-align: center; border-bottom: 2px solid #0f172a; padding-bottom: 12px; margin-bottom: 16px; }
                            .header h1 { margin: 0 0 6px 0; font-size: 20px; text-transform: uppercase; letter-spacing: 0.5px; color: #0f172a; }
                            .header p { margin: 2px 0; font-size: 13px; color: #475569; }
                            table { width: 100%; border-collapse: collapse; margin-top: 10px; table-layout: fixed; }
                            th, td { border: 1px solid #cbd5e1; }
                            .footer { margin-top: 20px; font-size: 10px; color: #94a3b8; text-align: right; }
                            @media print {
                                body { padding: 0; }
                            }
                        </style>
                    </head>
                    <body>
                        <div class="header">
                            <h1>Horario Escolar Oficial</h1>
                            <p><strong>${gradoNombre} - ${seccionNombre}</strong> &nbsp;|&nbsp; <strong>Año Escolar:</strong> ${anioEscolar}</p>
                        </div>
                        <table>
                            <thead>
                                <tr>
                                    <th style="padding: 8px; width: 110px; text-align: center; background-color: #0f172a; color: #ffffff; font-size: 12px;">Hora / Bloque</th>
                                    ${cabecerasDias}
                                </tr>
                            </thead>
                            <tbody>
                                ${filasHtml}
                            </tbody>
                        </table>
                        <div class="footer">
                            Generado el ${new Date().toLocaleDateString('es-VE', { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' })}
                        </div>
                        <script>
                            window.onload = function() {
                                window.print();
                            };
                        <\/script>
                    </body>
                    </html>
                `);
                printWindow.document.close();
            } catch (err) {
                console.error('Error al generar PDF:', err);
                alert('No se pudo generar el reporte del horario.');
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            const collapseErrores = document.getElementById('collapseErrores');
            const textoToggle = document.getElementById('textoToggleErrores');
            const iconoToggle = document.getElementById('iconoToggleErrores');

            if (collapseErrores && textoToggle && iconoToggle) {
                const totalItems = collapseErrores.querySelectorAll('li').length;
                collapseErrores.addEventListener('show.bs.collapse', function () {
                    textoToggle.textContent = 'Ocultar detalles de los errores';
                    iconoToggle.className = 'fas fa-eye-slash';
                });
                collapseErrores.addEventListener('hide.bs.collapse', function () {
                    textoToggle.textContent = 'Ver detalles de los errores (' + totalItems + ')';
                    iconoToggle.className = 'fas fa-eye';
                });
            }
        });
    </script>
@endsection

