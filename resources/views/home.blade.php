@extends('adminlte::page')

@section('title', 'Dashboard - Sistema de Inscripción')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/design-system.css') }}?v={{ @filemtime(public_path('css/design-system.css')) }}">
    <style>
        .dashboard-container-compact {
            padding: 1rem 1.25rem;
            background: #F8FAFC;
            min-height: calc(100vh - 120px);
        }

        /* TAREA 1: Hero Banner Elegante y Suave */
        .welcome-hero-banner {
            background: #4A6FC1;
            border-radius: 12px;
            box-shadow: 0 4px 12px -2px rgba(74, 111, 193, 0.22);
            color: #FFFFFF;
            padding: 0.85rem 1.4rem;
            margin-bottom: 1rem;
        }

        .welcome-icon-box {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.18);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        .welcome-title {
            font-size: 1.35rem; /* ~22px-24px */
            font-weight: 700;
            line-height: 1.2;
            color: #FFFFFF;
            margin: 0;
        }

        .welcome-subtitle {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.9);
            margin: 0.15rem 0 0 0;
        }

        /* TAREA 2: Grid de 5 Tarjetas KPI en una sola hilera */
        .kpi-grid-5 {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }

        @media (max-width: 1200px) {
            .kpi-grid-5 {
                grid-template-columns: repeat(3, 1fr) !important;
                gap: 12px;
                margin-bottom: 16px;
            }
        }

        @media (max-width: 992px) {
            .kpi-grid-5 {
                grid-template-columns: repeat(2, 1fr) !important;
                gap: 12px;
                margin-bottom: 16px;
            }
        }

        @media (max-width: 576px) {
            .kpi-grid-5 {
                grid-template-columns: 1fr !important;
                gap: 10px;
                margin-bottom: 14px;
            }
        }

        .kpi-card-compact {
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 10px;
            padding: 12px 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            transition: all 0.2s ease;
            min-width: 0;
        }

        .kpi-card-compact:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 15px -3px rgba(0, 0, 0, 0.08);
            border-color: #CBD5E1;
        }

        .kpi-dot {
            width: 7px;
            height: 7px;
            min-width: 7px;
            border-radius: 50%;
            display: inline-block;
            flex-shrink: 0;
        }

        .kpi-label {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748B;
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: block;
        }

        .kpi-number {
            font-size: 24px;
            font-weight: 700;
            color: #1E293B;
            line-height: 1.1;
        }

        .kpi-icon-circle {
            width: 32px;
            height: 32px;
            min-width: 32px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.88rem;
            flex-shrink: 0;
            border: none !important;
        }

        .kpi-icon-blue { background-color: #EFF6FF; color: #3B82F6; }
        .kpi-icon-green { background-color: #ECFDF5; color: #10B981; }
        .kpi-icon-purple { background-color: #F5F3FF; color: #8B5CF6; }
        .kpi-icon-amber { background-color: #FFFBEB; color: #F59E0B; }
        .kpi-icon-teal { background-color: #F0FDFA; color: #14B8A6; }
        .kpi-icon-pink { background-color: #FEF2F2; color: #EF4444; }

        /* TAREA 4 y 5: Botones Compactos de Acciones y Configuración */
        .compact-action-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 10px;
        }

        .compact-config-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 10px;
        }

        .compact-action-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 12px;
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 8px;
            text-decoration: none !important;
            color: #1E293B !important;
            transition: all 0.2s ease;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.02);
            min-height: 46px;
        }

        .compact-action-btn:hover {
            background-color: #EFF6FF !important;
            border-color: #4A6FC1 !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(74, 111, 193, 0.1);
            color: #1E293B !important;
        }

        .compact-action-icon {
            width: 30px;
            height: 30px;
            min-width: 30px;
            border-radius: 6px;
            background: #EFF6FF;
            color: #4A6FC1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            flex-shrink: 0;
            transition: all 0.2s ease;
        }

        .compact-action-btn:hover .compact-action-icon {
            background: #4A6FC1;
            color: #FFFFFF;
        }

        .compact-action-text {
            font-size: 13px !important;
            font-weight: 500;
            color: #1E293B;
            white-space: normal !important;
            word-break: break-word;
            line-height: 1.25;
            margin: 0;
        }

        /* TAREA 6: Tipografía General */
        .section-card-title {
            color: #1E293B;
            font-weight: 600;
            font-size: 15px;
            margin: 0;
        }

        .section-card-subtitle {
            color: #64748B;
            font-size: 13px;
            margin: 0;
        }
    </style>
@stop

@section('content_header')
@stop

@section('content')
    <div class="dashboard-container-compact">
        
        {{-- TAREA 1: Hero Banner Limpio (Sin recuadro blanco y con azul sólido elegante) --}}
        <div class="welcome-hero-banner d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
                <div class="welcome-icon-box">
                    <i class="fas fa-school text-white"></i>
                </div>
                <div>
                    <h1 class="welcome-title">¡Bienvenido, {{ Auth::user()->name }}! 👋</h1>
                    <p class="welcome-subtitle">Sistema de Gestión Escolar • {{ now()->translatedFormat('l, d \d\e F \d\e Y') }}</p>
                </div>
            </div>
        </div>

        {{-- TAREA 2: Grid de 5 Tarjetas KPI en una sola hilera horizontal --}}
        <div class="kpi-grid-5" style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 16px; margin-bottom: 24px;">
            {{-- Tarjeta 1: Nuevos Ingresos --}}
            <div class="kpi-card-compact">
                <div style="min-width: 0; flex: 1;" class="me-2">
                    <div class="d-flex align-items-center mb-1" style="gap: 6px;">
                        <span class="kpi-dot" style="background-color: #4A6FC1;"></span>
                        <span class="kpi-label">Nuevos Ingresos</span>
                    </div>
                    <div class="kpi-number">{{ $totalNuevoIngreso ?? 0 }}</div>
                </div>
                <div class="kpi-icon-circle kpi-icon-blue">
                    <i class="fas fa-user-plus"></i>
                </div>
            </div>

            {{-- Tarjeta 2: Prosecuciones --}}
            <div class="kpi-card-compact">
                <div style="min-width: 0; flex: 1;" class="me-2">
                    <div class="d-flex align-items-center mb-1" style="gap: 6px;">
                        <span class="kpi-dot" style="background-color: #10B981;"></span>
                        <span class="kpi-label">Prosecuciones</span>
                    </div>
                    <div class="kpi-number">{{ $totalProsecucion ?? 0 }}</div>
                </div>
                <div class="kpi-icon-circle kpi-icon-green">
                    <i class="fas fa-user-graduate"></i>
                </div>
            </div>

            {{-- Tarjeta 3: Total Docentes --}}
            <div class="kpi-card-compact">
                <div style="min-width: 0; flex: 1;" class="me-2">
                    <div class="d-flex align-items-center mb-1" style="gap: 6px;">
                        <span class="kpi-dot" style="background-color: #8B5CF6;"></span>
                        <span class="kpi-label">Total Docentes</span>
                    </div>
                    <div class="kpi-number">{{ $totalDocentes ?? 0 }}</div>
                </div>
                <div class="kpi-icon-circle kpi-icon-purple">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
            </div>

            {{-- Tarjeta 4: Años Activos --}}
            <div class="kpi-card-compact">
                <div style="min-width: 0; flex: 1;" class="me-2">
                    <div class="d-flex align-items-center mb-1" style="gap: 6px;">
                        <span class="kpi-dot" style="background-color: #F59E0B;"></span>
                        <span class="kpi-label">Años Activos</span>
                    </div>
                    <div class="kpi-number">{{ $totalGrados ?? 0 }}</div>
                </div>
                <div class="kpi-icon-circle kpi-icon-amber">
                    <i class="fas fa-school"></i>
                </div>
            </div>

            {{-- Tarjeta 5: Calendario Escolar --}}
            <div class="kpi-card-compact">
                <div style="min-width: 0; flex: 1;" class="me-2">
                    <div class="d-flex align-items-center mb-1" style="gap: 6px;">
                        <span class="kpi-dot" style="background-color: #14B8A6;"></span>
                        <span class="kpi-label">Calendario Escolar</span>
                    </div>
                    <div class="kpi-number" style="font-size: 19px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $anioEscolarActivo ?? 'N/A' }}</div>
                </div>
                <div class="kpi-icon-circle kpi-icon-teal">
                    <i class="fas fa-calendar-check"></i>
                </div>
            </div>
        </div>

        {{-- TAREA 5: Layout en 2 Columnas (8 y 4) --}}
        <div class="row g-3">
            
            {{-- COLUMNA IZQUIERDA (ANCHO 8): Gráfico + Acciones Rápidas --}}
            <div class="col-lg-8 d-flex flex-column gap-3">
                
                {{-- TAREA 3: Gráfico de Estudiantes por Sección Compacto (<250px, sin bordes negros ni rejillas) --}}
                <div class="card-modern m-0 shadow-sm" style="border: 1px solid #E2E8F0;">
                    <div class="card-header-modern p-2 px-3 d-flex align-items-center justify-content-between" style="border-bottom: 1px solid #F1F5F9;">
                        <div class="d-flex align-items-center gap-2">
                            <div class="header-icon header-icon-pastel-blue" style="width: 32px; height: 32px; font-size: 0.88rem;">
                                <i class="fas fa-chart-bar"></i>
                            </div>
                            <div>
                                <h3 class="section-card-title">Estudiantes por Sección</h3>
                                <p class="section-card-subtitle">Distribución actual de matrícula</p>
                            </div>
                        </div>
                    </div>
                    <div class="card-body-modern p-3" style="height: 220px; max-height: 230px;">
                        <canvas id="chartSecciones"></canvas>
                    </div>
                </div>

                {{-- TAREA 5A: Acciones Rápidas (Grid Compacto de Tarjetas) --}}
                <div class="card-modern m-0 shadow-sm" style="border: 1px solid #E2E8F0;">
                    <div class="card-header-modern p-2 px-3 d-flex align-items-center justify-content-between" style="border-bottom: 1px solid #F1F5F9;">
                        <div class="d-flex align-items-center gap-2">
                            <div class="header-icon header-icon-pastel-blue" style="width: 32px; height: 32px; font-size: 0.88rem;">
                                <i class="fas fa-bolt"></i>
                            </div>
                            <div>
                                <h3 class="section-card-title">Acciones Rápidas</h3>
                                <p class="section-card-subtitle">Accesos directos a operaciones frecuentes</p>
                            </div>
                        </div>
                    </div>
                    <div class="card-body-modern p-3">
                        <div class="compact-action-grid">
                            <a href="{{ url('admin/transacciones/inscripcion') }}" class="compact-action-btn">
                                <div class="compact-action-icon">
                                    <i class="fas fa-user-plus"></i>
                                </div>
                                <span class="compact-action-text">Nuevo Ingreso</span>
                            </a>

                            <a href="{{ url('admin/transacciones/inscripcion_prosecucion') }}" class="compact-action-btn">
                                <div class="compact-action-icon">
                                    <i class="fas fa-user-graduate"></i>
                                </div>
                                <span class="compact-action-text">Nueva Prosecución</span>
                            </a>

                            <a href="{{ url('representante') }}" class="compact-action-btn">
                                <div class="compact-action-icon">
                                    <i class="fas fa-user-tie"></i>
                                </div>
                                <span class="compact-action-text">Nuevo Representante</span>
                            </a>

                            <a href="{{ route('admin.docente.create') }}" class="compact-action-btn">
                                <div class="compact-action-icon">
                                    <i class="fas fa-chalkboard-teacher"></i>
                                </div>
                                <span class="compact-action-text">Nuevo Docente</span>
                            </a>

                            <a href="{{ route('admin.anio_escolar.index') }}" class="compact-action-btn">
                                <div class="compact-action-icon">
                                    <i class="fas fa-calendar-check"></i>
                                </div>
                                <span class="compact-action-text">Calendario Escolar</span>
                            </a>

                            <a href="{{ route('admin.historico.index') }}" class="compact-action-btn">
                                <div class="compact-action-icon">
                                    <i class="fas fa-history"></i>
                                </div>
                                <span class="compact-action-text">Histórico</span>
                            </a>

                            <a href="{{ route('admin.acta.create') }}" class="compact-action-btn">
                                <div class="compact-action-icon">
                                    <i class="fas fa-file-signature"></i>
                                </div>
                                <span class="compact-action-text">Acta de Entrevista</span>
                            </a>
                        </div>
                    </div>
                </div>

            </div>

            {{-- COLUMNA DERECHA (ANCHO 4): Configuración Académica + Info Sistema --}}
            <div class="col-lg-4 d-flex flex-column gap-3">
                
                {{-- TAREA 4: Configuración Académica (Texto completo de 13px sin cortes) --}}
                <div class="card-modern m-0 shadow-sm" style="border: 1px solid #E2E8F0;">
                    <div class="card-header-modern p-2 px-3 d-flex align-items-center justify-content-between" style="border-bottom: 1px solid #F1F5F9;">
                        <div class="d-flex align-items-center gap-2">
                            <div class="header-icon header-icon-pastel-blue" style="width: 32px; height: 32px; font-size: 0.88rem;">
                                <i class="fas fa-cogs"></i>
                            </div>
                            <div>
                                <h3 class="section-card-title">Configuración Académica</h3>
                                <p class="section-card-subtitle">Estructura y gestión del plantel</p>
                            </div>
                        </div>
                    </div>
                    <div class="card-body-modern p-3">
                        <div class="compact-config-grid">
                            <a href="{{ route('admin.grado.index') }}" class="compact-action-btn">
                                <div class="compact-action-icon">
                                    <i class="fas fa-layer-group"></i>
                                </div>
                                <span class="compact-action-text">Niveles Académicos</span>
                            </a>

                            <a href="{{ route('admin.bloque_horario.index') }}" class="compact-action-btn">
                                <div class="compact-action-icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <span class="compact-action-text">Bloques de Horario</span>
                            </a>

                            <a href="{{ route('admin.area_formacion.index') }}" class="compact-action-btn">
                                <div class="compact-action-icon">
                                    <i class="fas fa-book-open"></i>
                                </div>
                                <span class="compact-action-text">Áreas de Formación</span>
                            </a>

                            <a href="{{ route('admin.transacciones.grado_area_formacion.index') }}" class="compact-action-btn">
                                <div class="compact-action-icon">
                                    <i class="fas fa-university"></i>
                                </div>
                                <span class="compact-action-text">Grado - Materia</span>
                            </a>

                            <a href="{{ route('aulas.index') }}" class="compact-action-btn">
                                <div class="compact-action-icon">
                                    <i class="fas fa-door-open"></i>
                                </div>
                                <span class="compact-action-text">Aulas y Espacios</span>
                            </a>
                        </div>
                    </div>
                </div>

                {{-- TAREA 5B: Información del Sistema (Lista Limpia y Minimalista) --}}
                <div class="card-modern m-0 shadow-sm" style="border: 1px solid #E2E8F0;">
                    <div class="card-header-modern p-2 px-3 d-flex align-items-center justify-content-between" style="border-bottom: 1px solid #F1F5F9;">
                        <div class="d-flex align-items-center gap-2">
                            <div class="header-icon header-icon-pastel-blue" style="width: 32px; height: 32px; font-size: 0.88rem;">
                                <i class="fas fa-server"></i>
                            </div>
                            <div>
                                <h3 class="section-card-title">Información del Sistema</h3>
                                <p class="section-card-subtitle">Estado operativo y conectividad</p>
                            </div>
                        </div>
                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill" style="font-size: 0.68rem; padding: 0.22rem 0.55rem;">
                            <i class="fas fa-circle me-1" style="font-size: 0.42rem;"></i> En Línea
                        </span>
                    </div>
                    <div class="card-body-modern p-3">
                        <ul class="list-unstyled m-0 d-flex flex-column">
                            <li class="d-flex align-items-center justify-content-between py-2 border-bottom" style="border-color: #F1F5F9 !important;">
                                <span class="text-muted" style="font-size: 13px;">Versión del Sistema</span>
                                <span class="fw-semibold text-dark" style="font-size: 13px;">v1.0.0</span>
                            </li>
                            <li class="d-flex align-items-center justify-content-between py-2 border-bottom" style="border-color: #F1F5F9 !important;">
                                <span class="text-muted" style="font-size: 13px;">Base de Datos</span>
                                <span class="fw-semibold text-dark font-monospace" style="font-size: 12px;">MySQL {{ DB::connection()->getPdo()->getAttribute(PDO::ATTR_SERVER_VERSION) }}</span>
                            </li>
                            <li class="d-flex align-items-center justify-content-between py-2 border-bottom" style="border-color: #F1F5F9 !important;">
                                <span class="text-muted" style="font-size: 13px;">Usuarios Registrados</span>
                                <span class="fw-bold text-dark" style="font-size: 13px;">{{ $totalUsuarios ?? 0 }}</span>
                            </li>
                            <li class="d-flex align-items-center justify-content-between py-2" style="font-size: 13px;">
                                <span class="text-muted">Última Sincronización</span>
                                <span class="text-dark" style="font-size: 12px;">{{ now()->format('d/m/Y H:i') }}</span>
                            </li>
                        </ul>
                    </div>
                </div>

            </div>

        </div>

    </div>
@stop

@section('js')
    @if (!isset($chartJsLoaded))
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    @endif
    <script>
        // Datos de estudiantes por sección
        const seccionesData = {
            labels: ['Sección A', 'Sección B', 'Sección C', 'Sección D', 'Sección F'],
            estudiantes: [32, 28, 35, 24, 19]
        };

        const ctxSecciones = document.getElementById('chartSecciones');

        if (ctxSecciones) {
            new Chart(ctxSecciones, {
                type: 'bar',
                data: {
                    labels: seccionesData.labels,
                    datasets: [{
                        label: 'Estudiantes',
                        data: seccionesData.estudiantes,
                        // Paleta Pastel del Design System (Sin bordes negros)
                        backgroundColor: [
                            'rgba(199, 210, 254, 0.95)', // Pastel Blue
                            'rgba(167, 243, 208, 0.95)', // Pastel Green
                            'rgba(221, 214, 254, 0.95)', // Pastel Purple
                            'rgba(253, 230, 138, 0.95)', // Pastel Yellow
                            'rgba(153, 246, 228, 0.95)'  // Pastel Teal
                        ],
                        borderWidth: 0,
                        borderRadius: 6,
                        borderSkipped: false,
                        maxBarThickness: 45
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: '#0F172A',
                            titleColor: '#FFFFFF',
                            bodyColor: '#F8FAFC',
                            padding: 10,
                            cornerRadius: 8,
                            displayColors: false,
                            titleFont: {
                                size: 12,
                                weight: '600'
                            },
                            bodyFont: {
                                size: 12
                            },
                            callbacks: {
                                label: function(context) {
                                    return context.parsed.y + ' estudiantes';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                display: false,
                                drawBorder: false
                            },
                            border: {
                                display: false
                            },
                            ticks: {
                                stepSize: 10,
                                font: {
                                    size: 11
                                },
                                color: '#94A3B8'
                            }
                        },
                        x: {
                            grid: {
                                display: false,
                                drawBorder: false
                            },
                            border: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    size: 11
                                },
                                color: '#64748B'
                            }
                        }
                    }
                }
            });
        }
    </script>
@stop
