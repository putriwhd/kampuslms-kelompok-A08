/* =========================================================
   KAMPUSLMS - GLOBAL JAVASCRIPT
   File: resources/js/app.js
   ========================================================= */


/* =========================================================
   1. MOBILE MENU
   ========================================================= */

const menuToggle = document.getElementById('menuToggle');
const siteNav = document.getElementById('siteNav');

if (menuToggle && siteNav) {
    menuToggle.addEventListener('click', () => {
        siteNav.classList.toggle('open');

        const isOpen = siteNav.classList.contains('open');

        menuToggle.setAttribute('aria-expanded', isOpen);

        menuToggle.setAttribute(
            'aria-label',
            isOpen ? 'Tutup menu' : 'Buka menu'
        );
    });
}


/* =========================================================
   2. ROLE SELECTOR
   ========================================================= */

const roleSelect = document.getElementById('roleSelect');
const roleSelectorForm = document.getElementById('roleSelectorForm');

if (roleSelect && roleSelectorForm) {
    roleSelect.addEventListener('change', () => {
        roleSelectorForm.submit();
    });
}


/* =========================================================
   3. SCROLL TO TOP
   ========================================================= */

const scrollTop = document.getElementById('scrollTop');

if (scrollTop) {
    const updateScrollButton = () => {
        if (window.scrollY > 300) {
            scrollTop.classList.add('show');
        } else {
            scrollTop.classList.remove('show');
        }
    };

    window.addEventListener('scroll', updateScrollButton);

    scrollTop.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });

    updateScrollButton();
}