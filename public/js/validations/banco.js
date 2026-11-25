/**
 * Validaciones en tiempo real para el módulo de Bancos
 */

document.addEventListener('DOMContentLoaded', function() {
    // Verificar si el modal de crear está presente en la página
    const modalCrear = document.getElementById('modalCrear');
    let validacionesInicializadas = false;

    // Función para limpiar el formulario y errores
    function limpiarFormulario() {
        const form = document.getElementById('formCrearBanco');
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
    const form = document.getElementById('formCrearBanco');
    if (!form) return;

    // Elementos del formulario
    const codigoInput = document.getElementById('codigo_banco');
    const nombreInput = document.getElementById('nombre_banco');
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

    // Función para validar el formato del código de banco
    function validarFormatoCodigo(valor) {
        // Solo números, entre 1 y 4 dígitos
        const regex = /^\d{1,4}$/;
        return regex.test(valor);
    }

    // Función para verificar si el banco ya existe
    async function verificarBancoExistente(codigo, nombre) {
        try {
            // Usar una ruta absoluta para evitar problemas con la URL base
            const url = new URL('/admin/banco/verificar', window.location.origin);
            
            // Agregar parámetros a la URL
            if (codigo) url.searchParams.append('codigo', codigo);
            if (nombre) url.searchParams.append('nombre', nombre);
            
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
                return { existe: false, mensaje: '' }; // En caso de error, asumimos que no existe para no bloquear al usuario
            }
            
            return data;
        } catch (error) {
            console.error('Error al verificar el banco:', error);
            return { existe: false, mensaje: '' }; // En caso de error, asumimos que no existe para no bloquear al usuario
        }
    }

    // Validación en tiempo real para el campo de código
    if (codigoInput) {
        let timeoutId;
        let haHechoBlur = false;

        // Validar formato mientras se escribe
        codigoInput.addEventListener('input', function() {
            clearTimeout(timeoutId);
            const valor = this.value.trim();
            
            // Solo validar formato mientras se escribe, no si está vacío
            if (valor) {
                if (!validarFormatoCodigo(valor)) {
                    mostrarError(this, 'El código debe contener entre 1 y 4 dígitos numéricos');
                } else {
                    mostrarError(this, '');
                }
            }
            
            // Verificar duplicados después de un tiempo
            if (valor && validarFormatoCodigo(valor)) {
                timeoutId = setTimeout(async () => {
                    const resultado = await verificarBancoExistente(valor, null);
                    if (resultado.existe) {
                        mostrarError(this, resultado.mensaje || 'Ya existe un banco con este código');
                    } else {
                        mostrarError(this, '');
                    }
                }, 500);
            }
        });

        // Validar campo obligatorio solo cuando se hace blur
        codigoInput.addEventListener('blur', function() {
            haHechoBlur = true;
            const valor = this.value.trim();
            
            if (!valor) {
                mostrarError(this, 'El código es obligatorio');
            } else if (!validarFormatoCodigo(valor)) {
                mostrarError(this, 'El código debe contener entre 1 y 4 dígitos numéricos');
            } else {
                mostrarError(this, '');
            }
        });
    }

    // Validación en tiempo real para el campo de nombre
    if (nombreInput) {
        let timeoutId;
        let haHechoBlur = false;

        // Validar formato mientras se escribe (solo si hay contenido)
        nombreInput.addEventListener('input', function() {
            clearTimeout(timeoutId);
            const valor = this.value.trim();
            
            // Verificar duplicados después de un tiempo
            if (valor) {
                timeoutId = setTimeout(async () => {
                    const resultado = await verificarBancoExistente(null, valor);
                    if (resultado.existe) {
                        mostrarError(this, resultado.mensaje || 'Ya existe un banco con este nombre');
                    } else if (haHechoBlur) {
                        mostrarError(this, '');
                    }
                }, 500);
            }
        });

        // Validar campo obligatorio solo cuando se hace blur
        nombreInput.addEventListener('blur', function() {
            haHechoBlur = true;
            const valor = this.value.trim();
            
            if (!valor) {
                mostrarError(this, 'El nombre es obligatorio');
            } else {
                mostrarError(this, '');
            }
        });
    }

    // Validación al enviar el formulario
    form.addEventListener('submit', async function(event) {
        let esValido = true;
        
        // Validar código
        if (codigoInput) {
            const valor = codigoInput.value.trim();
            if (!valor) {
                mostrarError(codigoInput, 'El código es obligatorio');
                esValido = false;
            } else if (!validarFormatoCodigo(valor)) {
                mostrarError(codigoInput, 'El código debe contener entre 1 y 4 dígitos numéricos');
                esValido = false;
            } else {
                // Verificar si el código ya existe
                const resultado = await verificarBancoExistente(valor, null);
                if (resultado.existe) {
                    mostrarError(codigoInput, resultado.mensaje || 'Ya existe un banco con este código');
                    esValido = false;
                }
            }
        }
        
        // Validar nombre
        if (nombreInput) {
            const valor = nombreInput.value.trim();
            if (!valor) {
                mostrarError(nombreInput, 'El nombre es obligatorio');
                esValido = false;
            } else {
                // Verificar si el nombre ya existe
                const resultado = await verificarBancoExistente(null, valor);
                if (resultado.existe) {
                    mostrarError(nombreInput, resultado.mensaje || 'Ya existe un banco con este nombre');
                    esValido = false;
                }
            }
        }
        
        if (!esValido) {
            event.preventDefault();
            mostrarAlerta('Por favor, complete correctamente todos los campos requeridos.');
            
            // Desplazarse al primer error
            const primerError = form.querySelector('.is-invalid');
            if (primerError) {
                primerError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    });
}
