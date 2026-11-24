'use strict';

document.querySelectorAll('[data-search-autocomplete]').forEach(function (container) {
    const input = container.querySelector('input[name="q"]');
    const listbox = container.querySelector('[role="listbox"]');
    const status = container.querySelector('[data-suggestions-status]');
    const endpoint = container.dataset.suggestionsUrl;

    if (!input || !listbox || !status || !endpoint) {
        return;
    }

    let timer = null;
    let controller = null;
    let sugestoes = [];
    let indiceAtivo = -1;

    function fecharSugestoes() {
        listbox.hidden = true;
        input.setAttribute('aria-expanded', 'false');
        input.removeAttribute('aria-activedescendant');
        indiceAtivo = -1;
    }

    function definirSugestaoAtiva(indice) {
        indiceAtivo = indice;
        Array.from(listbox.children).forEach(function (opcao, indiceOpcao) {
            const ativa = indiceOpcao === indiceAtivo;
            opcao.setAttribute('aria-selected', ativa ? 'true' : 'false');
            if (ativa) {
                input.setAttribute('aria-activedescendant', opcao.id);
                opcao.scrollIntoView({ block: 'nearest' });
            }
        });
    }

    function selecionarSugestao(sugestao) {
        input.value = sugestao.valor;
        fecharSugestoes();
        status.textContent = '';
        if (typeof container.closest('form').requestSubmit === 'function') {
            container.closest('form').requestSubmit();
        } else {
            container.closest('form').submit();
        }
    }

    function exibirSugestoes(resultados) {
        listbox.replaceChildren();
        sugestoes = resultados;
        indiceAtivo = -1;

        resultados.forEach(function (sugestao, indice) {
            const opcao = document.createElement('button');
            opcao.type = 'button';
            opcao.id = listbox.id + '-opcao-' + indice;
            opcao.className = 'search-suggestion';
            opcao.setAttribute('role', 'option');
            opcao.setAttribute('aria-selected', 'false');

            const valor = document.createElement('span');
            valor.className = 'search-suggestion-value';
            valor.textContent = sugestao.valor;

            const tipo = document.createElement('span');
            tipo.className = 'search-suggestion-type';
            tipo.textContent = sugestao.tipo;

            opcao.append(valor, tipo);
            opcao.addEventListener('mousedown', function (evento) {
                evento.preventDefault();
            });
            opcao.addEventListener('click', function () {
                selecionarSugestao(sugestao);
            });
            opcao.addEventListener('mouseenter', function () {
                definirSugestaoAtiva(indice);
            });
            listbox.appendChild(opcao);
        });

        listbox.hidden = resultados.length === 0;
        input.setAttribute('aria-expanded', resultados.length > 0 ? 'true' : 'false');
        status.textContent = resultados.length > 0
            ? resultados.length + (resultados.length === 1 ? ' sugestão encontrada.' : ' sugestões encontradas.')
            : 'Nenhuma sugestão encontrada.';
    }

    async function carregarSugestoes(termo) {
        if (controller) {
            controller.abort();
        }
        controller = new AbortController();
        status.textContent = 'Carregando sugestões.';

        try {
            const resposta = await fetch(endpoint + '?q=' + encodeURIComponent(termo), {
                headers: { Accept: 'application/json' },
                signal: controller.signal,
            });
            if (!resposta.ok) {
                throw new Error('Falha ao carregar sugestões (HTTP ' + resposta.status + ').');
            }

            const dados = await resposta.json();
            if (input.value.trim() !== termo) {
                return;
            }
            if (!dados || !Array.isArray(dados.sugestoes)) {
                throw new Error('Resposta de sugestões inválida.');
            }
            exibirSugestoes(dados.sugestoes);
        } catch (erro) {
            if (erro.name === 'AbortError') {
                return;
            }
            console.error(erro);
            fecharSugestoes();
            status.textContent = 'Sugestões indisponíveis. Você ainda pode pesquisar normalmente.';
        }
    }

    input.addEventListener('input', function () {
        window.clearTimeout(timer);
        if (controller) {
            controller.abort();
        }

        const termo = input.value.trim();
        if (Array.from(termo).length < 2) {
            sugestoes = [];
            fecharSugestoes();
            status.textContent = '';
            return;
        }

        timer = window.setTimeout(function () {
            carregarSugestoes(termo);
        }, 180);
    });

    input.addEventListener('keydown', function (evento) {
        if (!sugestoes.length) {
            if (evento.key === 'Escape') {
                fecharSugestoes();
            }
            return;
        }

        if (evento.key === 'ArrowDown') {
            evento.preventDefault();
            definirSugestaoAtiva((indiceAtivo + 1) % sugestoes.length);
        } else if (evento.key === 'ArrowUp') {
            evento.preventDefault();
            definirSugestaoAtiva(indiceAtivo <= 0 ? sugestoes.length - 1 : indiceAtivo - 1);
        } else if (evento.key === 'Enter' && indiceAtivo >= 0) {
            evento.preventDefault();
            selecionarSugestao(sugestoes[indiceAtivo]);
        } else if (evento.key === 'Escape') {
            fecharSugestoes();
        }
    });

    input.addEventListener('blur', function () {
        window.setTimeout(function () {
            if (!container.contains(document.activeElement)) {
                fecharSugestoes();
            }
        }, 100);
    });

    document.addEventListener('pointerdown', function (evento) {
        if (!container.contains(evento.target)) {
            fecharSugestoes();
        }
    });
});
