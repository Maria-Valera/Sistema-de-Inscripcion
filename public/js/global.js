/*
 * Un solo envío por formulario: al darle Guardar (o Eliminar), el botón se bloquea
 * para que un doble clic no envíe el formulario dos veces.
 *
 * Aplica a todos los formularios POST del panel (PUT y DELETE también, porque van por POST).
 * No bloquea:
 *  - si otro script detuvo el envío (validación en tiempo real con errores, envíos por AJAX);
 *  - formularios GET (búsquedas y filtros);
 *  - formularios que abren otra pestaña (target="_blank", por ejemplo descargas de PDF);
 *  - formularios marcados con data-permitir-doble-envio.
 */
(function () {
    const BOTONES = 'button[type="submit"], button:not([type]), input[type="submit"]';

    function botonesDe(form) {
        const dentro = Array.from(form.querySelectorAll(BOTONES));
        // Botones fuera del formulario que lo envían con el atributo form="id"
        const fuera = form.id ? Array.from(document.querySelectorAll(`[form="${form.id}"]`)) : [];
        return dentro.concat(fuera.filter((b) => b.matches(BOTONES)));
    }

    function textoEspera(form) {
        const metodo = (form.querySelector('input[name="_method"]')?.value || '').toUpperCase();
        return metodo === 'DELETE' ? 'Eliminando...' : 'Guardando...';
    }

    // Se escucha en document: corre DESPUÉS de los listeners del propio formulario,
    // así sabemos si alguno detuvo el envío (evento.defaultPrevented).
    document.addEventListener('submit', function (evento) {
        const form = evento.target;

        if (evento.defaultPrevented) return;
        if ((form.getAttribute('method') || 'get').toLowerCase() !== 'post') return;
        if (form.target === '_blank') return;
        if (form.hasAttribute('data-permitir-doble-envio')) return;

        // Si ya se está enviando, se ignora el segundo clic.
        if (form.dataset.enviando === '1') {
            evento.preventDefault();
            return;
        }
        form.dataset.enviando = '1';

        const pulsado = evento.submitter;

        // Se bloquea un instante después: si se bloqueara ya, el navegador no enviaría
        // el name/value del botón pulsado (algunos formularios lo usan).
        setTimeout(function () {
            botonesDe(form).forEach(function (boton) {
                boton.disabled = true;
                boton.classList.add('disabled');
            });

            if (pulsado && pulsado.tagName === 'BUTTON') {
                pulsado.dataset.textoOriginal = pulsado.innerHTML;
                pulsado.innerHTML = `<i class="fas fa-spinner fa-spin"></i> ${textoEspera(form)}`;
            }
        }, 0);
    });

    // Al volver con el botón "Atrás" del navegador la página puede salir de la caché
    // con los botones bloqueados: se reactivan.
    window.addEventListener('pageshow', function () {
        document.querySelectorAll('form[data-enviando="1"]').forEach(function (form) {
            delete form.dataset.enviando;
            botonesDe(form).forEach(function (boton) {
                boton.disabled = false;
                boton.classList.remove('disabled');
                if (boton.dataset.textoOriginal) {
                    boton.innerHTML = boton.dataset.textoOriginal;
                    delete boton.dataset.textoOriginal;
                }
            });
        });
    });
})();
