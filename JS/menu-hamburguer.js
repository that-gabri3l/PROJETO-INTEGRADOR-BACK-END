'use strict';

document.querySelectorAll('nav.navbar').forEach((navbar) => {
    const logo = navbar.querySelector('.logo, .navbar-logo');
    let menuLinks = navbar.querySelector('.menu-links');

    if (!logo) {
        return;
    }

    if (!navbar.querySelector('.menu-toggle')) {
        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.id = 'menu-toggle';
        checkbox.className = 'menu-toggle';
        checkbox.setAttribute('aria-label', 'Abrir menu');

        const hamburger = document.createElement('label');
        hamburger.htmlFor = 'menu-toggle';
        hamburger.className = 'hamburger';
        hamburger.setAttribute('aria-label', 'Abrir menu');
        hamburger.innerHTML = '<span class="bar"></span><span class="bar"></span><span class="bar"></span>';

        logo.insertAdjacentElement('afterend', hamburger);
        hamburger.insertAdjacentElement('beforebegin', checkbox);
    }

    if (!menuLinks) {
        menuLinks = document.createElement('div');
        menuLinks.className = 'menu-links';

        Array.from(navbar.children).forEach((element) => {
            if (
                element !== logo &&
                !element.classList.contains('menu-toggle') &&
                !element.classList.contains('hamburger')
            ) {
                menuLinks.appendChild(element);
            }
        });

        const hamburger = navbar.querySelector('.hamburger');
        if (hamburger) {
            hamburger.insertAdjacentElement('afterend', menuLinks);
        } else {
            navbar.appendChild(menuLinks);
        }
    }

    const checkbox = navbar.querySelector('.menu-toggle');
    const hamburger = navbar.querySelector('.hamburger');
    if (checkbox && hamburger) {
        checkbox.addEventListener('change', () => {
            hamburger.setAttribute('aria-expanded', checkbox.checked ? 'true' : 'false');
        });
        hamburger.setAttribute('aria-expanded', 'false');
    }
});
