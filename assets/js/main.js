// Cursor-tilt: hero bike images subtly tilt and shift toward the mouse,
// snapping back to neutral when the cursor leaves. Skipped on touch
// devices (no hover) and for visitors who prefer reduced motion.
(function () {
    var targets = Array.prototype.slice.call(document.querySelectorAll('[data-tilt]'));
    if (!targets.length) return;
    if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    if (window.matchMedia && window.matchMedia('(hover: none)').matches) return;

    var maxTilt = 6;   // degrees
    var maxShift = 10; // px

    targets.forEach(function (el) {
        el.addEventListener('mousemove', function (e) {
            var rect = el.getBoundingClientRect();
            var x = (e.clientX - rect.left) / rect.width - 0.5;
            var y = (e.clientY - rect.top) / rect.height - 0.5;
            var rotateY = (x * maxTilt * 2).toFixed(2);
            var rotateX = (y * -maxTilt * 2).toFixed(2);
            var shiftX = (x * maxShift * 2).toFixed(1);
            var shiftY = (y * maxShift * 2).toFixed(1);
            el.style.transform = 'perspective(900px) rotateX(' + rotateX + 'deg) rotateY(' + rotateY + 'deg) translate(' + shiftX + 'px,' + shiftY + 'px)';
        });
        el.addEventListener('mouseleave', function () {
            el.style.transform = '';
        });
    });
})();

// Parallax: decorative blobs and section background photos drift at a
// fraction of scroll speed for a subtle sense of depth. Skipped entirely
// if the visitor prefers reduced motion.
(function () {
    var els = Array.prototype.slice.call(document.querySelectorAll('[data-parallax]'));
    if (!els.length) return;
    if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    var ticking = false;

    function update() {
        var vh = window.innerHeight;
        els.forEach(function (el) {
            var speed = parseFloat(el.getAttribute('data-parallax')) || 0.15;
            var rect = el.getBoundingClientRect();
            var centerOffset = (rect.top + rect.height / 2) - vh / 2;
            el.style.transform = 'translateY(' + (centerOffset * -speed).toFixed(1) + 'px)';
        });
        ticking = false;
    }

    function onScroll() {
        if (!ticking) {
            window.requestAnimationFrame(update);
            ticking = true;
        }
    }

    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onScroll);
    update();
})();

// Sticky header: adds a solid background once the page scrolls past the
// hero, so nav text stays legible over whatever content is underneath.
(function () {
    var header = document.querySelector('.site-header');
    if (!header) return;

    function updateHeader() {
        header.classList.toggle('is-scrolled', window.scrollY > 20);
    }

    updateHeader();
    window.addEventListener('scroll', updateHeader, { passive: true });
})();

// Scroll-reveal: each section marked with .reveal fades/slides in the
// first time it enters the viewport.
(function () {
    var items = Array.prototype.slice.call(document.querySelectorAll('.reveal'));
    if (!items.length) return;

    if (!('IntersectionObserver' in window)) {
        items.forEach(function (el) { el.classList.add('is-visible'); });
        return;
    }

    var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.15, rootMargin: '0px 0px -60px 0px' });

    items.forEach(function (el) { observer.observe(el); });
})();

// Mobile navigation toggle
(function () {
    var nav = document.getElementById('nav');
    var toggle = document.getElementById('navToggle');
    if (toggle && nav) {
        toggle.addEventListener('click', function () {
            nav.classList.toggle('open');
        });
        // close menu when a link is tapped
        nav.querySelectorAll('.nav-links a').forEach(function (a) {
            a.addEventListener('click', function () { nav.classList.remove('open'); });
        });
    }
})();

// Hero slider
(function () {
    var root = document.getElementById('heroSlider');
    if (!root) return;

    var dots = Array.prototype.slice.call(root.querySelectorAll('.hero-pager button'));
    var slideImgs = Array.prototype.slice.call(root.querySelectorAll('.hero-slide-img'));
    if (dots.length < 2) return; // nothing to rotate

    var titleEl = root.querySelector('.hero-title');
    var copyEl = root.querySelector('.hero-copy');
    var ctaEl = root.querySelector('#heroCta');
    var specEls = Array.prototype.slice.call(root.querySelectorAll('.spec-strip .v'));
    var defaultTitle = titleEl ? titleEl.getAttribute('data-default') : '';
    var defaultCopy = copyEl ? copyEl.getAttribute('data-default') : '';
    var defaultHref = ctaEl ? ctaEl.getAttribute('data-default-href') : '#feature';
    var current = 0;
    var timer = null;

    var textEls = [titleEl, copyEl].concat(specEls).filter(Boolean);

    function goTo(index) {
        current = (index + dots.length) % dots.length;

        dots.forEach(function (d, i) { d.classList.toggle('is-active', i === current); });
        slideImgs.forEach(function (img, i) { img.classList.toggle('is-active', i === current); });

        var d = dots[current];

        // Fade the text out, swap it once it's invisible, then fade back in —
        // avoids the jarring instant text swap while the image cross-fades.
        textEls.forEach(function (el) { el.style.opacity = '0'; });

        setTimeout(function () {
            if (titleEl) titleEl.textContent = d.dataset.heading || defaultTitle;
            if (copyEl) copyEl.textContent = d.dataset.subheading || defaultCopy;
            specEls.forEach(function (el, i) {
                var value = d.dataset['spec' + (i + 1)];
                el.textContent = value || el.getAttribute('data-default');
            });
            textEls.forEach(function (el) { el.style.opacity = '1'; });
        }, 260);

        if (ctaEl) ctaEl.setAttribute('href', d.dataset.link || defaultHref);
    }

    function restart() {
        if (timer) clearInterval(timer);
        var delay = parseInt(root.getAttribute('data-autoplay'), 10) || 6000;
        timer = setInterval(function () { goTo(current + 1); }, delay);
    }

    dots.forEach(function (d, i) {
        d.addEventListener('click', function () { goTo(i); restart(); });
    });

    restart();
})();

