// URL para verificar cédula duplicada - será establecida desde la vista
if (typeof window.verificarCedulaUrl === 'undefined') {
    window.verificarCedulaUrl = null;
}

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('docenteForm');
    
    if (form) {
        // Add submit event listener
        form.addEventListener('submit', async function(event) {
            if (!(await validateForm())) {
                event.preventDefault();
            }
        });

        // Agregar validación en tiempo real
        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            // Validar al salir del campo
            input.addEventListener('blur', function(e) {
                if (input.required || input.value.trim() !== '') {
                    validateField(e);
                }
            });
            
            // Limpiar errores al escribir
            input.addEventListener('input', function(e) {
                if (input.value.trim() === '') {
                    validateField(e);
                } else {
                    clearError(e);
                }
            });
            
            // Validar selects al cambiar
            if (input.tagName === 'SELECT') {
                input.addEventListener('change', validateField);
            }
        });
    } else {
        console.warn('No se encontró el formulario con ID "docenteForm". Asegúrese de que el formulario tenga el ID correcto.');
    }
});

async function validateForm() {
    let isValid = true;
    const form = document.getElementById('docenteForm');
    let firstInvalidField = null;
    
    if (!form) {
        console.error('El formulario no existe. No se puede realizar la validación.');
        return false;
    }
    
    // Reset previous errors and invalid states
    form.querySelectorAll('.is-invalid').forEach(field => {
        field.classList.remove('is-invalid');
    });
    
    form.querySelectorAll('.invalid-feedback').forEach(error => {
        error.remove();
    });
    
    // Validate required fields
    const requiredFields = [
        { id: 'tipo_documento_id', name: 'Tipo de documento' },
        { id: 'numero_documento', name: 'Número de documento' },
        { id: 'primer_nombre', name: 'Primer nombre' },
        { id: 'primer_apellido', name: 'Primer apellido' },
        { id: 'genero', name: 'Género' },
        { id: 'fecha_nacimiento', name: 'Fecha de nacimiento' },
        { id: 'prefijo_id', name: 'Prefijo de teléfono' },
        { id: 'primer_telefono', name: 'Número de teléfono' },
        { id: 'dependencia', name: 'Dependencia' },
        { id: 'direccion', name: 'Dirección' }
    ];

    requiredFields.forEach(field => {
        if (!isValid) return; // Skip remaining validations if already invalid
        const element = document.getElementById(field.id);
        let isEmpty = false;

        if (!element) {
            console.error(`No se encontró el elemento con ID: ${field.id}`);
            return;
        }

        // Manejo especial para selects
        if (element.tagName === 'SELECT') {
            const selectedOption = element.options[element.selectedIndex];
            isEmpty = !selectedOption.value;
        } 
        // Manejo para checkboxes
        else if (element.type === 'checkbox') {
            isEmpty = !element.checked;
        } 
        // Manejo para otros tipos de inputs
        else {
            isEmpty = !element.value.trim();
        }

        if (isEmpty) {
            showError(field.id, `El campo ${field.name} es obligatorio`);
            if (!firstInvalidField) {
                firstInvalidField = element;
            }
            isValid = false;
        } else {
            // Limpiar error si el campo es válido
            clearError({ target: element });
        }
    }); 

    // Additional validations
    if (isValid) {
        // Validate document number format (6-8 dígitos, opcionalmente precedidos por V o E)
        const numeroDocumento = document.getElementById('numero_documento').value.trim();
        if (!/^[VE]?\d{6,8}$/i.test(numeroDocumento)) {
            showError('numero_documento', 'La cédula debe tener entre 6 y 8 dígitos');
            isValid = false;
        } else {
            // Verificar cédula duplicada de forma síncrona para bloquear el envío
            const cedulaValida = await verificarCedulaDuplicada(numeroDocumento);
            if (!cedulaValida) {
                isValid = false;
            }
        }

        // Validate phone number
        const telefono = document.getElementById('primer_telefono').value.trim();
        if (!/^\d{7,15}$/.test(telefono)) {
            showError('primer_telefono', 'El número de teléfono no es válido');
            isValid = false;
        }

        // Validate date of birth (must be at least 18 years ago)
        const fechaNacimiento = new Date(document.getElementById('fecha_nacimiento').value);
        const hoy = new Date();
        const fechaMinima = new Date();
        fechaMinima.setFullYear(hoy.getFullYear() - 18);
        
        if (fechaNacimiento >= hoy) {
            showError('fecha_nacimiento', 'La fecha de nacimiento no puede ser futura');
            isValid = false;
        } else if (fechaNacimiento > fechaMinima) {
            showError('fecha_nacimiento', 'Debes ser mayor de 18 años');
            isValid = false;
        }

        // Validar Dependencia
        const dependencia = document.getElementById('dependencia').value.trim();
        if (!dependencia) {
            showError('dependencia', 'La dependencia es obligatoria');
            isValid = false;
        }

        // Validar Dirección
        const direccion = document.getElementById('direccion').value.trim();
        if (!direccion) {
            showError('direccion', 'La dirección es obligatoria');
            isValid = false;
        }
    }

    if (!isValid && firstInvalidField) {
        // Scroll to the first invalid field
        firstInvalidField.scrollIntoView({ 
            behavior: 'smooth',
            block: 'center'
        });
        firstInvalidField.focus();
    } else if (isValid) {
        // If all validations pass, you can add additional logic here
        console.log('Formulario válido, enviando...');
    }

    return isValid;
}

