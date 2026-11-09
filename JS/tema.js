'use strict'

const themeToggleBtn = document.getElementById('theme-toggle');
const htmlElement = document.documentElement;

// Verifica se o usuário já tinha escolhido o tema escuro antes
if (localStorage.getItem('theme') === 'dark') {
    htmlElement.setAttribute('data-theme', 'dark');
}

// Função do clique
themeToggleBtn.addEventListener('click', () => {
    if (htmlElement.getAttribute('data-theme') === 'dark') {
        htmlElement.removeAttribute('data-theme');
        localStorage.setItem('theme', 'light');
    } else {
        htmlElement.setAttribute('data-theme', 'dark');
        localStorage.setItem('theme', 'dark');
    }
});