// RA Beauty - Main JavaScript

document.addEventListener('DOMContentLoaded', function () {

    // ---- AOS Init ----
    if (typeof AOS !== 'undefined') {
        AOS.init({ duration: 700, once: true, offset: 60 });
    }

    // ---- Navbar scroll effect ----
    const nav = document.getElementById('mainNav');
    if (nav) {
        window.addEventListener('scroll', function () {
            nav.classList.toggle('scrolled', window.scrollY > 50);
        });
    }

    // ---- Hero Swiper ----
    if (document.querySelector('.hero-swiper')) {
        new Swiper('.hero-swiper', {
            loop: true,
            autoplay: { delay: 5000, disableOnInteraction: false },
            pagination: { el: '.swiper-pagination', clickable: true },
            effect: 'fade',
            fadeEffect: { crossFade: true },
        });
    }

    // ---- Related Products Swiper ----
    if (document.querySelector('.related-swiper')) {
        new Swiper('.related-swiper', {
            slidesPerView: 1.2,
            spaceBetween: 16,
            breakpoints: {
                576: { slidesPerView: 2.2 },
                768: { slidesPerView: 2.8 },
                992: { slidesPerView: 3.2 },
            },
            navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },
        });
    }

    // ---- Back to Top ----
    const btt = document.getElementById('backToTop');
    if (btt) {
        window.addEventListener('scroll', () => {
            btt.style.display = window.scrollY > 400 ? 'flex' : 'none';
        });
        btt.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
    }

    // ---- Product filter (products page) ----
    const filterBtns = document.querySelectorAll('.filter-btn');
    if (filterBtns.length) {
        filterBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                filterBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                const cat = this.dataset.cat;
                document.querySelectorAll('.product-item').forEach(item => {
                    item.style.display = (cat === 'all' || item.dataset.cat === cat) ? '' : 'none';
                });
            });
        });
    }

    // ---- Auto dismiss alerts ----
    document.querySelectorAll('.alert-auto').forEach(el => {
        setTimeout(() => {
            el.style.transition = 'opacity 0.5s';
            el.style.opacity = '0';
            setTimeout(() => el.remove(), 500);
        }, 4000);
    });

    // ---- Product image gallery (detail page) ----
    document.querySelectorAll('.thumb-img').forEach(img => {
        img.addEventListener('click', function () {
            const main = document.getElementById('mainProductImg');
            if (main) {
                main.src = this.src;
                document.querySelectorAll('.thumb-img').forEach(t => t.classList.remove('active'));
                this.classList.add('active');
            }
        });
    });

    // ---- Smooth navbar active state ----
    const currentPath = window.location.pathname;
    document.querySelectorAll('#mainNav .nav-link').forEach(link => {
        if (link.getAttribute('href') && currentPath.endsWith(link.getAttribute('href').replace(/.*\//, ''))) {
            link.classList.add('active');
        }
    });

});
