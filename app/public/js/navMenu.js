document.addEventListener('DOMContentLoaded', () => {
    const navToggle = document.querySelector('.nav-toggle');
    const navMenu = document.querySelector('#siteNavMenu');

    if (!navToggle || !navMenu) {
        return;
    }

    navToggle.addEventListener('click', () => {
        const isOpen = navMenu.classList.toggle('is-open');

        navToggle.classList.toggle('is-open', isOpen);
        navToggle.setAttribute('aria-expanded', String(isOpen));
    });
});