function validateField(e) {
    const field = e.target;
    
    // Limpiar errores previos
    clearError({ target: field });
    
    // Si el campo no es requerido y está vacío, no validar
    if (!field.required && !field.value.trim()) return;
    
    let value;
    // Obtener la etiqueta del campo si existe
    let fieldName = '';
    const label = field.closest('.mb-3')?.querySelector('label');
    if (label) {
        // Eliminar íconos y espacios en blanco del texto de la etiqueta
        fieldName = label.textContent.replace(/\s*\n\s*/g, ' ').trim();
        // Si no se encuentra un texto válido en la etiqueta, usar el ID
        if (!fieldName) {
            fieldName = field.id.replace(/_/g, ' ');
        }
    } else {
        fieldName = field.id.replace(/_/g, ' ');
    }
    
    // Obtener el valor según el tipo de campo
    if (field.type === 'checkbox') {
        value = field.checked;
    } else if (field.tagName === 'SELECT') {
        const selectedOption = field.options[field.selectedIndex];
        value = selectedOption.value;
    } else {
        value = field.value.trim();
    }
    
    // Si el campo está vacío, mostrar error
    if (!value && value !== 0) {
        showError(field.id, `El campo ${fieldName} es obligatorio`);
    }
    
    // Validaciones específicas por campo
    let isValid = true;
    let errorMessage = '';
    
    switch(field.id) {
        case 'numero_documento':
            if (!/^[VE]?\d{6,8}$/i.test(value)) {
                errorMessage = 'La cédula debe tener entre 6 y 8 dígitos';
                isValid = false;
            } else {
                // Verificar cédula duplicada en tiempo real
                verificarCedulaDuplicada(value);
            }
            break;
            
        case 'primer_telefono':
            if (!/^\d{7,15}$/.test(value)) {
                errorMessage = 'El número de teléfono no es válido';
                isValid = false;
            }
            break;
            
        case 'fecha_nacimiento':
            const fechaNacimiento = new Date(value);
            const hoy = new Date();
            const fechaMinima = new Date();
            fechaMinima.setFullYear(hoy.getFullYear() - 18);
            
            if (fechaNacimiento >= hoy) {
                errorMessage = 'La fecha de nacimiento no puede ser futura';
                isValid = false;
            } else if (fechaNacimiento > fechaMinima) {
                errorMessage = 'Debes ser mayor de 18 años';
                isValid = false;
            }
            break;
            
        case 'tipo_documento_id':
            if (value === 'Seleccione') {
                errorMessage = 'El tipo de documento es obligatorio';
                isValid = false;
            }
            break;
            
        case 'prefijo_id':
            if (value === 'Seleccione') {
                errorMessage = 'El prefijo de teléfono es obligatorio';
                isValid = false;
            }
            break;
            
        case 'primer_nombre':
        case 'primer_apellido':
            if (/\d/.test(value)) {
                errorMessage = 'Este campo no puede contener números';
                isValid = false;
            }
            break;
            
        case 'segundo_nombre':
        case 'tercer_nombre':
        case 'segundo_apellido':
            // Solo validar si el campo tiene algún valor (no es obligatorio)
            if (value && /\d/.test(value)) {
                errorMessage = 'Este campo no puede contener números';
                isValid = false;
            }
            break;
    }
    
    if (!isValid) {
        showError(field.id, errorMessage);
    }
}

