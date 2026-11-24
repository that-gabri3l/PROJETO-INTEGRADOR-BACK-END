'use strict';

const formularioExclusao = document.querySelector('[data-confirm-delete]');

if (formularioExclusao) {
    formularioExclusao.addEventListener('submit', function (evento) {
        if (!window.confirm('Tem certeza que deseja excluir esta vaga? Esta ação também removerá as candidaturas e vagas salvas relacionadas.')) {
            evento.preventDefault();
        }
    });
}
