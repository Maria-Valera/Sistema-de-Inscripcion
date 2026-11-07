{{--
    Motor de validación en tiempo real de M4 y M5 (también lo usa admin/cortes/index).
    Cada pantalla llama a ValidacionTiempoReal(form, revisar), donde revisar(form) devuelve
    { campo: 'mensaje' } con los errores encontrados. El servidor vuelve a validar al guardar.
--}}
<script>
    window.ValidacionTiempoReal = function (form, revisar) {
        form.setAttribute('novalidate', 'novalidate'); // sin los avisos del navegador (salen en su idioma)
        const campos = () => form.querySelectorAll('input:not([type="hidden"]), select');

        function mostrar(campo, mensaje) {
            let aviso = campo.parentElement.querySelector('.mensaje-error');
            if (!aviso) {
                aviso = document.createElement('small');
                aviso.className = 'mensaje-error';
                campo.insertAdjacentElement('afterend', aviso);
            }
            aviso.textContent = mensaje || '';
            campo.classList.toggle('campo-invalido', !!mensaje);
        }

        function validar(soloTocados) {
            const errores = revisar(form);
            let primero = null;
            campos().forEach((campo) => {
                const mensaje = errores[campo.name] || '';
                if (!soloTocados || campo.dataset.tocado) mostrar(campo, mensaje);
                if (mensaje && !primero) primero = campo;
            });
            return primero;
        }

        campos().forEach((campo) => {
            ['input', 'change'].forEach((evento) => campo.addEventListener(evento, () => {
                campo.dataset.tocado = '1';
                // Las fechas dependen unas de otras: al tocar una, se revisan todas.
                if (campo.type === 'date') {
                    form.querySelectorAll('input[type="date"]').forEach((fecha) => fecha.dataset.tocado = '1');
                }
                validar(true);
            }));
        });

        form.addEventListener('submit', (evento) => {
            campos().forEach((campo) => campo.dataset.tocado = '1');
            const primero = validar(false);
            if (primero) {
                evento.preventDefault();
                primero.focus();
            }
        });
    };

    // Ayudas comunes: fechas 'YYYY-MM-DD' (se comparan como texto) y su formato dd/mm/aaaa.
    window.fechaLegible = (f) => f ? f.split('-').reverse().join('/') : '';
    window.numeroLegible = (n) => String(Math.round(n * 100) / 100);
</script>
