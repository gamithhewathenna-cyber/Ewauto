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

    function goTo(index) {
        current = (index + dots.length) % dots.length;

        dots.forEach(function (d, i) { d.classList.toggle('is-active', i === current); });
        slideImgs.forEach(function (img, i) { img.classList.toggle('is-active', i === current); });

        var d = dots[current];
        if (titleEl) titleEl.textContent = d.dataset.heading || defaultTitle;
        if (copyEl) copyEl.textContent = d.dataset.subheading || defaultCopy;
        if (ctaEl) ctaEl.setAttribute('href', d.dataset.link || defaultHref);
        specEls.forEach(function (el, i) {
            var value = d.dataset['spec' + (i + 1)];
            el.textContent = value || el.getAttribute('data-default');
        });
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

// Bike colour swatches (bike.php)
(function () {
    var root = document.getElementById('bikeDetail');
    if (!root) return;

    var swatches = Array.prototype.slice.call(root.querySelectorAll('.swatch'));
    var images = Array.prototype.slice.call(root.querySelectorAll('.bike-color-img'));
    var nameEl = document.getElementById('bikeColorName');
    if (!swatches.length) return;

    swatches.forEach(function (btn, i) {
        btn.addEventListener('click', function () {
            swatches.forEach(function (b, j) { b.classList.toggle('is-active', j === i); });
            images.forEach(function (img) {
                img.classList.toggle('is-active', parseInt(img.dataset.index, 10) === i);
            });
            if (nameEl) nameEl.textContent = btn.dataset.name || '';
        });
    });
})();
