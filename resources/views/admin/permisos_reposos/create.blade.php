@extends('adminlte::page')

@section('css')
    {{-- Estilos modernos reutilizados del sistema --}}
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modal-styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/view-styles.css') }}">
@stop

@section('title', 'Registrar Permiso/Reposo')

@section('content_header')
    <div class="content-header-modern">
        {{-- Breadcrumbs --}}
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb" style="background: transparent; padding: 0; margin: 0; font-size: 0.85rem;">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" style="color: var(--primary); text-decoration: none;"><i class="fas fa-home me-1"></i> Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.permisos_reposos.index') }}" style="color: var(--primary); text-decoration: none;">Gestión de Inasistencias</a></li>
                <li class="breadcrumb-item active" aria-current="page" style="color: var(--gray-700); font-weight: 700;">Registrar Permiso o Reposo</li>
            </ol>
        </nav>

        <div class="header-content">
            <div class="header-title">
                <div class="icon-wrapper">
                    <i class="fas fa-file-signature"></i>
                </div>
                <div>
                    <h1 class="title-main">Registrar Permiso / Reposo</h1>
                    <p class="title-subtitle">Formulario de justificación de inasistencias</p>
                </div>
            </div>
            <a href="{{ route('admin.permisos_reposos.index') }}" class="btn-create" style="background: var(--gray-500);">
                <i class="fas fa-arrow-left"></i>
                <span>Volver</span>
            </a>
        </div>
    </div>
@stop

