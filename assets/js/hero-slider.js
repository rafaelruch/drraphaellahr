/**
 * Lahr — Hero slider (vanilla, sem dependências).
 * Ativa em [data-lahr-slider] com 2+ slides. Suporta setas, dots, swipe,
 * teclado, autoplay opcional (pausa em hover/foco/reduced-motion/aba oculta).
 */
(function () {
    'use strict';

    function initSlider(root) {
        var track = root.querySelector('.cn-hero__track');
        var slides = root.querySelectorAll('.cn-hero__slide');
        var dots = root.querySelectorAll('[data-lahr-dot]');
        var prev = root.querySelector('[data-lahr-prev]');
        var next = root.querySelector('[data-lahr-next]');
        var n = slides.length;
        if (!track || n < 2) return;

        var i = 0;
        var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        var autoplay = root.hasAttribute('data-autoplay') && !reduce;
        var interval = (parseInt(root.getAttribute('data-interval'), 10) || 6) * 1000;
        var timer = null;

        function go(idx) {
            i = (idx + n) % n;
            track.style.transform = 'translateX(-' + (i * 100) + '%)';
            for (var d = 0; d < dots.length; d++) {
                var on = d === i;
                dots[d].classList.toggle('is-active', on);
                dots[d].setAttribute('aria-selected', on ? 'true' : 'false');
            }
            for (var s = 0; s < slides.length; s++) {
                var v = slides[s].querySelector('video');
                if (!v) continue;
                if (s === i) { try { v.play(); } catch (e) {} } else { v.pause(); }
            }
        }
        function nextSlide() { go(i + 1); }
        function prevSlide() { go(i - 1); }
        function stop() { if (timer) { clearInterval(timer); timer = null; } }
        function start() { stop(); if (autoplay) { timer = setInterval(nextSlide, interval); } }

        if (next) next.addEventListener('click', function () { nextSlide(); start(); });
        if (prev) prev.addEventListener('click', function () { prevSlide(); start(); });
        for (var k = 0; k < dots.length; k++) {
            (function (di) {
                dots[di].addEventListener('click', function () { go(di); start(); });
            })(k);
        }

        root.addEventListener('mouseenter', stop);
        root.addEventListener('mouseleave', start);
        root.addEventListener('focusin', stop);
        root.addEventListener('focusout', start);

        var x0 = null;
        root.addEventListener('pointerdown', function (e) { x0 = e.clientX; });
        root.addEventListener('pointerup', function (e) {
            if (x0 === null) return;
            var dx = e.clientX - x0;
            if (Math.abs(dx) > 40) { if (dx < 0) { nextSlide(); } else { prevSlide(); } start(); }
            x0 = null;
        });

        root.addEventListener('keydown', function (e) {
            if (e.key === 'ArrowLeft') { prevSlide(); start(); }
            else if (e.key === 'ArrowRight') { nextSlide(); start(); }
        });

        document.addEventListener('visibilitychange', function () {
            if (document.hidden) { stop(); } else { start(); }
        });

        go(0);
        start();
    }

    function boot() {
        var sliders = document.querySelectorAll('[data-lahr-slider]');
        for (var i = 0; i < sliders.length; i++) { initSlider(sliders[i]); }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
