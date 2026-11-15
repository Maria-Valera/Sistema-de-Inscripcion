@extends('adminlte::page')

@section('title', 'Configuración de Bloques Horarios')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modal-styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/horario-styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/design-system.css') }}?v={{ @filemtime(public_path('css/design-system.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/horario-pilot.css') }}?v={{ @filemtime(public_path('css/horario-pilot.css')) }}">
    <style>
        body, .content-wrapper, .wrapper {
            background-color: #F9FAFB !important;
        }
        .content-header {
            background: transparent !important;
        }
        .config-card {
            background: #FFFFFF !important;
            border-radius: 12px !important;
            border: 1px solid #E5E7EB !important;
            box-shadow: none !important;
            transition: all 0.2s ease;
            min-width: 0;
        }
        .config-card-header {
            padding: 18px 24px !important;
            border-bottom: 1px solid #F3F4F6 !important;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 0.5rem;
            background: #FFFFFF !important;
            border-top-left-radius: 12px !important;
            border-top-right-radius: 12px !important;
            min-height: 58px;
        }
        .config-card-body {
            padding: 24px !important;
            min-width: 0;
            overflow-x: hidden;
        }
        .section-title {
            font-size: 1.05rem !important;
            font-weight: 600 !important;
            color: #111827 !important;
            margin-bottom: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .form-label-modern {
            font-size: 14px !important;
            font-weight: 500 !important;
            color: #374151 !important;
            margin-bottom: 6px !important;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }
        .form-control-modern {
            border-radius: 8px !important;
            border: 1px solid #E5E7EB !important;
            padding: 0.55rem 0.85rem !important;
            font-size: 0.92rem !important;
            height: 42px !important;
            background-color: #FFFFFF !important;
            color: #111827 !important;
            box-shadow: none !important;
            transition: border-color 0.15s ease-in-out;
        }
        .form-control-modern:focus {
            border-color: #4A6FC1 !important;
            box-shadow: none !important;
            outline: none !important;
        }
        .timeline-block-item {
            background: #FFFFFF !important;
            border: 1px solid #E5E7EB !important;
            border-left: 4px solid #4A6FC1 !important;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05) !important;
            border-radius: 8px !important;
            padding: 10px 14px !important;
            margin-bottom: 8px !important;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: all 0.15s ease;
        }
        .timeline-block-item:hover {
            background: #FFFFFF !important;
            border-color: #D1D5DB !important;
            border-left-color: #3B5BA5 !important;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.08) !important;
            transform: translateX(2px);
        }
        .timeline-receso-item {
            background: #F9FAFB !important;
            border: 1px dashed #E5E7EB !important;
            border-radius: 8px !important;
            padding: 8px 12px !important;
            margin-bottom: 8px !important;
            color: #4B5563 !important;
            font-size: 0.82rem !important;
            font-weight: 500 !important;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .timeline-receso-item i {
            color: #6B7280 !important;
        }
        .receso-row,
        .reservado-row {
            background: #F9FAFB !important;
            border: 1px solid #E5E7EB !important;
            border-radius: 8px !important;
            padding: 10px 12px !important;
            box-shadow: none !important;
        }
        #btnAgregarPrimerReceso:hover {
            background-color: #FFF7ED !important;
            border-color: #FDBA74 !important;
            color: #C2410C !important;
        }
        #btnAgregarPrimerReservado:hover {
            background-color: #F5F3FF !important;
            border-color: #C4B5FD !important;
            color: #4F46E5 !important;
        }
        .btn-eliminar-receso:hover,
        .btn-eliminar-reservado:hover {
            background-color: #FEF2F2 !important;
            border-color: #F87171 !important;
            color: #DC2626 !important;
        }
        select.form-select option {
            background-color: #FFFFFF !important;
            color: #1E293B !important;
            font-size: 14px;
            padding: 8px;
        }
        select.form-select.text-center {
            text-align: center;
            text-align-last: center;
        }
        .badge-kpi {
            background: #F3F4F6 !important;
            color: #374151 !important;
            font-weight: 500 !important;
            padding: 6px 10px !important;
            border-radius: 8px !important;
            font-size: 0.78rem !important;
            border: 1px solid #E5E7EB !important;
        }

        /* Pestañas SaaS Estilo Segmented Control */
        .config-tabs-wrapper {
            background: #F3F4F6 !important;
            border: 1px solid #E5E7EB !important;
            border-radius: 12px !important;
            padding: 4px !important;
            display: inline-flex !important;
            flex-wrap: wrap !important;
            gap: 4px !important;
        }
        .config-tabs-wrapper .nav-link {
            border: none !important;
            border-radius: 8px !important;
            padding: 0.55rem 1.15rem !important;
            font-size: 0.9rem !important;
            font-weight: 500 !important;
            color: #6B7280 !important;
            background: transparent !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 0.5rem !important;
            transition: all 0.18s ease !important;
        }
        .config-tabs-wrapper .nav-link:hover:not(.active) {
            background: rgba(255, 255, 255, 0.7) !important;
            color: #111827 !important;
        }
        .config-tabs-wrapper .nav-link.active {
            background-color: #FFFFFF !important;
            color: #111827 !important;
            font-weight: 600 !important;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05) !important;
            border: 1px solid #E5E7EB !important;
        }
        .config-tabs-wrapper .nav-link.active i {
            color: #4A6FC1 !important;
        }

        /* Estilos de Posiciones por Materia */
        .posicion-card {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            border: 1px solid #e5e7eb;
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
            border-color: #4A6FC1;
            box-shadow: 0 0 0 3px rgba(74, 111, 193, 0.18);
            outline: none;
        }
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
    <div class="content-header-modern hero-header-horario">
        <div class="header-content">
            <div class="header-title">
                <div class="icon-wrapper">
                    <i class="fas fa-sliders-h"></i>
                </div>
                <div>
                    <h1 class="title-main">Configuración de Bloques Horarios</h1>
                    <p class="title-subtitle">
                        Marco horario institucional, recesos y posiciones pedagógicas &bull;
                        <strong>Configuración Global</strong>
                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('admin.horario.index') }}" class="btn-hero-white">
                    <i class="fas fa-arrow-left"></i>
                    <span>Volver al Horario</span>
                </a>
            </div>
        </div>
    </div>
