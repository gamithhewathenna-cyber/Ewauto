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
    var current = 0;

    function showBike(index) {
        current = (index + panels.length) % panels.length;
        panels.forEach(function (p, i) { p.classList.toggle('is-active', i === current); });
    }

    if (prevBtn) prevBtn.addEventListener('click', function () { showBike(current - 1); });
    if (nextBtn) nextBtn.addEventListener('click', function () { showBike(current + 1); });

    // Each panel's colour swatches only affect that panel's own image stack.
    panels.forEach(function (panel) {
        var swatches = Array.prototype.slice.call(panel.querySelectorAll('.swatch'));
        var images = Array.prototype.slice.call(panel.querySelectorAll('.bike-color-img'));

        swatches.forEach(function (btn, i) {
            btn.addEventListener('click', function () {
                swatches.forEach(function (b, j) { b.classList.toggle('is-active', j === i); });
                images.forEach(function (img) {
                    img.classList.toggle('is-active', parseInt(img.dataset.index, 10) === i);
                });
            });
        });
    });
})();
