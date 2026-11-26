/* HazloLegal — JS mínimo: menú móvil, aviso de cookies y animación al scroll. */
(function () {
    'use strict';

    // -------------------------------------------------- Menú móvil
    var header = document.querySelector('[data-nav]');
    var toggle = document.querySelector('[data-nav-toggle]');

    if (header && toggle) {
        toggle.addEventListener('click', function () {
            var open = header.getAttribute('data-open') === 'true';
            header.setAttribute('data-open', String(!open));
            toggle.setAttribute('aria-expanded', String(!open));
            toggle.setAttribute('aria-label', open ? 'Abrir menú' : 'Cerrar menú');
        });

        // Cerrar al navegar o al pulsar Escape.
        header.querySelectorAll('.nav a').forEach(function (link) {
            link.addEventListener('click', function () {
                header.setAttribute('data-open', 'false');
                toggle.setAttribute('aria-expanded', 'false');
            });
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && header.getAttribute('data-open') === 'true') {
                header.setAttribute('data-open', 'false');
                toggle.setAttribute('aria-expanded', 'false');
                toggle.focus();
            }
        });
    }

    // -------------------------------------------------- Aviso de cookies
    //
    // El formulario funciona sin esto: envía, el servidor guarda y redirige.
    // Con JavaScript se evita esa recarga, que en mitad de una lectura o de
    // un trámite a medio llenar es justo lo que no se quiere. La decisión se
    // manda por fetch y el aviso se va con un mensaje flotante.
    //
    // Las etiquetas de medición que se acepten empiezan en la siguiente
    // página: inyectarlas aquí obligaría a tener el mismo fragmento escrito
    // en dos sitios, y perder una visita de analítica no le importa a nadie.
    var aviso = document.getElementById('aviso-cookies');

    if (aviso && window.fetch) {
        aviso.querySelectorAll('form').forEach(function (form) {
            form.addEventListener('submit', function (evento) {
                evento.preventDefault();

                aviso.querySelectorAll('button').forEach(function (boton) {
                    boton.disabled = true;
                });

                fetch(form.action, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    body: new FormData(form),
                    credentials: 'same-origin'
                })
                    .then(function (respuesta) { return respuesta.json(); })
                    .then(function (cuerpo) {
                        cerrarAviso();
                        flotante(cuerpo && cuerpo.message ? cuerpo.message : 'Guardamos tu preferencia.');
                    })
                    .catch(function () {
                        // Si falla la red se deja que el formulario haga lo
                        // de siempre, que sí funciona: recargar.
                        form.submit();
                    });
            });
        });
    }

    function cerrarAviso() {
        aviso.setAttribute('data-saliendo', 'true');
        window.setTimeout(function () { aviso.remove(); }, 240);
    }

    // -------------------------------------------------- Mensaje flotante
    function flotante(texto) {
        var caja = document.createElement('div');

        caja.className = 'toast';
        // `status` y no `alert`: esto informa, no interrumpe, y un lector de
        // pantalla no debería cortar lo que esté leyendo para anunciarlo.
        caja.setAttribute('role', 'status');
        caja.setAttribute('aria-live', 'polite');
        caja.textContent = texto;

        document.body.appendChild(caja);

        window.requestAnimationFrame(function () { caja.setAttribute('data-visible', 'true'); });

        window.setTimeout(function () {
            caja.removeAttribute('data-visible');
            window.setTimeout(function () { caja.remove(); }, 300);
        }, 5000);
    }

    // -------------------------------------------------- Reveal al hacer scroll
    var revealables = document.querySelectorAll('.reveal');

    if (!revealables.length) {
        return;
    }

    if (!('IntersectionObserver' in window) ||
        window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        revealables.forEach(function (el) { el.classList.add('is-visible'); });
        return;
    }

    var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

    revealables.forEach(function (el) { observer.observe(el); });
})();