@stop

@section('content')
    @php
        $tabActual = $activeTab ?? request('tab', 'bloques');
    @endphp
    <div class="container-fluid px-0">
        {{-- Navegación por Pestañas (Tabs) Estilo Segmented Control --}}
        <div class="mb-4">
            <ul class="nav nav-pills config-tabs-wrapper" id="configHorarioTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $tabActual === 'bloques' ? 'active' : '' }}"
                            id="tab-bloques-btn"
                            data-bs-toggle="tab"
                            data-bs-target="#pane-bloques"
                            data-tab-key="bloques"
                            type="button"
                            role="tab"
                            aria-controls="pane-bloques"
                            aria-selected="{{ $tabActual === 'bloques' ? 'true' : 'false' }}">
                        <i class="fas fa-clock"></i>
                        <span>Bloques y Recesos</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $tabActual === 'posiciones' ? 'active' : '' }}"
                            id="tab-posiciones-btn"
                            data-bs-toggle="tab"
                            data-bs-target="#pane-posiciones"
                            data-tab-key="posiciones"
                            type="button"
                            role="tab"
                            aria-controls="pane-posiciones"
                            aria-selected="{{ $tabActual === 'posiciones' ? 'true' : 'false' }}">
                        <i class="fas fa-th"></i>
                        <span>Posiciones por Materia</span>
                    </button>
                </li>
            </ul>
        </div>

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

        <div class="tab-content" id="configHorarioTabsContent">
            {{-- PESTAÑA 1: BLOQUES Y RECESOS --}}
            <div class="tab-pane fade {{ $tabActual === 'bloques' ? 'show active' : '' }}"
                 id="pane-bloques"
                 role="tabpanel"
                 aria-labelledby="tab-bloques-btn">

                {{-- Formulario Unificado Vertical --}}
                <form action="{{ route('admin.horario.configuracion.update') }}" method="POST" id="configHorarioForm">
            @csrf
            @method('PUT')

            <div class="row g-4">
                {{-- Columna Izquierda (Configuración): col-md-7 col-lg-8 --}}
                <div class="col-md-7 col-lg-8">
                    {{-- Fila 1: Parámetros de la Jornada (Horizontal, col-md-12) --}}
                    <div class="card config-card mb-4">
                        <div class="card-header config-card-header d-flex justify-content-between align-items-center flex-nowrap">
                            <div>
                                <h2 class="section-title">
                                    <i class="fas fa-clock" style="color: #4A6FC1;"></i>
                                    <span>Parámetros de la Jornada</span>
                                </h2>
                                <small class="text-muted" style="font-size: 0.8rem;">Define el inicio y cantidad de bloques diarios</small>
                            </div>
                            <span class="badge rounded-pill ms-auto" style="background: #F3F4F6; color: #6B7280; border: none; font-weight: 500; font-size: 0.75rem; padding: 5px 12px; display: inline-flex; align-items: center; letter-spacing: 0.2px;">
                                <i class="fas fa-globe me-1" style="font-size: 0.7rem; color: #6B7280;"></i> Global
                            </span>
                        </div>
                        <div class="card-body config-card-body">
                            <div class="row mb-4">
                                <!-- 1. Hora de Inicio -->
                                <div class="col-lg-3 col-md-6 mb-3 mb-lg-0" style="min-width: 0;">
                                    <label for="hora_inicio" class="form-label text-muted fw-bold d-flex align-items-center gap-1">
                                        <i class="far fa-clock" style="color: #4A6FC1; font-size: 0.82rem;"></i>
                                        <span>Hora de Inicio</span>
                                    </label>
                                    <input type="time" 
                                           class="form-control form-control-modern @error('hora_inicio') is-invalid @enderror" 
                                           id="hora_inicio" 
                                           name="hora_inicio" 
                                           value="{{ old('hora_inicio', substr($configuracion->hora_inicio ?? '07:00:00', 0, 5)) }}" 
                                           required>
                                    <small class="d-block mt-1" style="color: #9CA3AF !important; font-size: 12px;">Inicio 1er bloque.</small>
                                </div>

                                <!-- 2. Número de Bloques -->
                                <div class="col-lg-3 col-md-6 mb-3 mb-lg-0" style="min-width: 0;">
                                    <label for="numero_bloques" class="form-label text-muted fw-bold d-flex align-items-center gap-1">
                                        <i class="fas fa-th" style="color: #4A6FC1; font-size: 0.78rem;"></i>
                                        <span>Número Bloques</span>
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
                                        <small class="d-inline-flex align-items-center gap-1 mt-1 px-2 py-0.5 rounded" style="background-color: #EFF6FF; color: #1E3A8A; font-size: 11.5px; font-weight: 600; border: 1px solid #DBEAFE;">
                                            <i class="fas fa-info-circle" style="color: #4A6FC1;"></i> Mínimo: <strong>{{ $maxBloqueOcupado }}</strong> (con datos)
                                        </small>
                                    @else
                                        <small class="d-block mt-1" style="color: #9CA3AF !important; font-size: 12px;">Cantidad diaria.</small>
                                    @endif
                                </div>

                                <!-- 3. Duración Bloque -->
                                <div class="col-lg-3 col-md-6 mb-3 mb-lg-0" style="min-width: 0;">
                                    <label for="duracion_bloque_min" class="form-label text-muted fw-bold d-flex align-items-center gap-1">
                                        <i class="fas fa-hourglass-half" style="color: #4A6FC1; font-size: 0.76rem;"></i>
                                        <span>Duración Bloque</span>
                                    </label>
                                    <div class="input-group flex-nowrap" style="min-width: 0;">
                                        <input type="number" 
                                               class="form-control form-control-modern @error('duracion_bloque_min') is-invalid @enderror" 
                                               id="duracion_bloque_min" 
                                               name="duracion_bloque_min" 
                                               min="15" 
                                               max="240" 
                                               step="5"
                                               value="{{ old('duracion_bloque_min', $configuracion->duracion_bloque_min ?? 90) }}" 
                                               style="border-radius: 8px 0 0 8px !important; min-width: 0;"
                                               required>
                                        <span class="input-group-text bg-white text-muted border-start-0" style="border: 1px solid #E5E7EB; border-left: none; border-radius: 0 8px 8px 0; font-size: 0.82rem;">min</span>
                                    </div>
                                    <small class="d-block mt-1" style="color: #9CA3AF !important; font-size: 12px;">Estándar: 90 min.</small>
                                </div>

                                <!-- 4. Pausa Bloques -->
                                <div class="col-lg-3 col-md-6 mb-3 mb-lg-0" style="min-width: 0;">
                                    <label for="minutos_entre_bloques" class="form-label text-muted fw-bold d-flex align-items-center gap-1">
                                        <i class="fas fa-pause" style="color: #4A6FC1; font-size: 0.72rem;"></i>
                                        <span>Pausa Bloques</span>
                                    </label>
                                    <div class="input-group flex-nowrap" style="min-width: 0;">
                                        <input type="number" 
                                               class="form-control form-control-modern @error('minutos_entre_bloques') is-invalid @enderror" 
                                               id="minutos_entre_bloques" 
                                               name="minutos_entre_bloques" 
                                               min="0" 
                                               max="60" 
                                               value="{{ old('minutos_entre_bloques', $configuracion->minutos_entre_bloques ?? 0) }}" 
                                               style="border-radius: 8px 0 0 8px !important; min-width: 0;"
                                               required>
                                        <span class="input-group-text bg-white text-muted border-start-0" style="border: 1px solid #E5E7EB; border-left: none; border-radius: 0 8px 8px 0; font-size: 0.82rem;">min</span>
                                    </div>
                                    <small class="d-block mt-1" style="color: #9CA3AF !important; font-size: 12px;">Pausa estándar (ej. 0).</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Fila 2: Recesos y Bloqueos (Lado a Lado Simétricos) --}}
                    <div class="row mb-4">
                        <!-- Tarjeta 1: Recesos -->
                        <div class="col-md-6" style="min-width: 0;">
                            <div class="card config-card h-100">
                                <div class="card-header config-card-header d-flex align-items-center justify-content-between">
                                    <h2 class="section-title">
                                        <i class="fas fa-coffee" style="color: #EA580C;"></i>
                                        <span>Descansos y Recesos</span>
                                    </h2>
                                    <button type="button" class="btn-secondary-modern btn-sm d-inline-flex align-items-center gap-1" id="btnAgregarReceso">
                                        <i class="fas fa-plus"></i>
                                        <span>+ Receso</span>
                                    </button>
                                </div>
                                <div class="card-body config-card-body d-flex flex-column flex-grow-1">
                                    <p class="text-muted small mb-3" style="font-size: 0.82rem; min-height: 2.4rem;">
                                        Pausas dentro de la jornada (ej. recreo institucional). Desplazan las horas de los bloques siguientes.
                                    </p>

                                    <div id="recesosContainer">
                                        {{-- Filas dinámicas generadas con JS --}}
                                    </div>

                                    {{-- Feedback visual de errores en Recesos --}}
                                    <div id="errorRecesosFeedback" class="alert alert-danger py-2 px-3 small mt-2 d-none" style="border-radius: 8px; font-size: 0.82rem;">
                                        <i class="fas fa-exclamation-circle me-1"></i>
                                        <span class="error-text"></span>
                                    </div>
                                    @if ($errors->has('recesos*') || $errors->has('recesos'))
                                        <div class="alert alert-danger py-2 px-3 small mt-2" style="border-radius: 8px; font-size: 0.82rem;">
                                            <i class="fas fa-exclamation-circle me-1"></i>
                                            <span>{{ $errors->first('recesos.*.despues_de_bloque') ?: ($errors->first('recesos.*') ?: $errors->first('recesos')) }}</span>
                                        </div>
                                    @endif

                                    @php
                                        $recesosInit = old('recesos', $recesosArray ?? []);
                                        $tieneRecesos = is_array($recesosInit) && count($recesosInit) > 0;
                                    @endphp
                                    <div id="sinRecesosAlert" class="text-center py-3 px-2 mt-auto {{ $tieneRecesos ? 'd-none' : 'd-flex' }} flex-column align-items-center justify-content-center">
                                        <div class="d-inline-flex align-items-center justify-content-center mb-2"
                                             style="width: 44px; height: 44px; border-radius: 10px; background-color: #FFF7ED; color: #EA580C; border: 1px solid #FFEDD5;">
                                            <i class="fas fa-coffee" style="font-size: 18px; color: #EA580C;"></i>
                                        </div>
                                        <p class="small mb-2" style="font-size: 0.82rem; color: #6B7280 !important; font-weight: 400;">No hay recesos configurados.</p>
                                        <div class="mt-auto">
                                            <button type="button" class="btn-sm d-inline-flex align-items-center gap-1" id="btnAgregarPrimerReceso"
                                                    style="background-color: #FFFFFF; border: 1px solid #FED7AA; color: #EA580C; border-radius: 8px; font-weight: 500; font-size: 0.85rem; padding: 6px 14px; transition: all 0.15s ease;">
                                                <i class="fas fa-plus" style="color: #EA580C;"></i>
                                                <span>+ Agregar primer receso</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tarjeta 2: Bloqueos -->
                        <div class="col-md-6" style="min-width: 0;">
                            <div class="card config-card h-100">
                                <div class="card-header config-card-header d-flex align-items-center justify-content-between">
                                    <h2 class="section-title">
                                        <i class="fas fa-lock" style="color: #6366F1;"></i>
                                        <span>Bloques Reservados</span>
                                    </h2>
                                    <button type="button" class="btn-secondary-modern btn-sm d-inline-flex align-items-center gap-1" id="btnAgregarReservado">
                                        <i class="fas fa-plus"></i>
                                        <span>+ Bloque</span>
                                    </button>
                                </div>
                                <div class="card-body config-card-body d-flex flex-column flex-grow-1">
                                    <p class="text-muted small mb-3" style="font-size: 0.82rem; min-height: 2.4rem;">
                                        Franjas bloqueadas institucionalmente (ej. Grupos Estables o actos cívicos).
                                    </p>

                                    <div id="reservadosContainer">
                                        {{-- Filas dinámicas generadas con JS --}}
                                    </div>

                                    {{-- Feedback visual de errores en Bloques Reservados --}}
                                    <div id="errorReservadosFeedback" class="alert alert-danger py-2 px-3 small mt-2 d-none" style="border-radius: 8px; font-size: 0.82rem;">
                                        <i class="fas fa-exclamation-circle me-1"></i>
                                        <span class="error-text"></span>
                                    </div>
                                    @if ($errors->has('reservados*') || $errors->has('reservados'))
                                        <div class="alert alert-danger py-2 px-3 small mt-2" style="border-radius: 8px; font-size: 0.82rem;">
                                            <i class="fas fa-exclamation-circle me-1"></i>
                                            <span>{{ $errors->first('reservados') ?: ($errors->first('reservados.*.desde') ?: $errors->first('reservados.*')) }}</span>
                                        </div>
                                    @endif

                                    @php
                                        $reservadosInit = old('reservados', $reservadosArray ?? []);
                                        $tieneReservados = is_array($reservadosInit) && count($reservadosInit) > 0;
                                    @endphp
                                    <div id="sinReservadosAlert" class="text-center py-3 px-2 mt-auto {{ $tieneReservados ? 'd-none' : 'd-flex' }} flex-column align-items-center justify-content-center">
                                        <div class="d-inline-flex align-items-center justify-content-center mb-2"
                                             style="width: 44px; height: 44px; border-radius: 10px; background-color: #F5F3FF; color: #6366F1; border: 1px solid #EDE9FE;">
                                            <i class="fas fa-lock" style="font-size: 18px; color: #6366F1;"></i>
                                        </div>
                                        <p class="small mb-2" style="font-size: 0.82rem; color: #6B7280 !important; font-weight: 400;">No hay bloques reservados.</p>
                                        <div class="mt-auto">
                                            <button type="button" class="btn-sm d-inline-flex align-items-center gap-1" id="btnAgregarPrimerReservado"
                                                    style="background-color: #FFFFFF; border: 1px solid #DDD6FE; color: #6366F1; border-radius: 8px; font-weight: 500; font-size: 0.85rem; padding: 6px 14px; transition: all 0.15s ease;">
                                                <i class="fas fa-plus" style="color: #6366F1;"></i>
                                                <span>+ Agregar primer bloque reservado</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Botón Guardar Alineado a la Derecha --}}
                    <div class="d-flex justify-content-end mb-4">
                        <button type="submit" class="btn-primary-modern d-inline-flex align-items-center gap-2" style="padding: 10px 24px; border-radius: 8px; font-weight: 600; font-size: 0.92rem;">
                            <i class="fas fa-save"></i>
                            <span>Guardar Configuración de Horario</span>
                        </button>
                    </div>
                </div>

                {{-- Columna Derecha: Vista Previa en Vivo (Sticky) --}}
                <div class="col-md-5 col-lg-4">
                    <div class="card config-card preview-card" style="position: sticky; top: 20px;">
                        <div class="card-header config-card-header d-flex align-items-center justify-content-between">
                            <div>
                                <h2 class="section-title">
                                    <i class="fas fa-eye text-primary"></i>
                                    <span>Vista Previa en Vivo</span>
                                </h2>
                                <small class="text-muted" style="font-size: 0.78rem;">Marco horario calculado</small>
                            </div>
                            <span class="badge" id="kpiTotalHoras"
                                  style="border-radius: 8px; font-size: 0.82rem; font-weight: 600; background-color: #F3F4F6; color: #1F2937; border: 1px solid #E5E7EB; padding: 6px 12px;">
                                07:00 - 13:20
                            </span>
                        </div>
                        <div class="card-body config-card-body">
                            {{-- Resumen Estadístico con Acentos Pastel SaaS --}}
                            <div class="d-flex align-items-center gap-2 p-2 mb-3 flex-wrap" style="background-color: #F9FAFB; border: 1px solid #F3F4F6; border-radius: 10px;">
                                <div class="d-inline-flex align-items-center gap-1 px-2 py-1"
                                     style="background-color: #EFF6FF; color: #1E3A8A; font-weight: 600; font-size: 0.78rem; border-radius: 8px; border: none; padding: 5px 10px;">
                                    <i class="fas fa-th" style="color: #1E3A8A;"></i> 
                                    <span><span id="resumen-bloques">{{ $numeroBloques }}</span> Bloques</span>
                                </div>
                                <div class="d-inline-flex align-items-center gap-1 px-2 py-1"
                                     style="background-color: #F0FDF4; color: #166534; font-weight: 600; font-size: 0.78rem; border-radius: 8px; border: none; padding: 5px 10px;">
                                    <i class="far fa-clock" style="color: #166534;"></i> 
                                    <span><span id="resumen-minutos">{{ $minutosLectivos }}</span>m</span>
                                </div>
                                <div class="d-inline-flex align-items-center gap-1 px-2 py-1"
                                     style="background-color: #FFF7ED; color: #9A3412; font-weight: 600; font-size: 0.78rem; border-radius: 8px; border: none; padding: 5px 10px;">
                                    <i class="fas fa-coffee" style="color: #9A3412;"></i> 
                                    <span><span id="resumen-receso">{{ $minutosReceso }}</span>m</span>
                                </div>
                            </div>

                            {{-- Timeline de bloques --}}
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="text-uppercase fw-semibold" style="font-size: 0.74rem; color: #6B7280; letter-spacing: 0.5px;">
                                    <i class="fas fa-stream me-1" style="color: #4A6FC1;"></i> Distribución de la Jornada
                                </span>
                            </div>
                            <div id="previewTimeline" class="pe-1" style="max-height: 520px; overflow-y: auto;">
                                {{-- Renderizado reactivo en JS --}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
            </div>

            {{-- PESTAÑA 2: POSICIONES POR MATERIA --}}
            <div class="tab-pane fade {{ $tabActual === 'posiciones' ? 'show active' : '' }}"
                 id="pane-posiciones"
                 role="tabpanel"
                 aria-labelledby="tab-posiciones-btn">
                @include('admin.horario.partials._posiciones_tab')
            </div>
        </div>
    </div>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                    opcionesSelect += `<option value="${b}" ${selected}>${b}</option>`;
                }

                const fila = document.createElement('div');
                fila.className = 'receso-row p-3 mb-3 d-flex align-items-end gap-3 flex-nowrap';
                fila.id = `receso_row_${index}`;
                fila.style.cssText = 'background-color: #FFFFFF; border: 1px solid #E5E7EB; border-radius: 8px; width: 100%; box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);';
                fila.innerHTML = `
                    <div class="flex-grow-1" style="min-width: 0;">
                        <label class="text-muted fw-bold" style="font-size: 13px; margin-bottom: 4px; display: block; white-space: nowrap;">Después del bloque</label>
                        <select name="recesos[${index}][despues_de_bloque]" class="form-select form-select-sm text-center select-despues-de" style="height: 38px; border-radius: 8px; border: 1px solid #E5E7EB; background-color: #FFFFFF; font-size: 0.85rem;">
                            ${opcionesSelect}
                        </select>
                    </div>
                    <div class="flex-grow-1" style="min-width: 0;">
                        <label class="text-muted fw-bold" style="font-size: 13px; margin-bottom: 4px; display: block; white-space: nowrap;">Duración (min)</label>
                        <input type="number" name="recesos[${index}][duracion_min]" min="5" max="120" step="5" value="${duracion}" class="form-control form-control-sm input-duracion-receso" style="height: 38px; border-radius: 8px; border: 1px solid #E5E7EB; background-color: #FFFFFF; font-size: 0.85rem;" placeholder="Minutos">
                    </div>
                    <div class="flex-shrink-0">
                        <button type="button" class="btn btn-outline-danger btn-sm btn-eliminar-receso" title="Eliminar receso" style="height: 38px; width: 38px; border-radius: 8px; border: 1px solid #FECACA; background-color: #FFFFFF; color: #EF4444; display: inline-flex; align-items: center; justify-content: center; transition: all 0.15s ease;">
                            <i class="fas fa-trash-alt" style="font-size: 0.85rem;"></i>
                        </button>
                    </div>
                `;

                recesosContainer.appendChild(fila);

                fila.querySelector('.btn-eliminar-receso').addEventListener('click', function () {
                    fila.remove();
                    actualizarEstadoRecesos();
                    actualizarOpcionesDespuesDe();
                    recalcularVistaPrevia();
                });

                fila.querySelector('.select-despues-de').addEventListener('change', function () {
                    actualizarOpcionesDespuesDe();
                    recalcularVistaPrevia();
                });
                fila.querySelector('.input-duracion-receso').addEventListener('input', recalcularVistaPrevia);

                actualizarEstadoRecesos();
                actualizarOpcionesDespuesDe();
                recalcularVistaPrevia();
            }

            function actualizarEstadoRecesos() {
                if (!recesosContainer || !sinRecesosAlert) return;
                const filas = recesosContainer.querySelectorAll('.receso-row');
                if (filas.length === 0) {
                    sinRecesosAlert.classList.remove('d-none');
                    sinRecesosAlert.classList.add('d-flex');
                } else {
                    sinRecesosAlert.classList.add('d-none');
                    sinRecesosAlert.classList.remove('d-flex');
                }
            }

            function actualizarOpcionesDespuesDe() {
                if (!recesosContainer) return;
                const numBloques = parseInt(inputNumeroBloques.value, 10) || 4;
                const selects = Array.from(recesosContainer.querySelectorAll('.select-despues-de'));
                const valoresSeleccionados = selects.map(s => parseInt(s.value, 10)).filter(v => !isNaN(v) && v > 0);

                selects.forEach(sel => {
                    const valorActual = parseInt(sel.value, 10);
                    let opciones = '';
                    for (let b = 1; b < numBloques; b++) {
                        const isCurrent = (b === valorActual);
                        const isAlreadyUsed = valoresSeleccionados.includes(b) && !isCurrent;
                        const selectedAttr = isCurrent ? 'selected' : '';
                        const disabledAttr = isAlreadyUsed ? 'disabled' : '';
                        opciones += `<option value="${b}" ${selectedAttr} ${disabledAttr}>${b}${isAlreadyUsed ? ' (en uso)' : ''}</option>`;
                    }
                    sel.innerHTML = opciones;
                });

                validarDuplicadosRecesos();
            }

            function buscarSiguienteBloqueRecesoDisponible() {
                const numBloques = parseInt(inputNumeroBloques.value, 10) || 4;
                const selects = Array.from(recesosContainer.querySelectorAll('.select-despues-de'));
                const usados = selects.map(s => parseInt(s.value, 10));
                for (let b = 1; b < numBloques; b++) {
                    if (!usados.includes(b)) {
                        return b;
                    }
                }
                return null;
            }

            function validarDuplicadosRecesos() {
                const errorFeedback = document.getElementById('errorRecesosFeedback');
                if (!recesosContainer) return true;
                const selects = Array.from(recesosContainer.querySelectorAll('.select-despues-de'));
                const valores = selects.map(s => parseInt(s.value, 10)).filter(v => !isNaN(v) && v > 0);
                const tieneDuplicados = new Set(valores).size !== valores.length;

                if (tieneDuplicados) {
                    mostrarErrorReceso('Ya existe un receso asignado a este bloque.');
                    return false;
                } else {
                    ocultarErrorReceso();
                    return true;
                }
            }

            function mostrarErrorReceso(mensaje) {
                const errorFeedback = document.getElementById('errorRecesosFeedback');
                if (!errorFeedback) return;
                const span = errorFeedback.querySelector('.error-text');
                if (span) span.textContent = mensaje;
                errorFeedback.classList.remove('d-none');
            }

            function ocultarErrorReceso() {
                const errorFeedback = document.getElementById('errorRecesosFeedback');
                if (!errorFeedback) return;
                errorFeedback.classList.add('d-none');
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
                    opcionesDesde += `<option value="${b}" ${selDesde}>${b}</option>`;

                    const selHasta = (b === vHasta) ? 'selected' : '';
                    const disHasta = (b < vDesde) ? 'disabled' : '';
                    opcionesHasta += `<option value="${b}" ${selHasta} ${disHasta}>${b}</option>`;
                }

                const fila = document.createElement('div');
                fila.className = 'd-flex align-items-end gap-3 mb-3 flex-nowrap reservado-row p-3';
                fila.id = `reservado_row_${index}`;
                fila.style.cssText = 'background-color: #FFFFFF; border: 1px solid #E5E7EB; border-radius: 8px; width: 100%; box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);';
                fila.innerHTML = `
                    <!-- Día (Más angosto) -->
                    <div style="width: 110px; flex-shrink: 0;">
                        <label class="text-muted fw-bold" style="font-size: 13px; margin-bottom: 4px; display: block; white-space: nowrap;">Día</label>
                        <select name="reservados[${index}][dia]" class="form-select form-select-sm select-reservado-dia" style="height: 38px; border-radius: 8px; border: 1px solid #E5E7EB; background-color: #FFFFFF; font-size: 0.85rem;">
                            ${opcionesDias}
                        </select>
                    </div>

                    <!-- Desde -->
                    <div style="flex-grow: 1; min-width: 0;">
                        <label class="text-muted fw-bold" style="font-size: 13px; margin-bottom: 4px; display: block; white-space: nowrap;" title="Desde Bloque">Desde</label>
                        <select name="reservados[${index}][desde]" class="form-select form-select-sm text-center select-reservado-desde" style="height: 38px; border-radius: 8px; border: 1px solid #E5E7EB; background-color: #FFFFFF; font-size: 0.85rem;">
                            ${opcionesDesde}
                        </select>
                    </div>

                    <!-- Hasta -->
                    <div style="flex-grow: 1; min-width: 0;">
                        <label class="text-muted fw-bold" style="font-size: 13px; margin-bottom: 4px; display: block; white-space: nowrap;" title="Hasta Bloque">Hasta</label>
                        <select name="reservados[${index}][hasta]" class="form-select form-select-sm text-center select-reservado-hasta" style="height: 38px; border-radius: 8px; border: 1px solid #E5E7EB; background-color: #FFFFFF; font-size: 0.85rem;">
                            ${opcionesHasta}
                        </select>
                    </div>

                    <!-- Botón Eliminar (Ancho fijo) -->
                    <div style="flex-shrink: 0;">
                        <button type="button" class="btn btn-outline-danger btn-sm btn-eliminar-reservado" title="Eliminar bloque reservado" style="height: 38px; width: 38px; border-radius: 8px; border: 1px solid #FECACA; background-color: #FFFFFF; color: #EF4444; display: inline-flex; align-items: center; justify-content: center; transition: all 0.15s ease;">
                            <i class="fas fa-trash-alt" style="font-size: 0.85rem;"></i>
                        </button>
                    </div>
                `;

                reservadosContainer.appendChild(fila);

                const selectDia = fila.querySelector('.select-reservado-dia');
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

                selectDesde.addEventListener('change', function () {
                    sincronizarHasta();
                    validarSolapamientoReservados();
                });
                selectHasta.addEventListener('change', function () {
                    validarSolapamientoReservados();
                });
                selectDia.addEventListener('change', function () {
                    validarSolapamientoReservados();
                });

                fila.querySelector('.btn-eliminar-reservado').addEventListener('click', function () {
                    fila.remove();
                    actualizarEstadoReservados();
                    validarSolapamientoReservados();
                });

                actualizarEstadoReservados();
                validarSolapamientoReservados();
            }

            function actualizarEstadoReservados() {
                if (!reservadosContainer || !sinReservadosAlert) return;
                const filas = reservadosContainer.querySelectorAll('.reservado-row');
                if (filas.length === 0) {
                    sinReservadosAlert.classList.remove('d-none');
                    sinReservadosAlert.classList.add('d-flex');
                } else {
                    sinReservadosAlert.classList.add('d-none');
                    sinReservadosAlert.classList.remove('d-flex');
                }
            }

            function validarSolapamientoReservados() {
                const errorFeedback = document.getElementById('errorReservadosFeedback');
                if (!reservadosContainer) return true;
                const filas = Array.from(reservadosContainer.querySelectorAll('.reservado-row'));

                filas.forEach(f => {
                    f.style.borderColor = '#E5E7EB';
                });

                const rangos = [];
                let mensajeError = null;

                for (let i = 0; i < filas.length; i++) {
                    const fila = filas[i];
                    const dia = parseInt(fila.querySelector('.select-reservado-dia')?.value, 10);
                    const desde = parseInt(fila.querySelector('.select-reservado-desde')?.value, 10);
                    const hasta = parseInt(fila.querySelector('.select-reservado-hasta')?.value, 10);

                    if (isNaN(dia) || isNaN(desde) || isNaN(hasta)) continue;

                    if (desde > hasta) {
                        fila.style.borderColor = '#EF4444';
                        mensajeError = 'El bloque inicial no puede ser mayor al bloque final.';
                        break;
                    }

                    for (const r of rangos) {
                        if (r.dia === dia) {
                            if (Math.max(desde, r.desde) <= Math.min(hasta, r.hasta)) {
                                fila.style.borderColor = '#EF4444';
                                r.fila.style.borderColor = '#EF4444';
                                mensajeError = 'El bloque reservado se cruza con uno existente.';
                                break;
                            }
                        }
                    }

                    if (mensajeError) break;

                    rangos.push({ dia, desde, hasta, fila });
                }

                if (mensajeError) {
                    mostrarErrorReservados(mensajeError);
                    return false;
                } else {
                    ocultarErrorReservados();
                    return true;
                }
            }

            function buscarSiguienteReservadoDisponible() {
                const numBloques = parseInt(inputNumeroBloques.value, 10) || 4;
                const filas = Array.from(reservadosContainer.querySelectorAll('.reservado-row'));
                const ocupados = {};
                filas.forEach(f => {
                    const dia = parseInt(f.querySelector('.select-reservado-dia')?.value, 10);
                    const desde = parseInt(f.querySelector('.select-reservado-desde')?.value, 10);
                    const hasta = parseInt(f.querySelector('.select-reservado-hasta')?.value, 10);
                    if (!isNaN(dia) && !isNaN(desde) && !isNaN(hasta)) {
                        for (let b = desde; b <= hasta; b++) {
                            ocupados[`${dia}_${b}`] = true;
                        }
                    }
                });

                for (let d = 1; d <= 5; d++) {
                    for (let b = 1; b <= numBloques; b++) {
                        if (!ocupados[`${d}_${b}`]) {
                            return { dia: d, desde: b, hasta: b };
                        }
                    }
                }
                return null;
            }

            function mostrarErrorReservados(mensaje) {
                const errorFeedback = document.getElementById('errorReservadosFeedback');
                if (!errorFeedback) return;
                const span = errorFeedback.querySelector('.error-text');
                if (span) span.textContent = mensaje;
                errorFeedback.classList.remove('d-none');
            }

            function ocultarErrorReservados() {
                const errorFeedback = document.getElementById('errorReservadosFeedback');
                if (!errorFeedback) return;
                errorFeedback.classList.add('d-none');
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
                        optDesde += `<option value="${b}" ${b === curDesde ? 'selected' : ''}>${b}</option>`;
                        const dis = (b < curDesde) ? 'disabled' : '';
                        optHasta += `<option value="${b}" ${b === curHasta ? 'selected' : ''} ${dis}>${b}</option>`;
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
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge" style="background-color: #F8FAFC; color: #334155; border-radius: 6px; font-size: 0.78rem; border: 1px solid #E2E8F0; font-weight: 600; padding: 4px 8px;">Bloque ${b}</span>
                                <strong style="color: #111827; font-size: 0.9rem; font-weight: 600;">${formatTime(startMin)} - ${formatTime(endMin)}</strong>
                            </div>
                            <div class="d-flex align-items-center gap-1" style="color: #6B7280; font-size: 0.8rem;">
                                <i class="far fa-clock" style="color: #60A5FA;"></i> ${duracionBloque} min
                            </div>
                        </div>
                    `;

                    const receso = recesosMap[b] || 0;
                    if (receso > 0) {
                        htmlTimeline += `
                            <div class="timeline-receso-item">
                                <i class="fas fa-coffee" style="color: #6B7280;"></i>
                                <span>Receso (${receso} min): <strong>${formatTime(endMin)} - ${formatTime(endMin + receso)}</strong></span>
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
                    const siguiente = buscarSiguienteBloqueRecesoDisponible();
                    if (siguiente === null) {
                        mostrarErrorReceso('Ya existe un receso asignado a cada uno de los bloques disponibles.');
                        return;
                    }
                    ocultarErrorReceso();
                    agregarFilaReceso(siguiente, 20);
                    actualizarOpcionesDespuesDe();
                });
            }

            const btnAgregarPrimerReceso = document.getElementById('btnAgregarPrimerReceso');
            if (btnAgregarPrimerReceso && btnAgregarReceso) {
                btnAgregarPrimerReceso.addEventListener('click', function () {
                    btnAgregarReceso.click();
                });
            }

            if (btnAgregarReservado) {
                btnAgregarReservado.addEventListener('click', function () {
                    const siguiente = buscarSiguienteReservadoDisponible();
                    if (siguiente === null) {
                        mostrarErrorReservados('Todos los bloques de la semana ya se encuentran reservados.');
                        return;
                    }
                    ocultarErrorReservados();
                    agregarFilaReservado(siguiente.dia, siguiente.desde, siguiente.hasta);
                    validarSolapamientoReservados();
                });
            }

            const btnAgregarPrimerReservado = document.getElementById('btnAgregarPrimerReservado');
            if (btnAgregarPrimerReservado && btnAgregarReservado) {
                btnAgregarPrimerReservado.addEventListener('click', function () {
                    btnAgregarReservado.click();
                });
            }

            const configHorarioForm = document.getElementById('configHorarioForm');
            if (configHorarioForm) {
                configHorarioForm.addEventListener('submit', function (e) {
                    const recesosValidos = validarDuplicadosRecesos();
                    const reservadosValidos = validarSolapamientoReservados();

                    if (!recesosValidos || !reservadosValidos) {
                        e.preventDefault();
                        const mensaje = !recesosValidos 
                            ? 'Ya existe un receso asignado a este bloque. Por favor revisa la configuración de recesos.'
                            : 'El bloque reservado se cruza con uno existente. Por favor revisa la configuración de bloques reservados.';

                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Conflicto detectado',
                                text: mensaje,
                                confirmButtonColor: '#4A6FC1',
                                confirmButtonText: 'Entendido'
                            });
                        } else {
                            alert(mensaje);
                        }
                    }
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

            // Sincronizar parámetro ?tab= al cambiar de pestaña
            document.querySelectorAll('#configHorarioTabs [data-bs-toggle="tab"]').forEach(tabBtn => {
                tabBtn.addEventListener('shown.bs.tab', function (e) {
                    const key = e.target.getAttribute('data-tab-key');
                    if (key) {
                        const url = new URL(window.location.href);
                        url.searchParams.set('tab', key);
                        window.history.replaceState({}, '', url.toString());
                    }
                });
            });
        });

        // ==========================================================
        // 4. POSICIONES PEDAGÓGICAS POR MATERIA (PESTAÑA 2)
        // ==========================================================
        const ESTADOS_POSICION = ['disponible', 'no_preferente', 'no_preferente_fuerte', 'prohibido'];

        const BADGES_POSICION = {
            'disponible': {
                html: '<span class="pos-badge text-muted"><i class="fas fa-check"></i> Disponible</span>'
            },
            'no_preferente': {
                html: '<span class="pos-badge" style="background: rgba(234, 179, 8, 0.2); color: #854d0e;"><i class="fas fa-exclamation-circle"></i> No pref. (+1)</span>'
            },
            'no_preferente_fuerte': {
                html: '<span class="pos-badge" style="background: rgba(249, 115, 22, 0.2); color: #9a3412;"><i class="fas fa-exclamation-triangle"></i> No pref. ++ (+3)</span>'
            },
            'prohibido': {
                html: '<span class="pos-badge" style="background: rgba(239, 68, 68, 0.2); color: #991b1b;"><i class="fas fa-ban"></i> Prohibido</span>'
            }
        };

        function ciclarEstadoCelda(celda) {
            const actual = celda.getAttribute('data-tipo') || 'disponible';
            const indiceActual = ESTADOS_POSICION.indexOf(actual);
            const nuevoIndice = (indiceActual + 1) % ESTADOS_POSICION.length;
            const nuevoEstado = ESTADOS_POSICION[nuevoIndice];
            aplicarEstadoACelda(celda, nuevoEstado);
        }

        function aplicarEstadoACelda(celda, nuevoEstado) {
            celda.setAttribute('data-tipo', nuevoEstado);
            ESTADOS_POSICION.forEach(e => celda.classList.remove('estado-' + e));
            celda.classList.add('estado-' + nuevoEstado);

            const cfg = BADGES_POSICION[nuevoEstado] || BADGES_POSICION['disponible'];
            const cont = celda.querySelector('.contenido-celda');
            if (cont) cont.innerHTML = cfg.html;
        }

        (function initPosicionesTab() {
            const btnLimpiar = document.getElementById('btnLimpiarCuadricula');
            const selectArea = document.getElementById('selectArea');
            const selectGrado = document.getElementById('selectGrado');
            const btnGuardar = document.getElementById('btnGuardarPosiciones');

            if (btnLimpiar) {
                btnLimpiar.addEventListener('click', function () {
                    document.querySelectorAll('.celda-posicion').forEach(celda => {
                        aplicarEstadoACelda(celda, 'disponible');
                    });
                });
            }

            function cargarPosiciones() {
                if (!selectArea || !selectGrado) return;
                const areaId = selectArea.value;
                const gradoId = selectGrado.value;
                if (!areaId) return;

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
                            const celda = document.querySelector(`.celda-posicion[data-dia="${p.dia}"][data-bloque="${p.bloque}"]`);
                            if (celda) aplicarEstadoACelda(celda, p.tipo);
                        });
                    }
                })
                .catch(err => console.error('Error al cargar posiciones:', err));
            }

            if (selectArea) selectArea.addEventListener('change', cargarPosiciones);
            if (selectGrado) selectGrado.addEventListener('change', cargarPosiciones);

            if (btnGuardar) {
                btnGuardar.addEventListener('click', function () {
                    const areaId = selectArea ? selectArea.value : '';
                    const gradoId = selectGrado ? selectGrado.value : '0';

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

                    const posiciones = [];
                    document.querySelectorAll('.celda-posicion').forEach(c => {
                        posiciones.push({
                            dia: parseInt(c.getAttribute('data-dia'), 10),
                            bloque: parseInt(c.getAttribute('data-bloque'), 10),
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
                            area_formacion_id: parseInt(areaId, 10),
                            grado_id: gradoId === '0' || gradoId === '' ? null : parseInt(gradoId, 10),
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
                    .catch(() => {
                        btn.disabled = false;
                        btn.innerHTML = textoOriginal;
                        Swal.fire({
                            icon: 'error',
                            title: 'Error de Conexión',
                            text: 'Ocurrió un error al comunicarse con el servidor.'
                        });
                    });
                });
            }
        })();
    </script>
@stop
