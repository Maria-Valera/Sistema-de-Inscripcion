/**
 * Validaciones en tiempo real para el módulo de Prefijo de Teléfono
 */

document.addEventListener('DOMContentLoaded', function() {
    // Verificar si el modal de crear está presente en la página
    const modalCrear = document.getElementById('modalCrear');
    let validacionesInicializadas = false;

    // Función para limpiar el formulario y errores
    function limpiarFormulario() {
        const form = document.getElementById('formCrearPrefijo');
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
        modalCrear.addEventListener('hidden.bs.modal', limpiarFormulario);
    }

    // Inicializar validaciones si el modal ya está abierto (en caso de recarga de página)
    if (modalCrear && modalCrear.classList.contains('show')) {
        inicializarValidacionesCrear();
        validacionesInicializadas = true;
    }
});

/**
 * Inicializa las validaciones para el formulario de creación
 */
function inicializarValidacionesCrear() {
    const form = document.getElementById('formCrearPrefijo');
    if (!form) return;

    // Elementos del formulario
    const prefijoInput = document.getElementById('prefijo');
    const contenedorAlerta = document.getElementById('contenedorAlertaCrear');

    // Función para mostrar mensajes de error
    function mostrarError(elemento, mensaje) {
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
        if (!contenedorAlerta) return;
        
        contenedorAlerta.innerHTML = `
            <div class="alert alert-${tipo} alert-dismissible fade show" role="alert">
                ${mensaje}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        `;
    }

    // Función para validar el formato del prefijo
    function validarFormatoPrefijo(valor) {
        // Solo números, entre 1 y 4 dígitos
        const regex = /^\d{1,4}$/;
        return regex.test(valor);
    }

    // Función para verificar si el prefijo ya existe
    async function verificarPrefijoExistente(prefijo) {
        try {
            // Usar la ruta correcta con guión bajo
            const url = new URL('/admin/prefijo_telefono/verificar', window.location.origin);
            url.searchParams.append('prefijo', prefijo);
            
            console.log('Solicitando URL:', url.toString()); // Para depuración
            
            const response = await fetch(url.toString());
            if (!response.ok) {
                const errorData = await response.json().catch(() => ({}));
                console.error('Error en la respuesta:', response.status, errorData);
                throw new Error(`Error HTTP: ${response.status}`);
            }
            
            const data = await response.json();
            console.log('Respuesta del servidor:', data); // Para depuración
            
            // Verificar si la respuesta es exitosa
            if (data.success === false) {
                console.error('Error del servidor:', data.message);
                return false; // En caso de error, asumimos que no existe para no bloquear al usuario
            }
            
            return data.existe;
        } catch (error) {
            console.error('Error al verificar el prefijo:', error);
            return false; // En caso de error, asumimos que no existe para no bloquear al usuario
        }
    }

    // Validación en tiempo real para el campo de prefijo
    if (prefijoInput) {
        let timeoutId;
        let haHechoBlur = false;

        // Validar formato mientras se escribe
        prefijoInput.addEventListener('input', function() {
            clearTimeout(timeoutId);
            const valor = this.value.trim();
            
            // Solo validar formato mientras se escribe, no si está vacío
            if (valor) {
                if (!validarFormatoPrefijo(valor)) {
                    mostrarError(this, 'El prefijo debe contener entre 1 y 4 dígitos numéricos');
                } else {
                    mostrarError(this, '');
                }
            } else {
                mostrarError(this, '');
            }
            
            // Verificar duplicados después de un tiempo
            if (valor && validarFormatoPrefijo(valor)) {
                timeoutId = setTimeout(async () => {
                    const existe = await verificarPrefijoExistente(valor);
                    if (existe) {
                        mostrarError(this, 'Ya existe un prefijo con este número');
                    } else if (haHechoBlur) {
                        mostrarError(this, '');
                    }
                }, 500);
            }
        });

        // Validar campo obligatorio solo cuando se hace blur
        prefijoInput.addEventListener('blur', function() {
            haHechoBlur = true;
            const valor = this.value.trim();
            
            if (!valor) {
                mostrarError(this, 'El prefijo es obligatorio');
            } else if (!validarFormatoPrefijo(valor)) {
                mostrarError(this, 'El prefijo debe contener entre 1 y 4 dígitos numéricos');
            } else {
                // Verificar duplicados al salir del campo
                verificarPrefijoExistente(valor).then(existe => {
                    if (existe) {
                        mostrarError(this, 'Ya existe un prefijo con este número');
                    } else {
                        mostrarError(this, '');
                    }
                });
            }
        });
    }

    // Validación al enviar el formulario
    form.addEventListener('submit', async function(event) {
        event.preventDefault();
        let esValido = true;
        
        // Validar prefijo
        if (prefijoInput) {
            const valor = prefijoInput.value.trim();
            if (!valor) {
                mostrarError(prefijoInput, 'El prefijo es obligatorio');
                esValido = false;
            } else if (!validarFormatoPrefijo(valor)) {
                mostrarError(prefijoInput, 'El prefijo debe contener entre 1 y 4 dígitos numéricos');
                esValido = false;
            } else {
                // Verificar si el prefijo ya existe
                const existe = await verificarPrefijoExistente(valor);
                if (existe) {
                    mostrarError(prefijoInput, 'Ya existe un prefijo con este número');
                    esValido = false;
                }
            }
        }
        
        // Si hay errores, mostrar mensaje y prevenir el envío
        if (!esValido) {
            mostrarAlerta('Por favor, complete el formulario correctamente.');
            return false;
        }
        
        // Si todo está bien, enviar el formulario
        this.submit();
        return true;
    });
}
