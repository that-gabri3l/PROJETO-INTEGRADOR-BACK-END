'use strict';

const passwordInput = document.getElementById('senha-login');
const passwordToggle = document.querySelector('.password-toggle');

if (passwordInput && passwordToggle) {
    passwordToggle.addEventListener('click', () => {
        const senhaVisivel = passwordInput.type === 'password';

        passwordInput.type = senhaVisivel ? 'text' : 'password';
        passwordToggle.textContent = senhaVisivel ? 'Ocultar' : 'Mostrar';
        passwordToggle.setAttribute('aria-label', senhaVisivel ? 'Ocultar senha' : 'Mostrar senha');
        passwordToggle.setAttribute('aria-pressed', String(senhaVisivel));
    });
}
