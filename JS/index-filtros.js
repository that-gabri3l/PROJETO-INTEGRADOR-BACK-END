'use strict';

async function aplicarFiltros(url) {
    const posicaoX = window.scrollX;
    const posicaoY = window.scrollY;
    const filtroAtual = document.querySelector('.job-filter');
    const filtroAberto = filtroAtual ? filtroAtual.open : false;

    try {
        const resposta = await fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                Accept: 'text/html',
            },
        });

        if (!resposta.ok) {
            throw new Error('Não foi possível atualizar as vagas.');
        }

        const html = await resposta.text();
        const documento = new DOMParser().parseFromString(html, 'text/html');
        const novaGrade = documento.querySelector('.job-grid');
        const novoFiltro = documento.querySelector('.job-filter');
        const gradeAtual = document.querySelector('.job-grid');
        const filtroAtualizado = document.querySelector('.job-filter');

        if (!novaGrade) {
            throw new Error('Resultado de vagas inválido.');
        }

        if (gradeAtual) {
            gradeAtual.replaceWith(novaGrade);
        }

        if (novoFiltro && filtroAtualizado) {
            novoFiltro.open = filtroAberto;
            filtroAtualizado.replaceWith(novoFiltro);
        }

        const urlAtual = new URL(url, window.location.href);
        history.pushState({}, '', urlAtual.pathname + urlAtual.search);

        window.requestAnimationFrame(function () {
            window.scrollTo(posicaoX, posicaoY);
        });
    } catch (erro) {
        console.error(erro);
        window.location.assign(url);
    }
}

document.addEventListener('click', function (evento) {
    const link = evento.target.closest('[data-preservar-posicao]');
    if (link && link.matches('.filter-tag')) {
        evento.preventDefault();
        aplicarFiltros(link.href);
    }
});

document.addEventListener('change', function (evento) {
    if (evento.target.matches('#ordem-destaques')) {
        const formularioOrdenacao = evento.target.closest('.sort-control');
        if (formularioOrdenacao) {
            const parametros = new URLSearchParams(new FormData(formularioOrdenacao));
            aplicarFiltros('/index.php?' + parametros.toString());
        }
    }
});
