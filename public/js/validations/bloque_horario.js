/**
 * Validaciones en tiempo real para el módulo de Bloques Horarios
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('Cargando validaciones para bloque horario...');
    
    // Inicializar validaciones cuando el DOM esté completamente cargado
    const modalBloqueHorario = document.getElementById('modalCrearBloqueHorario');
    if (modalBloqueHorario) {
        console.log('Modal de bloque horario encontrado, inicializando validaciones...');
        inicializarValidacionesBloqueHorario();
    } else {
        console.log('Modal de bloque horario no encontrado');
    }
});

/**
 * Inicializa las validaciones para el formulario de bloques horarios
 */
function inicializarValidacionesBloqueHorario() {
    console.log('Inicializando validaciones para bloque horario...');
    const form = document.getElementById('formCrearBloqueHorario');
    
    if (!form) {
        console.error('No se encontró el formulario con ID formCrearBloqueHorario');
        return;
    }

    // Elementos del formulario
    const horaInicioInput = document.getElementById('hora_inicio');
    const horaFinInput = document.getElementById('hora_fin');
    const contenedorAlerta = document.getElementById('contenedorAlertaCrear');

    console.log('Elementos del formulario:', {
        horaInicioInput,
        horaFinInput,
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

    // Función para validar formato de hora
    function validarHora(valor) {
        const regex = /^([01]?[0-9]|2[0-3]):[0-5][0-9]$/;
        return regex.test(valor);
    }

    // Función para verificar si el bloque horario ya existe
    async function verificarBloqueHorarioExistente(horaInicio, horaFin) {
        try {
            const url = new URL('/admin/bloque_horario/verificar', window.location.origin);
            url.searchParams.append('hora_inicio', horaInicio);
            url.searchParams.append('hora_fin', horaFin);
            
            console.log('Solicitando URL:', url.toString());
            
            const response = await fetch(url.toString());
            if (!response.ok) {
                throw new Error(`Error HTTP: ${response.status}`);
            }
            
            const data = await response.json();
            console.log('Respuesta del servidor:', data);
            
            if (data.success === false) {
                console.error('Error del servidor:', data.message);
                return false;
            }
            
            return data.existe;
        } catch (error) {
            console.error('Error al verificar el bloque horario:', error);
            return false;
        }
    }

    // Validación en tiempo real para hora inicio
    if (horaInicioInput) {
        let timeoutId;
        let haHechoBlur = false;

        horaInicioInput.addEventListener('input', function() {
            console.log('Evento input en hora inicio:', this.value);
            clearTimeout(timeoutId);
            const valor = this.value.trim();
            
            if (valor) {
                if (!validarHora(valor)) {
                    mostrarError(this, 'Formato de hora inválido (use HH:MM)');
                } else {
                    mostrarError(this, '');
                }
            } else {
                mostrarError(this, '');
            }
        });

        horaInicioInput.addEventListener('blur', function() {
            console.log('Evento blur en hora inicio:', this.value);
            haHechoBlur = true;
            const valor = this.value.trim();
            
            if (!valor) {
                mostrarError(this, 'La hora de inicio es obligatoria');
            } else if (!validarHora(valor)) {
                mostrarError(this, 'Formato de hora inválido (use HH:MM)');
            } else {
                mostrarError(this, '');
            }
        });
    }

    // Validación para hora fin
    if (horaFinInput) {
        horaFinInput.addEventListener('input', function() {
            const valor = this.value.trim();
            const horaInicio = horaInicioInput ? horaInicioInput.value.trim() : '';
            
            if (valor) {
                if (!validarHora(valor)) {
                    mostrarError(this, 'Formato de hora inválido (use HH:MM)');
                } else if (horaInicio && valor <= horaInicio) {
                    mostrarError(this, 'La hora fin debe ser posterior a la hora inicio');
                } else {
                    mostrarError(this, '');
                }
            } else {
                mostrarError(this, '');
            }
        });

        horaFinInput.addEventListener('blur', function() {
            const valor = this.value.trim();
            const horaInicio = horaInicioInput ? horaInicioInput.value.trim() : '';
            
            if (!valor) {
                mostrarError(this, 'La hora fin es obligatoria');
            } else if (!validarHora(valor)) {
                mostrarError(this, 'Formato de hora inválido (use HH:MM)');
            } else if (horaInicio && valor <= horaInicio) {
                mostrarError(this, 'La hora fin debe ser posterior a la hora inicio');
            } else {
                mostrarError(this, '');
            }
        });
    }

    // Validación al enviar el formulario
    form.addEventListener('submit', async function(event) {
        console.log('Enviando formulario de bloque horario...');
        event.preventDefault();
        let esValido = true;
        
        // Validar hora inicio
        if (horaInicioInput) {
            const valor = horaInicioInput.value.trim();
            if (!valor) {
                mostrarError(horaInicioInput, 'La hora de inicio es obligatoria');
                esValido = false;
            } else if (!validarHora(valor)) {
                mostrarError(horaInicioInput, 'Formato de hora inválido (use HH:MM)');
                esValido = false;
            }
        }
        
        // Validar hora fin
        if (horaFinInput) {
            const valor = horaFinInput.value.trim();
            const horaInicio = horaInicioInput ? horaInicioInput.value.trim() : '';
            
            if (!valor) {
                mostrarError(horaFinInput, 'La hora fin es obligatoria');
                esValido = false;
            } else if (!validarHora(valor)) {
                mostrarError(horaFinInput, 'Formato de hora inválido (use HH:MM)');
                esValido = false;
            } else if (horaInicio && valor <= horaInicio) {
                mostrarError(horaFinInput, 'La hora fin debe ser posterior a la hora inicio');
                esValido = false;
            } else {
                // Verificar si el bloque horario ya existe
                console.log('Verificando duplicados antes de enviar...');
                const existe = await verificarBloqueHorarioExistente(horaInicio, valor);
                console.log('Resultado de verificación antes de enviar:', existe);
                if (existe) {
                    mostrarError(horaFinInput, 'Ya existe un bloque horario con este horario');
                    esValido = false;
                }
            }
        }
        
        // Si hay errores, mostrar mensaje y prevenir el envío
        if (!esValido) {
            console.log('Formulario no válido, mostrando alerta...');
            mostrarAlerta('Por favor, complete el formulario correctamente.');
            return false;
        }
        
        console.log('Formulario válido, enviando...');
        this.submit();
        return true;
    });

    // Limpiar el formulario cuando se cierre el modal
    const modal = document.getElementById('modalCrearBloqueHorario');
    if (modal) {
        modal.addEventListener('hidden.bs.modal', function() {
            console.log('Modal de bloque horario cerrado, limpiando formulario...');
            form.reset();
            const errores = form.querySelectorAll('.error-validacion, .is-invalid');
            errores.forEach(error => {
                if (error.classList.contains('error-validacion')) {
                    error.remove();
                } else {
                    error.classList.remove('is-invalid');
                }
            });
            if (contenedorAlerta) {
                contenedorAlerta.innerHTML = '';
            }
        });
    }

    console.log('Validaciones de bloque horario inicializadas correctamente');
}
