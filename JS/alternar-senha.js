'use strict';

const passwordToggles = document.querySelectorAll('.password-toggle');

passwordToggles.forEach((passwordToggle) => {
    const passwordInput = document.getElementById(passwordToggle.getAttribute('aria-controls'));

    if (!passwordInput) {
        return;
    }

    passwordToggle.addEventListener('click', () => {
        const senhaVisivel = passwordInput.type === 'password';

        passwordInput.type = senhaVisivel ? 'text' : 'password';
        passwordToggle.textContent = senhaVisivel ? 'Ocultar' : 'Mostrar';
        passwordToggle.setAttribute('aria-label', senhaVisivel ? 'Ocultar senha' : 'Mostrar senha');
        passwordToggle.setAttribute('aria-pressed', String(senhaVisivel));
    });
});
