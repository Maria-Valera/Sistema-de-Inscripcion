    <form wire:submit.prevent="save">
        <div class="card-modern mb-4" style="border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); background: #FFFFFF;">
            <div class="card-header-modern">
                <div class="header-left">
                    <div class="header-icon" style="background: linear-gradient(135deg, var(--success), #059669);">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div>
                        <h3>Datos del Estudiante</h3>
                        <p>Información personal del estudiante</p>
                    </div>
                </div>
            </div>

            <div class="card-body-modern" style="padding: 2rem;">
                <!-- Fila 1: Identificación (4 columnas) -->
                <div class="mb-3" style="display: grid; grid-template-columns: 1fr 1.5fr 1.5fr 1fr; gap: 16px;">
                    <div class="floating-group">
                        <label for="tipo_ci">
                            Doc. <span class="text-danger">*</span>
                        </label>
                        <select wire:model.live="tipo_documento_id" id="tipo_ci"
                            class="form-select @error('tipo_documento_id') is-invalid @enderror">
                            <option value="">Seleccione</option>
                            @foreach ($tipos_documentos as $item)
                                <option value="{{ $item->id }}">{{ $item->nombre }}</option>
                            @endforeach
                        </select>
                        @error('tipo_documento_id')
                            <div class="invalid-feedback-modern">
                                <i class="fas fa-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="floating-group">
                        <label for="numero_documento">
                            Cédula <span class="text-danger">*</span>
                        </label>
                        <input type="text" wire:model.live="numero_documento" id="numero_documento"
                            class="form-control @error('numero_documento') is-invalid @enderror text-uppercase"
                            maxlength="{{ $documento_maxlength }}" pattern="{{ $documento_pattern }}"
                            inputmode="{{ $documento_inputmode }}" placeholder=" "
                            oninput="
                            @if ($tipo_documento_id == 1 || $tipo_documento_id == 3) this.value = this.value.replace(/[^0-9]/g,'')
                            @elseif($tipo_documento_id == 2)
                            this.value = this.value.replace(/[^a-zA-Z0-9]/g,'') @endif
                            ">
                        @error('numero_documento')
                            <div class="invalid-feedback-modern">
                                <i class="fas fa-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="floating-group">
                        <label for="fecha_nacimiento">
                            Fecha de Nacimiento <span class="text-danger">*</span>
                        </label>
                        <input type="date" wire:model.live="fecha_nacimiento" id="fecha_nacimiento"
                            placeholder=" "
                            class="form-control @error('fecha_nacimiento') is-invalid @enderror">
                        @error('fecha_nacimiento')
                            <div class="invalid-feedback-modern">
                                <i class="fas fa-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="floating-group">
                        <label for="genero">
                            Género <span class="text-danger">*</span>
                        </label>
                        <select wire:model.live="genero_id" id="genero"
                            class="form-select @error('genero_id') is-invalid @enderror">
                            <option value="">Seleccione</option>
                            @foreach ($generos as $item)
                                <option value="{{ $item->id }}">{{ $item->genero }}</option>
                            @endforeach
                        </select>
                        @error('genero_id')
                            <div class="invalid-feedback-modern">
                                <i class="fas fa-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>

                <!-- Fila 2: Nombres (3 columnas) -->
                <div class="mb-3" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
                    <div class="floating-group">
                        <label for="primer_nombre">
                            Primer Nombre <span class="text-danger">*</span>
                        </label>
                        <input type="text" wire:model.live="primer_nombre" id="primer_nombre"
                            class="form-control @error('primer_nombre') is-invalid @enderror text-capitalize"
                            placeholder=" ">
                        @error('primer_nombre')
                            <div class="invalid-feedback-modern">
                                <i class="fas fa-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="floating-group">
                        <label for="segundo_nombre">
                            Segundo Nombre
                        </label>
                        <input type="text" wire:model.live="segundo_nombre" id="segundo_nombre"
                            class="form-control text-capitalize" placeholder=" "
                            @disabled(empty($primer_nombre))>
                    </div>

                    <div class="floating-group">
                        <label for="tercer_nombre">
                            Tercer Nombre
                        </label>
                        <input type="text" wire:model.live="tercer_nombre" id="tercer_nombre"
                            class="form-control text-capitalize" placeholder=" "
                            @disabled(empty($segundo_nombre))>
                    </div>
                </div>

                <!-- Fila 3: Apellidos (2 columnas) -->
                <div class="mb-3" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px;">
                    <div class="floating-group">
                        <label for="primer_apellido">
                            Primer Apellido <span class="text-danger">*</span>
                        </label>
                        <input type="text" wire:model.live="primer_apellido" id="primer_apellido"
                            class="form-control @error('primer_apellido') is-invalid @enderror text-capitalize"
                            placeholder=" ">
                        @error('primer_apellido')
                            <div class="invalid-feedback-modern">
                                <i class="fas fa-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="floating-group">
                        <label for="segundo_apellido">
                            Segundo Apellido
                        </label>
                        <input type="text" wire:model.live="segundo_apellido" id="segundo_apellido"
                            class="form-control text-capitalize" placeholder=" "
                            @disabled(empty($primer_apellido))>
                    </div>
                </div>

                <!-- Fila 4: Datos Complementarios (3 columnas) -->
                <div class="mb-3" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
                    <div class="floating-group">
                        <label for="etnia_indigena_id">
                            Etnia Indígena
                        </label>
                        <select wire:model.live="etnia_indigena_id" id="etnia_indigena_id" placeholder=" "
                            class="form-select @error('etnia_indigena_id') is-invalid @enderror">
                            <option value="">Seleccione</option>
                            @foreach ($etnia_indigenas as $item)
                                <option value="{{ $item->id }}">{{ $item->nombre }}</option>
                            @endforeach
                        </select>
                        @error('etnia_indigena_id')
                            <div class="invalid-feedback-modern">
                                <i class="fas fa-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="floating-group">
                        <label for="lateralidad">
                            Lateralidad <span class="text-danger">*</span>
                        </label>
                        <select wire:model.live="lateralidad_id" id="lateralidad" placeholder=" "
                            class="form-select @error('lateralidad_id') is-invalid @enderror">
                            <option value="">Seleccione</option>
                            @foreach ($lateralidades as $item)
                                <option value="{{ $item->id }}">{{ $item->lateralidad }}</option>
                            @endforeach
                        </select>
                        @error('lateralidad_id')
                            <div class="invalid-feedback-modern">
                                <i class="fas fa-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="floating-group">
                        <label for="orden_nacimiento">
                            Orden de Nacimiento <span class="text-danger">*</span>
                        </label>
                        <select wire:model.live="orden_nacimiento_id" id="orden_nacimiento" placeholder=" "
                            class="form-select @error('orden_nacimiento_id') is-invalid @enderror">
                            <option value="">Seleccione</option>
                            @foreach ($orden_nacimientos as $item)
                                <option value="{{ $item->id }}">{{ $item->orden_nacimiento }}</option>
                            @endforeach
                        </select>
                        @error('orden_nacimiento_id')
                            <div class="invalid-feedback-modern">
                                <i class="fas fa-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
        <div class="card-modern mb-4" style="border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); background: #FFFFFF;">
            <div class="card-header-modern">
                <div class="header-left">
                    <div class="header-icon" style="background: linear-gradient(135deg, var(--warning), #d97706);">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div>
                        <h3>Lugar de Nacimiento</h3>
                        <p>Ubicación geográfica del estudiante</p>
                    </div>
                </div>
            </div>
            <div class="card-body-modern" style="padding: 2rem;">
                <div class="mb-3" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px;">
                    <!-- País -->
                    <div>
                        <div class="floating-group mb-0">
                            <label for="paisId">País <span class="text-danger">*</span></label>
                            <select wire:model.live="paisId" id="paisId" placeholder=" "
                                class="form-select @error('paisId') is-invalid @enderror">
                                <option value="">Seleccione un país</option>
                                @foreach ($paises as $pais)
                                    <option value="{{ $pais->id }}">{{ $pais->nameES }}</option>
                                @endforeach
                            </select>
                            @error('paisId')
                                <div class="invalid-feedback-modern">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Estado -->
                    <div>
                        <div class="floating-group mb-0">
                            <label for="estado_id">Estado <span class="text-danger">*</span></label>
                            <select wire:model.live="estado_id" id="estado_id" @disabled(!$paisId) placeholder=" "
                                class="form-select @error('estado_id') is-invalid @enderror">
                                <option value="">Seleccione un estado</option>
                                @foreach ($estados as $estado)
                                    <option value="{{ $estado->id }}">{{ $estado->nombre_estado }}</option>
                                @endforeach
                            </select>
                            @error('estado_id')
                                <div class="invalid-feedback-modern">
                                    <i class="fas fa-exclamation-circle"></i> {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <small class="form-text-modern d-flex align-items-center gap-1 mt-1">
                            <i class="fas fa-plus-circle text-primary"></i>
                            <a href="#" class="text-primary text-decoration-none"
                                data-bs-toggle="modal" data-bs-target="#modalCrearEstado">
                                Crear nuevo estado
                            </a>
                        </small>
                    </div>

                    <!-- Municipio -->
                    <div>
                        <div class="floating-group mb-0">
                            <label for="municipio_id">Municipio <span class="text-danger">*</span></label>
                            <select wire:model.live="municipio_id" id="municipio_id" @disabled(!$estado_id) placeholder=" "
                                class="form-select @error('municipio_id') is-invalid @enderror">
                                <option value="">Seleccione un municipio</option>
                                @foreach ($municipios as $municipio)
                                    <option value="{{ $municipio->id }}">{{ $municipio->nombre_municipio }}</option>
                                @endforeach
                            </select>
                            @error('municipio_id')
                                <div class="invalid-feedback-modern">
                                    <i class="fas fa-exclamation-circle"></i> {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <small class="form-text-modern d-flex align-items-center gap-1 mt-1">
                            <i class="fas fa-plus-circle text-primary"></i>
                            <a href="#" class="text-primary text-decoration-none" data-bs-toggle="modal"
                                data-bs-target="#modalCrearMunicipio">
                                Crear nuevo municipio
                            </a>
                        </small>
                    </div>

                    <!-- Localidad -->
                    <div>
                        <div class="floating-group mb-0">
                            <label for="localidad_id">Localidad <span class="text-danger">*</span></label>
                            <select wire:model.live="localidad_id" id="localidad_id" @disabled(!$municipio_id) placeholder=" "
                                class="form-select @error('localidad_id') is-invalid @enderror">
                                <option value="">Seleccione una localidad</option>
                                @foreach ($localidades as $localidad)
                                    <option value="{{ $localidad->id }}">{{ $localidad->nombre_localidad }}</option>
                                @endforeach
                            </select>
                            @error('localidad_id')
                                <div class="invalid-feedback-modern">
                                    <i class="fas fa-exclamation-circle"></i> {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <small class="form-text-modern d-flex align-items-center gap-1 mt-1">
                            <i class="fas fa-plus-circle text-primary"></i>
                            <a href="#" class="text-primary text-decoration-none" data-bs-toggle="modal"
                                data-bs-target="#modalCrearLocalidad">
                                Crear nueva localidad
                            </a>
                        </small>
                    </div>
                </div>

            </div>
        </div>
        <div class="card-modern mb-4" style="border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); background: #FFFFFF;">
            <div class="card-header-modern">
                <div class="header-left">
                    <div class="header-icon" style="background: linear-gradient(135deg, #8b5cf6, #6d28d9);">
                        <i class="fas fa-ruler-combined"></i>
                    </div>
                    <div>
                        <h3>Descripciones Físicas del Estudiante</h3>
                        <p>Medidas y tallas del estudiante</p>
                    </div>
                </div>
            </div>
            <div class="card-body-modern" style="padding: 2rem;">
                <div class="mb-3" style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 16px;">
                    <!-- 1. Estatura -->
                    <div class="floating-group mb-0">
                        <label for="talla_estudiante">
                            Estatura (m) <span class="text-danger">*</span>
                        </label>
                        <input type="text" wire:model.defer="talla_estudiante" wire:blur="validarEstatura" id="talla_estudiante"
                            placeholder=" "
                            class="form-control @error('talla_estudiante') is-invalid @enderror"
                            inputmode="numeric" pattern="^[0-9]*$"
                            oninput="this.value = this.value.replace(/[^0-9.,]/g, '')" maxlength="4">
                        @error('talla_estudiante')
                            <div class="invalid-feedback-modern">
                                <i class="fas fa-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- 2. Peso -->
                    <div class="floating-group mb-0">
                        <label for="peso_estudiante">
                            Peso (kg) <span class="text-danger">*</span>
                        </label>
                        <input type="number" wire:model.live="peso_estudiante" id="peso_estudiante"
                            class="form-control @error('peso_estudiante') is-invalid @enderror"
                            step="0.1" min="20" max="100" placeholder=" ">
                        @error('peso_estudiante')
                            <div class="invalid-feedback-modern">
                                <i class="fas fa-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- 3. Talla Zapato -->
                    <div class="floating-group mb-0">
                        <label for="talla_zapato">
                            Talla Zapato <span class="text-danger">*</span>
                        </label>
                        <select wire:model.live="talla_zapato" id="talla_zapato" placeholder=" "
                            class="form-select @error('talla_zapato') is-invalid @enderror">
                            <option value="">Seleccione</option>
                            @foreach (range(30, 45) as $talla)
                                <option value="{{ $talla }}">{{ $talla }}</option>
                            @endforeach
                        </select>
                        @error('talla_zapato')
                            <div class="invalid-feedback-modern">
                                <i class="fas fa-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- 4. Talla Camisa -->
                    <div class="floating-group mb-0">
                        <label for="talla_camisa_id">
                            Talla Camisa <span class="text-danger">*</span>
                        </label>
                        <select wire:model.live="talla_camisa_id" id="talla_camisa_id" placeholder=" "
                            class="form-select @error('talla_camisa_id') is-invalid @enderror">
                            <option value="">Seleccione</option>
                            @foreach ($tallas as $item)
                                <option value="{{ $item->id }}">{{ $item->nombre }}</option>
                            @endforeach
                        </select>
                        @error('talla_camisa_id')
                            <div class="invalid-feedback-modern">
                                <i class="fas fa-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- 5. Talla Pantalón -->
                    <div class="floating-group mb-0">
                        <label for="talla_pantalon_id">
                            Talla Pantalón <span class="text-danger">*</span>
                        </label>
                        <select wire:model.live="talla_pantalon_id" id="talla_pantalon_id" placeholder=" "
                            class="form-select @error('talla_pantalon_id') is-invalid @enderror">
                            <option value="">Seleccione</option>
                            @foreach ($tallas as $item)
                                <option value="{{ $item->id }}">{{ $item->nombre }}</option>
                            @endforeach
                        </select>
                        @error('talla_pantalon_id')
                            <div class="invalid-feedback-modern">
                                <i class="fas fa-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
    </form>

    <script>
        (function () {
            function inicializarBloqueoCascada() {
                const primerNombre = document.getElementById('primer_nombre');
                const segundoNombre = document.getElementById('segundo_nombre');
                const tercerNombre = document.getElementById('tercer_nombre');
                const primerApellido = document.getElementById('primer_apellido');
                const segundoApellido = document.getElementById('segundo_apellido');

                if (!primerNombre || !segundoNombre || !tercerNombre || !primerApellido || !segundoApellido) {
                    return;
                }

                function actualizarNombres() {
                    const val1 = primerNombre.value.trim();
                    const val2 = segundoNombre.value.trim();

                    if (val1 === '') {
                        segundoNombre.disabled = true;
                        if (segundoNombre.value !== '') {
                            segundoNombre.value = '';
                            segundoNombre.dispatchEvent(new Event('input', { bubbles: true }));
                        }

                        tercerNombre.disabled = true;
                        if (tercerNombre.value !== '') {
                            tercerNombre.value = '';
                            tercerNombre.dispatchEvent(new Event('input', { bubbles: true }));
                        }
                    } else {
                        segundoNombre.disabled = false;
                        if (val2 === '') {
                            tercerNombre.disabled = true;
                            if (tercerNombre.value !== '') {
                                tercerNombre.value = '';
                                tercerNombre.dispatchEvent(new Event('input', { bubbles: true }));
                            }
                        } else {
                            tercerNombre.disabled = false;
                        }
                    }
                }

                function actualizarApellidos() {
                    const val1 = primerApellido.value.trim();

                    if (val1 === '') {
                        segundoApellido.disabled = true;
                        if (segundoApellido.value !== '') {
                            segundoApellido.value = '';
                            segundoApellido.dispatchEvent(new Event('input', { bubbles: true }));
                        }
                    } else {
                        segundoApellido.disabled = false;
                    }
                }

                if (!primerNombre.dataset.cascadaVinculada) {
                    primerNombre.addEventListener('input', actualizarNombres);
                    segundoNombre.addEventListener('input', actualizarNombres);
                    primerApellido.addEventListener('input', actualizarApellidos);
                    primerNombre.dataset.cascadaVinculada = 'true';
                }

                // Habilitar campos antes del submit para que viajen los valores si fuera necesario
                const form = primerNombre.closest('form');
                if (form && !form.dataset.cascadaVinculada) {
                    form.addEventListener('submit', function () {
                        segundoNombre.disabled = false;
                        tercerNombre.disabled = false;
                        segundoApellido.disabled = false;
                    });
                    form.dataset.cascadaVinculada = 'true';
                }

                actualizarNombres();
                actualizarApellidos();
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', inicializarBloqueoCascada);
            } else {
                inicializarBloqueoCascada();
            }

            document.addEventListener('livewire:navigated', inicializarBloqueoCascada);
            document.addEventListener('livewire:initialized', function () {
                if (window.Livewire) {
                    Livewire.hook('commit', ({ succeed }) => {
                        succeed(() => {
                            setTimeout(inicializarBloqueoCascada, 30);
                        });
                    });
                }
            });
        })();
    </script>
