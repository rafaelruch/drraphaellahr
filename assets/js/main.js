/**
 * Lahr Cinematic v4 — animations coexistentes e coesas
 *  - Cursor glow no hero
 *  - Parallax em imagens
 *  - Tilt 3D nos cards
 *  - Mouse-following gold glow nos cards (via CSS custom prop)
 *  - Scroll reveal stagger
 *  - Counter animado
 *  - Sticky header
 */
(function () {
    'use strict';
    var prefersReduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var isCoarse = window.matchMedia('(pointer: coarse)').matches;

    /* ====== HERO CURSOR GLOW ====== */
    if (!prefersReduce && !isCoarse) {
        var heroSection = document.querySelector('[data-cursor-glow]');
        var heroGlow = document.querySelector('[data-glow]');
        if (heroSection && heroGlow) {
            heroSection.addEventListener('mousemove', function (e) {
                var r = heroSection.getBoundingClientRect();
                var x = e.clientX - r.left;
                var y = e.clientY - r.top;
                heroGlow.style.transform = 'translate(' + x + 'px, ' + y + 'px) translate(-50%, -50%)';
                heroGlow.style.opacity = '1';
            });
            heroSection.addEventListener('mouseleave', function () {
                heroGlow.style.opacity = '0';
            });
        }
    }

    /* ====== PARALLAX em elementos com data-parallax ====== */
    if (!prefersReduce) {
        var parallaxElems = document.querySelectorAll('[data-parallax]');
        var pxTicking = false;
        function updateParallax () {
            parallaxElems.forEach(function (el) {
                var speed = parseFloat(el.getAttribute('data-parallax-speed')) || 0.1;
                var r = el.getBoundingClientRect();
                var offset = (r.top + r.height / 2 - window.innerHeight / 2) * speed;
                el.style.transform = 'translateY(' + (-offset) + 'px)';
            });
            pxTicking = false;
        }
        window.addEventListener('scroll', function () {
            if (!pxTicking) { requestAnimationFrame(updateParallax); pxTicking = true; }
        }, { passive: true });
        updateParallax();
    }

    /* ====== TILT 3D nos cards (mouse following gold glow) ====== */
    if (!prefersReduce && !isCoarse) {
        document.querySelectorAll('[data-tilt]').forEach(function (card) {
            card.addEventListener('mousemove', function (e) {
                var r = card.getBoundingClientRect();
                var x = e.clientX - r.left;
                var y = e.clientY - r.top;
                // gold glow position
                card.style.setProperty('--mx', x + 'px');
                card.style.setProperty('--my', y + 'px');
                // tilt
                var px = (x / r.width  - 0.5) * 2;
                var py = (y / r.height - 0.5) * 2;
                card.style.transform = 'translateY(-6px) perspective(900px) rotateX(' + (-py * 2.5) + 'deg) rotateY(' + (px * 2.5) + 'deg)';
            });
            card.addEventListener('mouseleave', function () {
                card.style.transform = '';
                card.style.transition = 'transform 0.6s cubic-bezier(0.16, 1, 0.3, 1)';
                setTimeout(function () { card.style.transition = ''; }, 600);
            });
        });
    }

    /* ====== SCROLL REVEAL ====== */
    if (!prefersReduce && 'IntersectionObserver' in window) {
        var autoTargets = document.querySelectorAll(
            '.cn-sec-head, .cn-cards, ' +
            '.cn-about__photo, .cn-about__content, ' +
            '.cn-stats__grid, .cn-testimonials__grid, ' +
            '.cn-cta__inner, .cn-footer__grid'
        );
        var vh = window.innerHeight;
        autoTargets.forEach(function (el) {
            el.classList.add('cn-reveal');
            if (el.matches('.cn-cards, .cn-stats__grid, .cn-testimonials__grid')) {
                el.classList.add('cn-reveal--stagger');
            }
            if (el.getBoundingClientRect().top < vh - 60) {
                requestAnimationFrame(function () { el.classList.add('is-revealed'); });
            }
        });
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-revealed');
                    io.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1, rootMargin: '0px 0px -80px 0px' });
        document.querySelectorAll('.cn-reveal:not(.is-revealed)').forEach(function (el) { io.observe(el); });
        // Safety
        setTimeout(function () {
            document.querySelectorAll('.cn-reveal:not(.is-revealed)').forEach(function (el) { el.classList.add('is-revealed'); });
        }, 4500);
    } else {
        document.querySelectorAll('.cn-reveal').forEach(function (el) { el.classList.add('is-revealed'); });
    }

    /* ====== COUNTER ANIMADO ====== */
    if (!prefersReduce && 'IntersectionObserver' in window) {
        var counters = document.querySelectorAll('[data-counter]');
        var cio = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;
                var el = entry.target;
                var target = parseFloat(el.getAttribute('data-counter'));
                var duration = 1500;
                var start = performance.now();
                function step (now) {
                    var p = Math.min((now - start) / duration, 1);
                    var eased = 1 - Math.pow(1 - p, 3);
                    el.textContent = Math.round(target * eased);
                    if (p < 1) requestAnimationFrame(step);
                    else el.textContent = target;
                }
                requestAnimationFrame(step);
                cio.unobserve(el);
            });
        }, { threshold: 0.4 });
        counters.forEach(function (el) { cio.observe(el); });
    }

    /* ====== CAROUSELS ====== */
    document.querySelectorAll('[data-carousel]').forEach(function (carousel) {
        var track  = carousel.querySelector('[data-carousel-track]');
        var slides = Array.from(track.children);
        var prevBtn = carousel.querySelector('[data-carousel-prev]');
        var nextBtn = carousel.querySelector('[data-carousel-next]');
        var dotsContainer = carousel.querySelector('[data-carousel-dots]');
        if (!track || !slides.length) return;

        var index = 0;
        var perView = 3;

        function compute () {
            var w = window.innerWidth;
            if (w <= 700) perView = 1;
            else if (w <= 1024) perView = 2;
            else perView = 3;
        }

        function pages () {
            return Math.max(1, Math.ceil(slides.length / perView));
        }

        function goTo (i) {
            compute();
            var total = pages();
            index = Math.max(0, Math.min(i, total - 1));
            var slideW = slides[0].getBoundingClientRect().width;
            var gap = parseFloat(getComputedStyle(track).gap) || 0;
            var offset = -(index * perView * (slideW + gap));
            // Clamp so we don't overflow at the end
            var maxOffset = -(track.scrollWidth - track.parentElement.clientWidth);
            if (offset < maxOffset) offset = maxOffset;
            track.style.transform = 'translateX(' + offset + 'px)';
            updateUI();
        }

        function updateUI () {
            var total = pages();
            if (prevBtn) prevBtn.disabled = index === 0;
            if (nextBtn) nextBtn.disabled = index >= total - 1;
            if (dotsContainer) {
                dotsContainer.innerHTML = '';
                for (var p = 0; p < total; p++) {
                    var dot = document.createElement('button');
                    dot.type = 'button';
                    dot.className = 'cn-carousel__dot' + (p === index ? ' is-active' : '');
                    dot.setAttribute('aria-label', 'Ir para grupo ' + (p + 1));
                    (function (pageIdx) {
                        dot.addEventListener('click', function () { goTo(pageIdx); restartAuto(); });
                    })(p);
                    dotsContainer.appendChild(dot);
                }
            }
        }

        if (prevBtn) prevBtn.addEventListener('click', function () { goTo(index - 1); restartAuto(); });
        if (nextBtn) nextBtn.addEventListener('click', function () { goTo(index + 1); restartAuto(); });

        // Swipe (touch)
        var startX = 0, currentX = 0, dragging = false;
        track.addEventListener('touchstart', function (e) {
            startX = e.touches[0].clientX; dragging = true; track.style.transition = 'none';
        }, { passive: true });
        track.addEventListener('touchmove', function (e) {
            if (!dragging) return;
            currentX = e.touches[0].clientX;
        }, { passive: true });
        track.addEventListener('touchend', function () {
            if (!dragging) return; dragging = false;
            track.style.transition = '';
            var dx = currentX - startX;
            if (Math.abs(dx) > 50) {
                if (dx < 0) goTo(index + 1);
                else goTo(index - 1);
            } else {
                goTo(index);
            }
            restartAuto();
        });

        // Autoplay sutil (8s)
        var autoTimer;
        function startAuto () {
            if (prefersReduce) return;
            autoTimer = setInterval(function () {
                var total = pages();
                var next = (index + 1) % total;
                goTo(next);
            }, 8000);
        }
        function restartAuto () {
            if (autoTimer) clearInterval(autoTimer);
            startAuto();
        }
        carousel.addEventListener('mouseenter', function () { if (autoTimer) clearInterval(autoTimer); });
        carousel.addEventListener('mouseleave', function () { restartAuto(); });

        window.addEventListener('resize', function () {
            compute();
            goTo(index);
        });

        compute();
        goTo(0);
        startAuto();
    });

    /* ====== AGENDAR FORM ====== */
    var agendForm = document.getElementById('lahr-agendar');
    if (agendForm) {
        // Capture UTMs from URL into hidden fields
        try {
            var params = new URLSearchParams(window.location.search);
            ['utm_source','utm_medium','utm_campaign','utm_term','utm_content'].forEach(function (k) {
                var v = params.get(k);
                if (v) {
                    sessionStorage.setItem(k, v);
                }
                var inp = agendForm.querySelector('input[name="' + k + '"]');
                if (inp) {
                    inp.value = params.get(k) || sessionStorage.getItem(k) || '';
                }
            });
        } catch (e) {}

        // Phone mask
        var phoneInp = agendForm.querySelector('input[data-lm-mask="phone"]');
        if (phoneInp) {
            phoneInp.addEventListener('input', function (e) {
                var v = e.target.value.replace(/\D/g, '').slice(0, 11);
                var f = v;
                if (v.length > 0) f = '(' + v.slice(0, 2);
                if (v.length >= 3) f += ') ' + v.slice(2, 7);
                if (v.length >= 8) f += '-' + v.slice(7, 11);
                e.target.value = f;
            });
        }

        // Character counter for textarea
        var msgEl = agendForm.querySelector('textarea[name="mensagem"]');
        var counterEl = agendForm.querySelector('[data-char-counter]');
        if (msgEl && counterEl) {
            msgEl.addEventListener('input', function () {
                counterEl.textContent = msgEl.value.length + ' / 500';
            });
        }

        agendForm.addEventListener('submit', function (e) {
            e.preventDefault();
            var honeypot = agendForm.querySelector('input[name="website"]');
            if (honeypot && honeypot.value) return; // bot

            var fields = {
                nome:      agendForm.querySelector('[name="nome"]'),
                whatsapp:  agendForm.querySelector('[name="whatsapp"]'),
                cidade:    agendForm.querySelector('[name="cidade"]'),
                interesse: agendForm.querySelector('[name="interesse"]')
            };
            var valid = true;
            [
                ['nome',      fields.nome.value.trim().length >= 3],
                ['whatsapp',  fields.whatsapp.value.replace(/\D/g, '').length >= 10],
                ['cidade',    fields.cidade.value.trim().length >= 2],
                ['interesse', fields.interesse.value.trim().length > 0]
            ].forEach(function (pair) {
                if (!pair[1]) { fields[pair[0]].classList.add('is-error'); valid = false; }
                else { fields[pair[0]].classList.remove('is-error'); }
            });
            if (!valid) {
                var firstErr = agendForm.querySelector('.is-error');
                if (firstErr) firstErr.focus();
                return;
            }

            var nome      = fields.nome.value.trim();
            var whatsapp  = fields.whatsapp.value.trim();
            var cidade    = fields.cidade.value.trim();
            var interesse = fields.interesse.value;
            var email     = (agendForm.querySelector('[name="email"]') || {}).value || '';
            var origem    = (agendForm.querySelector('[name="origem"]') || {}).value || '';
            var mensagem  = (agendForm.querySelector('[name="mensagem"]') || {}).value || '';

            // Compose WhatsApp message
            var lines = [
                'Olá! Gostaria de agendar uma consulta com o Dr. Raphael.',
                '',
                'Nome: ' + nome,
                'WhatsApp: ' + whatsapp,
                'Cidade: ' + cidade,
                'Interesse: ' + interesse
            ];
            if (email) lines.push('E-mail: ' + email);
            if (origem) lines.push('Como me encontrou: ' + origem);
            if (mensagem) lines.push('', 'Mensagem:', mensagem);
            var waNum = (window.LAHR && window.LAHR.waNumber) || '5547999701100';
            var url = 'https://wa.me/' + waNum + '?text=' + encodeURIComponent(lines.join('\n'));

            // Tracking
            if (window.dataLayer) {
                window.dataLayer.push({
                    event: 'generate_lead',
                    lead_source: 'agendar_form',
                    area_interesse: interesse,
                    origem: origem || 'nao_informado',
                    value: 0,
                    currency: 'BRL'
                });
            }

            // Persistir lead no site + e-mails (não bloqueia o WhatsApp; keepalive
            // garante o envio mesmo com o redirect para /obrigado/).
            if (window.LAHR && window.LAHR.ajaxUrl) {
                var fd = new FormData(agendForm);
                fd.append('action', 'lahr_lead');
                fd.append('_nonce', window.LAHR.leadNonce || '');
                fd.append('origem_form', 'agendar');
                try {
                    fetch(window.LAHR.ajaxUrl, { method: 'POST', body: fd, keepalive: true, credentials: 'same-origin' });
                } catch (err) {}
            }

            // Open WhatsApp
            window.open(url, '_blank', 'noopener');

            // Replace form by success state
            var success = document.createElement('div');
            success.className = 'cn-form__success';
            success.innerHTML = '<h3>Solicitação enviada</h3><p>Acabamos de abrir o WhatsApp com sua mensagem pronta. Caso não tenha aberto automaticamente, <a href="' + url + '" target="_blank" rel="noopener" style="color:var(--c-gold)">clique aqui para abrir</a>. Retorno em até 24h.</p>';
            agendForm.parentNode.replaceChild(success, agendForm);

            // Redirect to /obrigado after 6s if exists
            setTimeout(function () {
                window.location.href = '/obrigado/';
            }, 6000);
        });
    }

    /* ====== FAQ ACCORDION ====== */
    document.querySelectorAll('.cn-faq__item').forEach(function (item) {
        var q = item.querySelector('.cn-faq__q');
        if (!q) return;
        q.addEventListener('click', function () {
            var open = item.classList.contains('is-open');
            // Single-open mode: close other open items in the same list
            var list = item.closest('.cn-faq__list');
            if (list) {
                list.querySelectorAll('.cn-faq__item.is-open').forEach(function (other) {
                    if (other !== item) other.classList.remove('is-open');
                });
            }
            item.classList.toggle('is-open', !open);
        });
    });

    /* ====== STICKY HEADER ====== */
    var header = document.querySelector('.cn-header');
    if (header) {
        var threshold = 60;
        function onScroll () {
            if (window.scrollY > threshold) header.classList.add('is-stuck');
            else header.classList.remove('is-stuck');
        }
        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();
    }

    /* ====== MOBILE MENU ====== */
    var menuOpen  = document.querySelector('[data-lm-mobile-open]');
    var menuClose = document.querySelector('[data-lm-mobile-close]');
    var menu      = document.querySelector('[data-lm-mobile-menu]');
    if (menuOpen && menu) menuOpen.addEventListener('click', function () { menu.classList.add('is-open'); document.body.style.overflow = 'hidden'; });
    if (menuClose && menu) menuClose.addEventListener('click', function () { menu.classList.remove('is-open'); document.body.style.overflow = ''; });

    /* ====== SMOOTH HASH ====== */
    document.addEventListener('click', function (e) {
        var a = e.target.closest('a[href^="#"]');
        if (!a) return;
        var href = a.getAttribute('href');
        if (href === '#' || href.length < 2) return;
        var t = document.querySelector(href);
        if (!t) return;
        e.preventDefault();
        t.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });

    /* ====== WHATSAPP WIDGET ====== */
    var waWidget = document.querySelector('.lm-wa');
    if (waWidget) {
        var waOpenBtn  = waWidget.querySelector('[data-lm-wa-open]');
        var waCloseBtn = waWidget.querySelector('[data-lm-wa-close]');
        var waForm     = waWidget.querySelector('[data-lm-wa-form]');
        var open  = function () { waWidget.classList.add('is-open'); waOpenBtn.setAttribute('aria-expanded', 'true'); setTimeout(function () { var f = waForm.querySelector('input[name="nome"]'); if (f) f.focus(); }, 350); if (window.dataLayer) window.dataLayer.push({ event: 'whatsapp_chat_opened' }); };
        var close = function () { waWidget.classList.remove('is-open'); waOpenBtn.setAttribute('aria-expanded', 'false'); };
        waOpenBtn.addEventListener('click', function () { waWidget.classList.contains('is-open') ? close() : open(); });
        waCloseBtn.addEventListener('click', close);
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && waWidget.classList.contains('is-open')) close(); });
        document.addEventListener('click', function (e) { if (waWidget.classList.contains('is-open') && !waWidget.contains(e.target)) close(); });
        var phone = waForm.querySelector('input[data-lm-mask="phone"]');
        if (phone) {
            phone.addEventListener('input', function (e) {
                var v = e.target.value.replace(/\D/g, '').slice(0, 11);
                var f = v;
                if (v.length > 0) f = '(' + v.slice(0, 2);
                if (v.length >= 3) f += ') ' + v.slice(2, 7);
                if (v.length >= 8) f += '-' + v.slice(7, 11);
                e.target.value = f;
            });
        }
        waForm.addEventListener('submit', function (e) {
            e.preventDefault();
            var nome      = waForm.querySelector('input[name="nome"]').value.trim();
            var telefone  = waForm.querySelector('input[name="telefone"]').value.trim();
            var interesse = waForm.querySelector('select[name="interesse"]').value;
            var valid = true;
            [['nome', nome.length >= 3], ['telefone', telefone.replace(/\D/g, '').length >= 10]].forEach(function (pair) {
                var input = waForm.querySelector('[name="' + pair[0] + '"]');
                if (!pair[1]) { input.classList.add('is-error'); valid = false; }
                else input.classList.remove('is-error');
            });
            if (!valid) return;
            var msg = 'Olá! Meu nome é ' + nome + '.';
            msg += interesse ? ' Gostaria de saber sobre ' + interesse + '.' : ' Gostaria de agendar uma consulta com o Dr. Raphael.';
            msg += '\nMeu WhatsApp: ' + telefone;
            var waNum = (window.LAHR && window.LAHR.waNumber) || '5547999701100';
            var url = 'https://wa.me/' + waNum + '?text=' + encodeURIComponent(msg);
            if (window.dataLayer) window.dataLayer.push({ event: 'generate_lead', lead_source: 'whatsapp_widget', area_interesse: interesse || 'nao_informado' });

            // Persistir lead no site + e-mails (mesmo endpoint do /agendar)
            if (window.LAHR && window.LAHR.ajaxUrl) {
                var fd = new FormData();
                fd.append('action', 'lahr_lead');
                fd.append('_nonce', window.LAHR.leadNonce || '');
                fd.append('origem_form', 'widget');
                fd.append('nome', nome);
                fd.append('whatsapp', telefone);
                fd.append('interesse', interesse);
                try {
                    fetch(window.LAHR.ajaxUrl, { method: 'POST', body: fd, keepalive: true, credentials: 'same-origin' });
                } catch (err) {}
            }

            window.open(url, '_blank', 'noopener');

            // Confirmação clara e persistente de que os dados foram enviados
            var waBody = waWidget.querySelector('.lm-wa__body');
            if (waBody) {
                waForm.style.display = 'none';
                var okBubble = document.createElement('div');
                okBubble.className = 'lm-wa__bubble';
                okBubble.setAttribute('role', 'status');
                okBubble.innerHTML = '<p><strong>Dados enviados.</strong> Recebemos seu contato e retornamos pelo WhatsApp em até <strong>24h</strong>, de forma discreta.</p><span class="lm-wa__time">agora</span>';
                waBody.appendChild(okBubble);
            }
        });
    }
})();
