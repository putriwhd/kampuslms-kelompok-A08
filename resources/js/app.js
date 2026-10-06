const menuToggle = document.getElementById('menuToggle');
const siteNav = document.getElementById('siteNav');

if (menuToggle && siteNav) {
    menuToggle.addEventListener('click', () => {
        const isOpen = siteNav.classList.toggle('open');

        menuToggle.setAttribute('aria-expanded', String(isOpen));
        menuToggle.textContent = isOpen ? '✕' : '☰';
    });

    siteNav.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => {
            siteNav.classList.remove('open');
            menuToggle.setAttribute('aria-expanded', 'false');
            menuToggle.textContent = '☰';
        });
    });
}

const scrollTop = document.getElementById('scrollTop');

if (scrollTop) {
    window.addEventListener('scroll', () => {
        scrollTop.classList.toggle('show', window.scrollY > 300);
    });

    scrollTop.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
}