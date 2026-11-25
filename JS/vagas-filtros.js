'use strict';

const chavePosicaoFiltros = 'vagas-posicao-filtros';
const formularioFiltros = document.querySelector('.vaga-filter-form');

function salvarPosicaoFiltros() {
    sessionStorage.setItem(chavePosicaoFiltros, String(window.scrollY));
}

if (formularioFiltros) {
    if ('scrollRestoration' in history) {
        history.scrollRestoration = 'manual';
    }

    formularioFiltros.addEventListener('submit', salvarPosicaoFiltros);

    document.querySelectorAll('.vaga-filter-clear, [data-preservar-posicao]').forEach(function (link) {
        link.addEventListener('click', salvarPosicaoFiltros);
    });

    const posicaoSalva = sessionStorage.getItem(chavePosicaoFiltros);
    if (posicaoSalva !== null) {
        sessionStorage.removeItem(chavePosicaoFiltros);
        const posicao = Number(posicaoSalva);
        if (!Number.isFinite(posicao)) {
            return;
        }

        function restaurarPosicao() {
            window.requestAnimationFrame(function () {
                window.scrollTo({
                    top: posicao,
                    left: 0,
                    behavior: 'auto',
                });
            });
        }

        restaurarPosicao();
        window.addEventListener('load', restaurarPosicao, { once: true });
        window.setTimeout(restaurarPosicao, 100);
    }
}
