/**
 * Validaciones en tiempo real para el módulo de Grados
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('Cargando validaciones para nivel academico...');
    
    // Inicializar validaciones cuando el DOM esté completamente cargado
    const modalGrado = document.getElementById('modalCrearGrado');
    if (modalGrado) {
        console.log('Modal de nivel academico encontrado, inicializando validaciones...');
        inicializarValidacionesGrado();
    } else {
        console.log('Modal de nivel academico no encontrado');
    }
});

/**
 * Inicializa las validaciones para el formulario de grados
 */
function inicializarValidacionesGrado() {
    console.log('Inicializando validaciones para nivel academico...');
    const form = document.getElementById('formCrearGrado');
    
    if (!form) {
        console.error('No se encontró el formulario con ID formCrearGrado');
        return;
    }

    // Elementos del formulario
    const numeroGradoInput = document.getElementById('numero_grado');
    const capacidadMaxInput = document.getElementById('capacidad_max');
    const minSeccionInput = document.getElementById('min_seccion');
    const maxSeccionInput = document.getElementById('max_seccion');
    const contenedorAlerta = document.getElementById('contenedorAlertaCrear');

    console.log('Elementos del formulario:', {
        numeroGradoInput,
        capacidadMaxInput,
        minSeccionInput,
        maxSeccionInput,
        contenedorAlerta
    });

    // Función para mostrar mensajes de error
    function mostrarError(elemento, mensaje) {
        console.log(`Mostrando error para ${elemento.id}:`, mensaje);
        // Eliminar mensajes de error existentes
        const errorExistente = elemento.parentElement.querySelector('.error-validacion');
        if (errorExistente) {
            errorExistente.remove();
        }

        if (mensaje) {
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-validacion text-danger mt-1 small';
            errorDiv.textContent = mensaje;
            elemento.parentElement.appendChild(errorDiv);
            elemento.classList.add('is-invalid');
        } else {
            elemento.classList.remove('is-invalid');
        }
    }

    // Función para mostrar alerta general
    function mostrarAlerta(mensaje, tipo = 'danger') {
        console.log(`Mostrando alerta (${tipo}):`, mensaje);
        if (contenedorAlerta) {
            contenedorAlerta.innerHTML = `
                <div class="alert alert-${tipo} alert-dismissible fade show mb-3" role="alert">
                    ${mensaje}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
            `;
        }
    }

    // Función para validar el formato del número de grado
    function validarNumeroGrado(valor) {
        const num = parseInt(valor, 10);
        return !isNaN(num) && num > 0 && num <= 9999; // Máximo 4 dígitos
    }

    // Función para validar capacidad máxima
    function validarCapacidadMaxima(valor) {
        const num = parseInt(valor, 10);
        return !isNaN(num) && num > 0 && num <= 999; // Máximo 3 dígitos
    }

    // Función para validar sección
    function validarSeccion(valor) {
        const num = parseInt(valor, 10);
        return !isNaN(num) && num > 0 && num <= 99; // Máximo 2 dígitos
    }

    // Función para verificar si el grado ya existe
    async function verificarGradoExistente(numeroGrado) {
        try {
            const url = new URL('/admin/grado/verificar', window.location.origin);
            url.searchParams.append('numero_grado', numeroGrado);
            
            console.log('Solicitando URL:', url.toString());
            
            const response = await fetch(url.toString());
            if (!response.ok) {
                throw new Error(`Error HTTP: ${response.status}`);
            }
            
            const data = await response.json();
            console.log('Respuesta del servidor:', data);
            
            // Verificar si la respuesta es exitosa
            if (data.success === false) {
                console.error('Error del servidor:', data.message);
                return false; // En caso de error, asumimos que no existe para no bloquear al usuario
            }
            
            return data.existe;
        } catch (error) {
            console.error('Error al verificar el nivel academico:', error);
            return false; // En caso de error, asumimos que no existe para no bloquear al usuario
        }
    }

    // Validación en tiempo real para el campo de número de grado
    if (numeroGradoInput) {
        let timeoutId;
        let haHechoBlur = false;

        // Validar formato mientras se escribe
        numeroGradoInput.addEventListener('input', function() {
            console.log('Evento input en número de nivel academico:', this.value);
            clearTimeout(timeoutId);
            const valor = this.value.trim();
            
            // Solo validar formato mientras se escribe, no si está vacío
            if (valor) {
                if (!validarNumeroGrado(valor)) {
                    mostrarError(this, 'El número de nivel academico debe ser un número entre 1 y 9999');
                } else {
                    mostrarError(this, '');
                    
                    // Verificar duplicados después de un tiempo
                    timeoutId = setTimeout(async () => {
                        console.log('Verificando duplicados para:', valor);
                        const existe = await verificarGradoExistente(valor);
                        console.log('Resultado de verificación:', existe);
                        if (existe) {
                            mostrarError(this, 'Ya existe un nivel academico con este número');
                        } else if (haHechoBlur) {
                            mostrarError(this, '');
                        }
                    }, 500);
                }
            } else {
                mostrarError(this, '');
            }
        });

        // Validar campo obligatorio solo cuando se hace blur
        numeroGradoInput.addEventListener('blur', function() {
            console.log('Evento blur en número de nivel academico:', this.value);
            haHechoBlur = true;
            const valor = this.value.trim();
            
            if (!valor) {
                mostrarError(this, 'El número de nivel academico es obligatorio');
            } else if (!validarNumeroGrado(valor)) {
                mostrarError(this, 'El número de nivel academico debe ser un número entre 1 y 9999');
            } else {
                // Verificar duplicados al salir del campo
                verificarGradoExistente(valor).then(existe => {
                    console.log('Verificación después de blur:', existe);
                    if (existe) {
                        mostrarError(this, 'Ya existe un nivel academico con este número');
                    } else {
                        mostrarError(this, '');
                    }
                });
            }
        });
    }

    // Validación para capacidad máxima
    if (capacidadMaxInput) {
        capacidadMaxInput.addEventListener('blur', function() {
            const valor = this.value.trim();
            if (!valor) {
                mostrarError(this, 'La capacidad máxima es obligatoria');
            } else if (!validarCapacidadMaxima(valor)) {
                mostrarError(this, 'La capacidad debe ser un número entre 1 y 999');
            } else {
                mostrarError(this, '');
            }
        });
    }

    // Validación para mínimo de sección
    if (minSeccionInput) {
        minSeccionInput.addEventListener('blur', function() {
            const valor = this.value.trim();
            if (!valor) {
                mostrarError(this, 'El mínimo de sección es obligatorio');
            } else if (!validarSeccion(valor)) {
                mostrarError(this, 'El valor debe ser un número entre 1 y 99');
            } else {
                mostrarError(this, '');
            }
        });
    }

    // Validación para máximo de sección
    if (maxSeccionInput) {
        maxSeccionInput.addEventListener('blur', function() {
            const valor = this.value.trim();
            if (!valor) {
                mostrarError(this, 'El máximo de sección es obligatorio');
            } else if (!validarSeccion(valor)) {
                mostrarError(this, 'El valor debe ser un número entre 1 y 99');
            } else {
                // Verificar que el máximo sea mayor o igual que el mínimo
                const minSeccion = minSeccionInput ? parseInt(minSeccionInput.value.trim() || '0', 10) : 0;
                if (parseInt(valor, 10) < minSeccion) {
                    mostrarError(this, 'El máximo de sección no puede ser menor que el mínimo');
                } else {
                    mostrarError(this, '');
                }
            }
        });
    }

    // Validación al enviar el formulario
    form.addEventListener('submit', async function(event) {
        console.log('Enviando formulario de grado...');
        event.preventDefault();
        let esValido = true;
        
        // Validar número de grado
        if (numeroGradoInput) {
            const valor = numeroGradoInput.value.trim();
            if (!valor) {
                mostrarError(numeroGradoInput, 'El número de nivel academico es obligatorio');
                esValido = false;
            } else if (!validarNumeroGrado(valor)) {
                mostrarError(numeroGradoInput, 'El número de nivel academico debe ser un número entre 1 y 9999');
                esValido = false;
            } else {
                // Verificar si el grado ya existe
                console.log('Verificando duplicados antes de enviar...');
                const existe = await verificarGradoExistente(valor);
                console.log('Resultado de verificación antes de enviar:', existe);
                if (existe) {
                    mostrarError(numeroGradoInput, 'Ya existe un nivel academico con este número');
                    esValido = false;
                }
            }
        }
        
        // Validar capacidad máxima
        if (capacidadMaxInput) {
            const valor = capacidadMaxInput.value.trim();
            if (!valor) {
                mostrarError(capacidadMaxInput, 'La capacidad máxima es obligatoria');
                esValido = false;
            } else if (!validarCapacidadMaxima(valor)) {
                mostrarError(capacidadMaxInput, 'La capacidad debe ser un número entre 1 y 999');
                esValido = false;
            }
        }
        
        // Validar mínimo de sección
        if (minSeccionInput) {
            const valor = minSeccionInput.value.trim();
            if (!valor) {
                mostrarError(minSeccionInput, 'El mínimo de sección es obligatorio');
                esValido = false;
            } else if (!validarSeccion(valor)) {
                mostrarError(minSeccionInput, 'El valor debe ser un número entre 1 y 99');
                esValido = false;
            }
        }
        
        // Validar máximo de sección
        if (maxSeccionInput) {
            const valor = maxSeccionInput.value.trim();
            const minSeccion = minSeccionInput ? parseInt(minSeccionInput.value.trim() || '0', 10) : 0;
            
            if (!valor) {
                mostrarError(maxSeccionInput, 'El máximo de sección es obligatorio');
                esValido = false;
            } else if (!validarSeccion(valor)) {
                mostrarError(maxSeccionInput, 'El valor debe ser un número entre 1 y 99');
                esValido = false;
            } else if (parseInt(valor, 10) < minSeccion) {
                mostrarError(maxSeccionInput, 'El máximo de sección no puede ser menor que el mínimo');
                esValido = false;
            }
        }
        
        // Si hay errores, mostrar mensaje y prevenir el envío
        if (!esValido) {
            console.log('Formulario no válido, mostrando alerta...');
            mostrarAlerta('Por favor, complete el formulario correctamente.');
            return false;
        }
        
        console.log('Formulario válido, enviando...');
        // Si todo está bien, enviar el formulario
        this.submit();
        return true;
    });

    // Limpiar el formulario cuando se abra o se cierre el modal
    const modal = document.getElementById('modalCrearGrado');
    if (modal) {
        function limpiarFormularioCrear() {
            console.log('Limpiando formulario de nivel academico...');
            form.reset();
            const errores = form.querySelectorAll('.error-validacion, .is-invalid, .invalid-feedback, .server-error');
            errores.forEach(error => {
                if (error.classList.contains('is-invalid')) {
                    error.classList.remove('is-invalid');
                } else {
                    error.remove();
                }
            });
            if (contenedorAlerta) {
                contenedorAlerta.innerHTML = '';
            }
        }

        modal.addEventListener('show.bs.modal', limpiarFormularioCrear);
        modal.addEventListener('hidden.bs.modal', limpiarFormularioCrear);
    }

    console.log('Validaciones de nivel academico inicializadas correctamente');
}