@section('content')
<div class="main-container">
    <div class="card-modern" style="max-width: 800px; margin: 0 auto;">
        <div class="card-header-modern">
            <div class="header-left">
                <div class="header-icon">
                    <i class="fas fa-edit"></i>
                </div>
                <div>
                    <h3>Registrar Solicitud</h3>
                    <p>Complete todos los campos requeridos para justificar la inasistencia</p>
                </div>
            </div>
        </div>

        <div class="card-body-modern" style="padding: 2rem;">
            <form action="#" method="POST" enctype="multipart/form-data" class="form-modern" id="formSolicitud">
                @csrf
                
                <!-- 1. Tipo de Usuario -->
                <div class="form-group-modern mb-3">
                    <label for="tipo_usuario" class="form-label-modern">
                        <i class="fas fa-user-tag"></i> Tipo de Usuario <span class="required-badge">*</span>
                    </label>
                    @role('Representante')
                        <select name="tipo_usuario" id="tipo_usuario" class="form-control-modern" required onchange="actualizarPlaceholderBuscador()">
                            <option value="estudiante" selected>Estudiante</option>
                        </select>
                        <small class="form-text-modern">
                            <i class="fas fa-info-circle"></i>
                            Como representante, solo puede justificar inasistencias de sus representados (Estudiantes).
                        </small>
                    @else
                        <select name="tipo_usuario" id="tipo_usuario" class="form-control-modern" required onchange="actualizarPlaceholderBuscador()">
                            <option value="" disabled selected>Seleccione tipo de usuario</option>
                            <option value="estudiante">Estudiante</option>
                            <option value="administrativo">Personal Administrativo</option>
                            <option value="obrero">Personal Obrero</option>
                        </select>
                        <small class="form-text-modern">
                            <i class="fas fa-info-circle"></i>
                            Indique el tipo de usuario que realiza la solicitud.
                        </small>
                    @endrole
                </div>

                <!-- 2. Buscador o campo para el nombre/cédula de la persona -->
                <div class="form-group-modern mb-3">
                    <label for="persona_busqueda" class="form-label-modern">
                        <i class="fas fa-search"></i> Buscar Persona (Cédula o Nombre) <span class="required-badge">*</span>
                    </label>
                    <div class="input-group-modern">
                        <span class="input-prefix-modern">
                            <i class="fas fa-id-card"></i>
                        </span>
                        <input 
                            type="text" 
                            class="form-control-modern with-prefix" 
                            name="persona_busqueda"
                            id="persona_busqueda"
                            placeholder="Seleccione tipo de usuario primero"
                            list="personas_list"
                            disabled
                            required>
                    </div>
                    <datalist id="personas_list">
                        <!-- Se llena dinámicamente con JS según el tipo de usuario -->
                    </datalist>
                    <small class="form-text-modern">
                        <i class="fas fa-info-circle"></i>
                        Ingrese el número de cédula o nombre para buscar en la base de datos.
                    </small>
                </div>

                <!-- 3. Tipo de Solicitud -->
                <div class="form-group-modern mb-3">
                    <label class="form-label-modern mb-2">
                        <i class="fas fa-file-medical"></i> Tipo de Solicitud <span class="required-badge">*</span>
                    </label>
                    <div class="radio-group-modern">
                        <label class="radio-modern">
                            <input type="radio" name="tipo_solicitud" value="Permiso" checked required>
                            <span class="radio-checkmark"></span>
                            <span class="radio-label">Permiso (Asuntos Personales / Diligencias)</span>
                        </label>
                        <label class="radio-modern">
                            <input type="radio" name="tipo_solicitud" value="Reposo Médico">
                            <span class="radio-checkmark"></span>
                            <span class="radio-label">Reposo Médico (Validado por centro de salud)</span>
                        </label>
                    </div>
                </div>

                <!-- 4. Rango de fechas (Fecha de inicio y Fecha de fin) -->
                <div class="form-row-modern mb-3" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group-modern">
                        <label for="fecha_inicio" class="form-label-modern">
                            <i class="fas fa-calendar-alt"></i> Fecha Inicio <span class="required-badge">*</span>
                        </label>
                        <input 
                            type="date" 
                            class="form-control-modern" 
                            name="fecha_inicio" 
                            id="fecha_inicio" 
                            required 
                            onchange="calcularDias()">
                    </div>
                    <div class="form-group-modern">
                        <label for="fecha_fin" class="form-label-modern">
                            <i class="fas fa-calendar-check"></i> Fecha Fin <span class="required-badge">*</span>
                        </label>
                        <input 
                            type="date" 
                            class="form-control-modern" 
                            name="fecha_fin" 
                            id="fecha_fin" 
                            required 
                            onchange="calcularDias()">
                    </div>
                </div>

                <div class="mb-3 px-3 py-2 rounded bg-light border d-flex justify-content-between align-items-center" style="font-size: 0.85rem;">
                    <span class="text-muted"><i class="fas fa-clock me-1"></i> Total de días calculados:</span>
                    <strong id="total_dias" class="text-primary">0 días</strong>
                </div>

                <!-- 5. Motivo o descripción de la inasistencia -->
                <div class="form-group-modern mb-3">
                    <label for="motivo" class="form-label-modern">
                        <i class="fas fa-align-left"></i> Motivo o Descripción <span class="required-badge">*</span>
                    </label>
                    <textarea 
                        class="form-control-modern" 
                        name="motivo" 
                        id="motivo" 
                        rows="3" 
                        placeholder="Describa el motivo detallado de la inasistencia..." 
                        required></textarea>
                    <small class="form-text-modern">
                        <i class="fas fa-info-circle"></i>
                        Explique la justificación de la ausencia.
                    </small>
                </div>

                <!-- 6. Campo para adjuntar justificativo digitalizado (PDF o imagen) -->
                <div class="form-group-modern mb-4">
                    <label class="form-label-modern">
                        <i class="fas fa-paperclip"></i> Justificativo Digitalizado <span class="required-badge">*</span>
                    </label>
                    <div class="file-upload-modern">
                        <input type="file" name="justificativo" id="justificativo" accept="image/*,application/pdf" hidden required onchange="mostrarArchivoSeleccionado()">
                        <label for="justificativo" class="file-upload-label" id="file_upload_label" style="cursor: pointer; border: 2px dashed var(--gray-300); border-radius: var(--radius); padding: 1.5rem; text-align: center; display: block; background: var(--gray-50); transition: all 0.3s ease;">
                            <div class="file-upload-icon" style="font-size: 2rem; color: var(--gray-500); margin-bottom: 0.5rem;">
                                <i class="fas fa-cloud-upload-alt"></i>
                            </div>
                            <div class="file-upload-text" id="file_upload_text">
                                <strong>Haz clic para subir</strong>
                                <span>o arrastra y suelta</span>
                            </div>
                            <div class="file-upload-info" style="font-size: 0.75rem; color: var(--gray-500); margin-top: 0.25rem;">
                                Formatos permitidos: PDF o Imágenes (JPG, PNG) hasta 5MB
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="form-actions-modern d-flex justify-content-end gap-2">
                    <button type="reset" class="btn-secondary-modern" onclick="resetForm()">
                        <i class="fas fa-undo"></i> Limpiar
                    </button>
                    <button type="submit" class="btn-primary-modern">
                        <i class="fas fa-save"></i> Guardar Solicitud
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
    // Listado simulado de personas según tipo de usuario para el buscador autocompletable
    const personasPorTipo = {
        estudiante: [
            { nombre: "Ana Maria Valera", cedula: "24.555.888" },
            { nombre: "José Gregorio Hernandez", cedula: "25.111.222" },
            { nombre: "Maria Alejandra Perez", cedula: "26.333.444" },
            { nombre: "Luis Alfredo Colmenarez", cedula: "27.555.666" }
        ],
        administrativo: [
            { nombre: "Carlos Mendoza", cedula: "18.222.333" },
            { nombre: "Patricia Colina", cedula: "14.666.777" },
            { nombre: "Eduardo Silva", cedula: "15.999.888" }
        ],
        obrero: [
            { nombre: "Pedro Perez", cedula: "12.444.555" },
            { nombre: "Juan Bautista", cedula: "10.111.111" },
            { nombre: "Ramon Valdes", cedula: "11.222.222" }
        ]
    };

    function actualizarPlaceholderBuscador() {
        const selectUsuario = document.getElementById('tipo_usuario');
        const inputBuscador = document.getElementById('persona_busqueda');
        const datalist = document.getElementById('personas_list');
        
        datalist.innerHTML = '';
        
        if (selectUsuario.value) {
            inputBuscador.disabled = false;
            inputBuscador.placeholder = `Escriba para buscar ${selectUsuario.options[selectUsuario.selectedIndex].text}...`;
            
            // Llenar datalist
            const personas = personasPorTipo[selectUsuario.value];
            personas.forEach(p => {
                const option = document.createElement('option');
                option.value = `${p.nombre} (V-${p.cedula})`;
                datalist.appendChild(option);
            });
        } else {
            inputBuscador.disabled = true;
            inputBuscador.placeholder = "Seleccione tipo de usuario primero";
            inputBuscador.value = '';
        }
    }

    function mostrarArchivoSeleccionado() {
        const input = document.getElementById('justificativo');
        const labelText = document.getElementById('file_upload_text');
        const label = document.getElementById('file_upload_label');
        
        if (input.files && input.files[0]) {
            const file = input.files[0];
            labelText.innerHTML = `<strong>Archivo seleccionado:</strong> <span>${file.name}</span>`;
            label.style.borderColor = 'var(--success)';
            label.style.background = 'var(--success-light)';
        } else {
            labelText.innerHTML = `<strong>Haz clic para subir</strong> <span>o arrastra y suelta</span>`;
            label.style.borderColor = 'var(--gray-300)';
            label.style.background = 'var(--gray-50)';
        }
    }

    function calcularDias() {
        const inicio = document.getElementById('fecha_inicio').value;
        const fin = document.getElementById('fecha_fin').value;
        const totalDiasSpan = document.getElementById('total_dias');
        
        if (inicio && fin) {
            const fechaIni = new Date(inicio);
            const fechaFin = new Date(fin);
            
            // Calcular diferencia en milisegundos
            const diffTime = fechaFin - fechaIni;
            
            if (diffTime >= 0) {
                // Calcular días (sumando 1 para incluir el día de inicio)
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                totalDiasSpan.textContent = `${diffDays} día${diffDays > 1 ? 's' : ''}`;
                totalDiasSpan.className = 'text-primary';
            } else {
                totalDiasSpan.textContent = 'Fecha fin debe ser posterior';
                totalDiasSpan.className = 'text-danger';
            }
        } else {
            totalDiasSpan.textContent = '0 días';
            totalDiasSpan.className = 'text-primary';
        }
    }

    function resetForm() {
        setTimeout(() => {
            actualizarPlaceholderBuscador();
            mostrarArchivoSeleccionado();
            calcularDias();
        }, 50);
    }

    // Manejar el submit del formulario para dar feedback en tiempo real
    document.getElementById('formSolicitud').addEventListener('submit', function(e) {
        e.preventDefault();
        alert('¡Solicitud registrada con éxito! (Simulado)');
        window.location.href = "{{ route('admin.permisos_reposos.index') }}";
    });

    document.addEventListener('DOMContentLoaded', function() {
        actualizarPlaceholderBuscador();
    });
</script>
@stop
