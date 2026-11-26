(function () {
'use strict';

    const chavePosicaoSalvamento = 'vagas-posicao-salvamento';
    const formulariosSalvarVaga = document.querySelectorAll('form[action="/src/vagas-salvas.php"]');

    formulariosSalvarVaga.forEach(function (formulario) {
        formulario.addEventListener('submit', function () {
            sessionStorage.setItem(chavePosicaoSalvamento, String(window.scrollY));
        });
    });

    if ('scrollRestoration' in history) {
        history.scrollRestoration = 'manual';
    }

    const posicaoSalva = sessionStorage.getItem(chavePosicaoSalvamento);
    if (posicaoSalva !== null) {
        sessionStorage.removeItem(chavePosicaoSalvamento);
        const posicao = Number(posicaoSalva);

        if (Number.isFinite(posicao)) {
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
})();
