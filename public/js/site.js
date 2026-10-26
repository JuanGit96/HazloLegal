/* HazloLegal — JS mínimo: menú móvil y animación al scroll. Nada más. */
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
