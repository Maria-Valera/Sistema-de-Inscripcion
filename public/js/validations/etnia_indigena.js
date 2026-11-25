/**
 * Validaciones en tiempo real para el módulo de Etnias Indígenas
 */

console.log('Cargando validaciones para etnia_indigena.js');

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM completamente cargado, inicializando validaciones...');
    // Verificar si el modal de crear está presente en la página
    const modalCrear = document.getElementById('modalCrear');
    console.log('Modal de crear:', modalCrear);
    let validacionesInicializadas = false;

    // Función para limpiar el formulario y errores
    function limpiarFormulario() {
        const form = document.getElementById('formCrear');
        if (form) {
            form.reset();
            // Limpiar errores
            const errores = form.querySelectorAll('.error-validacion, .is-invalid, .invalid-feedback, .server-error');
            errores.forEach(error => {
                if (error.classList.contains('is-invalid')) {
                    error.classList.remove('is-invalid');
                } else {
                    error.remove();
                }
            });
            // Limpiar alertas
            const contenedorAlerta = document.getElementById('contenedorAlertaCrear');
            if (contenedorAlerta) {
                contenedorAlerta.innerHTML = '';
            }
        }
    }

    if (modalCrear) {
        // Limpiar e inicializar cuando el modal va a mostrarse
        modalCrear.addEventListener('show.bs.modal', function() {
            limpiarFormulario();
            if (!validacionesInicializadas) {
                inicializarValidacionesCrear();
                validacionesInicializadas = true;
            }
        });

        // Inicializar validaciones cuando el modal se muestra
        modalCrear.addEventListener('shown.bs.modal', function() {
            limpiarFormulario();
            if (!validacionesInicializadas) {
                inicializarValidacionesCrear();
                validacionesInicializadas = true;
            }
        });

        // Limpiar el formulario cuando se oculte el modal
        modalCrear.addEventListener('hidden.bs.modal', function() {
            limpiarFormulario();
        });
    }

    // Inicializar validaciones si el modal ya está abierto (en caso de recarga de página)
    if (modalCrear && modalCrear.classList.contains('show')) {
        console.log('Modal ya está abierto, inicializando validaciones...');
        inicializarValidacionesCrear();
        validacionesInicializadas = true;
    }
});

/**
 * Inicializa las validaciones para el formulario de creación
 */
function inicializarValidacionesCrear() {
    console.log('Inicializando validaciones de creación...');
    const form = document.getElementById('formCrear');
    console.log('Formulario encontrado:', form);
    if (!form) {
        console.error('No se encontró el formulario con ID formCrear');
        return;
    }

    // Elementos del formulario
    const nombreInput = document.getElementById('nombre');
    const contenedorAlerta = document.getElementById('contenedorAlertaCrear');

    console.log('Elementos del formulario:', {
        nombreInput,
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
        if (!contenedorAlerta) {
            console.error('No se encontró el contenedor de alertas');
            return;
        }
        
        contenedorAlerta.innerHTML = `
            <div class="alert alert-${tipo} alert-dismissible fade show" role="alert">
                ${mensaje}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        `;
    }

    // Función para validar el formato del nombre de la etnia indígena
    function validarFormatoNombre(valor) {
        return /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/.test(valor) && valor.trim().length > 0 && valor.trim().length <= 50;
    }

    // Función para verificar si la etnia indígena ya existe
    async function verificarEtniaIndigenaExistente(nombre) {
        try {
            const url = new URL('/admin/etnia_indigena/verificar', window.location.origin);
            url.searchParams.append('nombre', nombre);
            
            console.log('Solicitando URL:', url.toString());
            
            const response = await fetch(url.toString());
            if (!response.ok) {
                const errorData = await response.json().catch(() => ({}));
                console.error('Error en la respuesta:', response.status, errorData);
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
            console.error('Error al verificar la etnia indígena:', error);
            return false; // En caso de error, asumimos que no existe para no bloquear al usuario
        }
    }

    // Validación en tiempo real para el campo de nombre
    if (nombreInput) {
        let timeoutId;
        let haHechoBlur = false;

        // Validar formato mientras se escribe
        nombreInput.addEventListener('input', function() {
            console.log('Evento input en nombre:', this.value);
            clearTimeout(timeoutId);
            const valor = this.value.trim();
            
            // Solo validar formato mientras se escribe, no si está vacío
            if (valor) {
                if (!validarFormatoNombre(valor)) {
                    mostrarError(this, 'Solo se permiten letras y espacios, máximo 50 caracteres');
                } else {
                    mostrarError(this, '');
                    
                    // Verificar duplicados después de un tiempo
                    timeoutId = setTimeout(async () => {
                        console.log('Verificando duplicados para:', valor);
                        const existe = await verificarEtniaIndigenaExistente(valor);
                        console.log('Resultado de verificación:', existe);
                        if (existe) {
                            mostrarError(this, 'Ya existe una etnia indígena con este nombre');
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
        nombreInput.addEventListener('blur', function() {
            console.log('Evento blur en nombre:', this.value);
            haHechoBlur = true;
            const valor = this.value.trim();
            
            if (!valor) {
                mostrarError(this, 'El nombre de la etnia indígena es obligatorio');
            } else if (!validarFormatoNombre(valor)) {
                mostrarError(this, 'Solo se permiten letras y espacios, máximo 50 caracteres');
            } else {
                // Verificar duplicados al salir del campo
                verificarEtniaIndigenaExistente(valor).then(existe => {
                    console.log('Verificación después de blur:', existe);
                    if (existe) {
                        mostrarError(this, 'Ya existe una etnia indígena con este nombre');
                    } else {
                        mostrarError(this, '');
                    }
                });
            }
        });
    }

    // Validación al enviar el formulario
    form.addEventListener('submit', async function(event) {
        console.log('Enviando formulario...');
        event.preventDefault();
        let esValido = true;
        
        // Validar nombre
        if (nombreInput) {
            const valor = nombreInput.value.trim();
            if (!valor) {
                mostrarError(nombreInput, 'El nombre de la etnia indígena es obligatorio');
                esValido = false;
            } else if (!validarFormatoNombre(valor)) {
                mostrarError(nombreInput, 'Solo se permiten letras y espacios, máximo 50 caracteres');
                esValido = false;
            } else {
                // Verificar si la etnia indígena ya existe
                console.log('Verificando duplicados antes de enviar...');
                const existe = await verificarEtniaIndigenaExistente(valor);
                console.log('Resultado de verificación antes de enviar:', existe);
                if (existe) {
                    mostrarError(nombreInput, 'Ya existe una etnia indígena con este nombre');
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
        // Si todo está bien, enviar el formulario
        this.submit();
        return true;
    });

    console.log('Validaciones inicializadas correctamente');
}