// Our Bikes carousel: left/right arrows switch bike model, swatches switch
// that bike's colour (homepage).
(function () {
    var track = document.getElementById('bikesTrack');
    if (!track) return;

    var panels = Array.prototype.slice.call(track.querySelectorAll('.bike-panel'));
    if (!panels.length) return;

    var prevBtn = document.getElementById('bikesPrev');
    var nextBtn = document.getElementById('bikesNext');
    var selectBtns = Array.prototype.slice.call(document.querySelectorAll('.bike-select-btn'));
    var current = 0;

    function showBike(index) {
        var next = (index + panels.length) % panels.length;
        if (next === current) return;

        panels[current].classList.remove('is-active');
        panels[next].classList.add('is-active');
        selectBtns.forEach(function (btn, i) { btn.classList.toggle('is-active', i === next); });

        // Text/specs appear instantly with the panel; only the bike image
        // fades in.
        var img = panels[next].querySelector('.bike-color-img.is-active');
        if (img) {
            img.style.transition = 'none';
            img.style.opacity = '0';
            void img.offsetWidth; // force reflow so the reset above applies before re-enabling
            img.style.transition = '';
            img.style.opacity = '1';
        }

        current = next;
    }

    if (prevBtn) prevBtn.addEventListener('click', function () { showBike(current - 1); });
    if (nextBtn) nextBtn.addEventListener('click', function () { showBike(current + 1); });
    selectBtns.forEach(function (btn, i) {
        btn.addEventListener('click', function () { showBike(i); });
    });

    // Each panel's colour swatches only affect that panel's own image stack.
    // The old colour fades out completely before the new one fades in —
    // sequential, not simultaneous, so the two photos never blend/ghost
    // together mid-switch.
    panels.forEach(function (panel) {
        var swatches = Array.prototype.slice.call(panel.querySelectorAll('.swatch'));
        var images = Array.prototype.slice.call(panel.querySelectorAll('.bike-color-img'));
        var activeIndex = images.findIndex(function (img) { return img.classList.contains('is-active'); });
        if (activeIndex === -1) activeIndex = 0;
        var switching = false;

        swatches.forEach(function (btn, i) {
            btn.addEventListener('click', function () {
                if (i === activeIndex || switching) return;
                switching = true;

                swatches.forEach(function (b, j) { b.classList.toggle('is-active', j === i); });

                var oldImg = images[activeIndex];
                if (oldImg) oldImg.classList.remove('is-active');

                setTimeout(function () {
                    images.forEach(function (img, j) { img.classList.toggle('is-active', j === i); });
                    activeIndex = i;
                    switching = false;
                }, 350);
            });
        });
    });
})();

// Bike full-spec popup: "View full specs" clones that bike's hidden
// template (all spec groups) into a modal overlay.
(function () {
    var modal = document.getElementById('bikeModal');
    if (!modal) return;

    var body = document.getElementById('bikeModalBody');

    function openModal(template) {
        body.innerHTML = '';
        body.appendChild(template.content.cloneNode(true));
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }

    document.querySelectorAll('[data-view-more]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var panel = btn.closest('.bike-panel');
            var tmpl = panel ? panel.querySelector('[data-spec-template]') : null;
            if (tmpl) openModal(tmpl);
        });
    });

    modal.querySelectorAll('[data-modal-close]').forEach(function (el) {
        el.addEventListener('click', closeModal);
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modal.classList.contains('is-open')) closeModal();
    });
})();

// Hover preview: hovering a bike-selector button shows a quick popup
// (photo + key specs) for that bike without switching to it.
(function () {
    var popup = document.getElementById('bikeHoverPreview');
    if (!popup) return;
    if (window.matchMedia && window.matchMedia('(hover: none)').matches) return;

    var imgEl = document.getElementById('bikeHoverImage');
    var nameEl = document.getElementById('bikeHoverName');
    var taglineEl = document.getElementById('bikeHoverTagline');
    var specsEl = document.getElementById('bikeHoverSpecs');

    document.querySelectorAll('.bike-select-btn').forEach(function (btn) {
        btn.addEventListener('mouseenter', function () {
            var image = btn.getAttribute('data-preview-image') || '';
            var specs = [];
            try { specs = JSON.parse(btn.getAttribute('data-preview-specs') || '[]'); } catch (err) { specs = []; }

            if (image) {
                imgEl.src = image;
                imgEl.style.display = '';
            } else {
                imgEl.style.display = 'none';
            }
            nameEl.textContent = btn.getAttribute('data-preview-name') || '';
            taglineEl.textContent = btn.getAttribute('data-preview-tagline') || '';

            specsEl.innerHTML = '';
            specs.forEach(function (s) {
                var row = document.createElement('div');
                var k = document.createElement('span');
                k.className = 'k';
                k.textContent = s.label;
                var v = document.createElement('span');
                v.className = 'v';
                v.textContent = s.value;
                row.appendChild(k);
                row.appendChild(v);
                specsEl.appendChild(row);
            });

            var rect = btn.getBoundingClientRect();
            popup.style.left = (rect.left + rect.width / 2 + window.scrollX) + 'px';
            popup.style.top = (rect.bottom + window.scrollY + 10) + 'px';
            popup.classList.add('is-visible');
        });

        btn.addEventListener('mouseleave', function () {
            popup.classList.remove('is-visible');
        });
    });
})();