function showError(fieldId, message) {
    // Remove existing error message if any
    const field = document.getElementById(fieldId);
    if (!field) {
        console.error(`No se encontró el campo con ID: ${fieldId}`);
        return;
    }
    
    // Get the form group element
    const formGroup = field.closest('.form-group') || field.closest('.input-group') || field.closest('.mb-3') || field.parentNode;
    
    // Remove any existing error message
    const existingError = formGroup.querySelector('.invalid-feedback');
    if (existingError) {
        existingError.remove();
    }
    
    // Add error class to field
    field.classList.add('is-invalid');
    
    // Create and append error message
    const errorElement = document.createElement('div');
    errorElement.className = 'invalid-feedback d-block';
    errorElement.innerHTML = `<i class="fas fa-exclamation-circle me-1"></i>${message}`;
    
    // Insert after the field or at the end of the form group
    if (field.nextSibling) {
        formGroup.insertBefore(errorElement, field.nextSibling);
    } else {
        formGroup.appendChild(errorElement);
    }
    
// Ensure the field is visible
field.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function verificarCedulaDuplicada(cedula) {
return new Promise(async (resolve) => {
    try {
        if (!window.verificarCedulaUrl) {
            console.error('La URL de verificación de cédula no está configurada');
            resolve(true);
            return;
        }
        
        // Obtener el campo de cédula y sus datos
        const cedulaField = document.getElementById('numero_documento');
        const originalValue = cedulaField ? cedulaField.dataset.originalValue : null;
        const personaId = cedulaField ? cedulaField.dataset.personaId : null;
        
        // Si la cédula no ha cambiado, no validar duplicados
        if (originalValue && cedula === originalValue) {
            clearError({ target: cedulaField });
            resolve(true);
            return;
        }
        
        // Construir URL con parámetros
        let url = `${window.verificarCedulaUrl}?numero_documento=${cedula}`;
        if (personaId) {
            url += `&persona_id=${personaId}`;
        }
        
        const response = await fetch(url);
        
        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.message || 'Error al verificar la cédula');
        }

            const data = await response.json();
            const alertaExistente = document.getElementById('alerta-persona-encontrada');
            
            if (data.status === 'error') {
                if (alertaExistente) alertaExistente.classList.add('d-none');
                desbloquearCamposPersonales();
                showError('numero_documento', data.message);
                resolve(false);
            } else if (data.status === 'persona_existente') {
                clearError({ target: document.getElementById('numero_documento') });
                if (alertaExistente) {
                    alertaExistente.classList.remove('d-none');
                    alertaExistente.classList.add('d-flex');
                }
                autocompletarYBloquearCamposPersonales(data.persona);
                resolve(true);
            } else if (data.status === 'info') {
                if (alertaExistente) alertaExistente.classList.add('d-none');
                desbloquearCamposPersonales();
                console.log(data.message);
                clearError({ target: document.getElementById('numero_documento') });
                resolve(true);
            } else {
                // Cédula disponible
                if (alertaExistente) alertaExistente.classList.add('d-none');
                desbloquearCamposPersonales();
                clearError({ target: document.getElementById('numero_documento') });
                resolve(true);
            }
        } catch (error) {
            console.error('Error al verificar cédula:', error);
            showError('numero_documento', error.message || 'Error al verificar la cédula');
            resolve(false);
        }
    });
}

function autocompletarYBloquearCamposPersonales(persona) {
    if (!persona) return;

    const campos = [
        { id: 'primer_nombre', val: persona.primer_nombre },
        { id: 'segundo_nombre', val: persona.segundo_nombre },
        { id: 'tercer_nombre', val: persona.tercer_nombre },
        { id: 'primer_apellido', val: persona.primer_apellido },
        { id: 'segundo_apellido', val: persona.segundo_apellido },
        { id: 'fecha_nacimiento', val: persona.fecha_nacimiento },
        { id: 'direccion', val: persona.direccion },
        { id: 'correo', val: persona.email },
        { id: 'primer_telefono', val: persona.telefono },
        { id: 'telefono_dos', val: persona.telefono_dos }
    ];

    campos.forEach(c => {
        const el = document.getElementById(c.id) || document.querySelector(`[name="${c.id}"]`);
        if (el) {
            if (c.val !== undefined && c.val !== null && c.val !== '') {
                el.value = c.val;
            }
            el.readOnly = true;
            el.classList.add('bg-light');
        }
    });

    if (persona.genero_id) {
        const genSelect = document.getElementById('genero');
        if (genSelect) {
            genSelect.value = persona.genero_id;
            genSelect.style.pointerEvents = 'none';
            genSelect.classList.add('bg-light');
        }
    }

    if (persona.tipo_documento_id) {
        const tipoDocSelect = document.getElementById('tipo_documento_id');
        if (tipoDocSelect) {
            tipoDocSelect.value = persona.tipo_documento_id;
            tipoDocSelect.style.pointerEvents = 'none';
            tipoDocSelect.classList.add('bg-light');
        }
    }

    if (persona.prefijo_id) {
        const prefSelect = document.getElementById('prefijo_id');
        if (prefSelect) {
            prefSelect.value = persona.prefijo_id;
            prefSelect.style.pointerEvents = 'none';
            prefSelect.classList.add('bg-light');
        }
    }
}

function desbloquearCamposPersonales() {
    const camposIds = [
        'primer_nombre',
        'segundo_nombre',
        'tercer_nombre',
        'primer_apellido',
        'segundo_apellido',
        'fecha_nacimiento',
        'direccion',
        'correo',
        'primer_telefono',
        'telefono_dos'
    ];

    camposIds.forEach(id => {
        const el = document.getElementById(id) || document.querySelector(`[name="${id}"]`);
        if (el && el.readOnly) {
            el.readOnly = false;
            el.classList.remove('bg-light');
        }
    });

    ['genero', 'tipo_documento_id', 'prefijo_id'].forEach(id => {
        const select = document.getElementById(id);
        if (select) {
            select.style.pointerEvents = '';
            select.classList.remove('bg-light');
        }
    });
}

function clearError(e) {
    const field = e.target;
    field.classList.remove('is-invalid');
    
    const formGroup = field.closest('.form-group') || field.closest('.input-group') || field.closest('.mb-3') || field.parentNode;
    const errorMessage = formGroup.querySelector('.invalid-feedback');
    if (errorMessage) {
        errorMessage.remove();
    }
}