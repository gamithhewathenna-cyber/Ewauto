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
