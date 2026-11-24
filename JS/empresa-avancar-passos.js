'use strict'

const formularioEmpresa = document.querySelector('form[action="/src/processar-empresa.php"]');
let currentStep = formularioEmpresa ? Number(formularioEmpresa.dataset.currentStep || 1) : 1;
const totalSteps = 3;

if (formularioEmpresa) {
    const campoCnpj = formularioEmpresa.querySelector('input[name="cnpj"]');
    if (campoCnpj) {
        const formatarCnpj = function (valor) {
            const digitos = valor.replace(/\D/g, '').slice(0, 14);
            let cnpjFormatado = digitos.slice(0, 2);

            if (digitos.length > 2) cnpjFormatado += '.' + digitos.slice(2, 5);
            if (digitos.length > 5) cnpjFormatado += '.' + digitos.slice(5, 8);
            if (digitos.length > 8) cnpjFormatado += '/' + digitos.slice(8, 12);
            if (digitos.length > 12) cnpjFormatado += '-' + digitos.slice(12, 14);

            return cnpjFormatado;
        };

        campoCnpj.value = formatarCnpj(campoCnpj.value);
        campoCnpj.addEventListener('input', function () {
            const posicaoAtual = campoCnpj.selectionStart === null
                ? campoCnpj.value.length
                : campoCnpj.selectionStart;
            const digitosAntesDoCursor = campoCnpj.value
                .slice(0, posicaoAtual)
                .replace(/\D/g, '').length;
            const cnpjFormatado = formatarCnpj(campoCnpj.value);

            campoCnpj.value = cnpjFormatado;

            let novaPosicao = 0;
            let digitosContados = 0;
            while (novaPosicao < cnpjFormatado.length && digitosContados < digitosAntesDoCursor) {
                if (/\d/.test(cnpjFormatado[novaPosicao])) {
                    digitosContados++;
                }
                novaPosicao++;
            }

            campoCnpj.setSelectionRange(novaPosicao, novaPosicao);
        });
    }
}

function changeStep(direction) {
    const passoAtualEl = document.getElementById(`step-${currentStep}`);

    if (direction === 1) {
        const inputsObrigatorios = passoAtualEl.querySelectorAll('input[required], select[required], textarea[required]');

        for (let input of inputsObrigatorios) {
            if (!input.checkValidity()) {
                input.reportValidity();
                return;
            }
        }

        if (currentStep === totalSteps) {
            const senha = passoAtualEl.querySelector('input[name="senha"]');
            const confirmacao = passoAtualEl.querySelector('input[name="confirmar_senha"]');

            if (senha.value.length < 8) {
                senha.setCustomValidity('A senha deve ter pelo menos 8 caracteres.');
                senha.reportValidity();
                senha.setCustomValidity('');
                return;
            }

            if (senha.value !== confirmacao.value) {
                confirmacao.setCustomValidity('As senhas não conferem.');
                confirmacao.reportValidity();
                confirmacao.setCustomValidity('');
                return;
            }
        }
    }

    passoAtualEl.classList.remove('active');

    if (direction === 1) {
        document.getElementById(`indicator-${currentStep}`).classList.remove('step-active');
        document.getElementById(`indicator-${currentStep}`).classList.add('step-done');
        document.getElementById(`indicator-${currentStep}`).querySelector('.step-circle').innerHTML = '✓';
        if (currentStep < totalSteps) document.getElementById(`line-${currentStep}`).classList.add('step-done-line');
    } else {
        document.getElementById(`indicator-${currentStep}`).classList.remove('step-active');
        document.getElementById(`indicator-${currentStep}`).classList.remove('step-done');
        document.getElementById(`indicator-${currentStep}`).querySelector('.step-circle').innerHTML = currentStep;
        if (currentStep > 1) document.getElementById(`line-${currentStep - 1}`).classList.remove('step-done-line');
    }

    currentStep += direction;
    document.getElementById(`step-${currentStep}`).classList.add('active');
    document.getElementById(`indicator-${currentStep}`).classList.remove('step-done');
    document.getElementById(`indicator-${currentStep}`).classList.add('step-active');
    document.getElementById(`indicator-${currentStep}`).querySelector('.step-circle').innerHTML = currentStep;

    document.getElementById('btnPrev').style.display = (currentStep === 1) ? 'none' : 'block';

    if (currentStep === totalSteps) {
        document.getElementById('btnNext').style.display = 'none';
        document.getElementById('btnSubmit').style.display = 'block';
    } else {
        document.getElementById('btnNext').style.display = 'block';
        document.getElementById('btnSubmit').style.display = 'none';
    }
}

if (formularioEmpresa) {
    formularioEmpresa.addEventListener('submit', function (evento) {
        const camposObrigatorios = formularioEmpresa.querySelectorAll('input[required], select[required], textarea[required]');
        for (const campo of camposObrigatorios) {
            if (!campo.checkValidity()) {
                evento.preventDefault();

                const passoCampo = Number(campo.closest('.step-content').id.replace('step-', ''));
                while (currentStep > passoCampo) {
                    changeStep(-1);
                }
                while (currentStep < passoCampo) {
                    const passoAnterior = currentStep;
                    changeStep(1);
                    if (currentStep === passoAnterior) {
                        return;
                    }
                }

                campo.focus();
                campo.reportValidity();
                return;
            }
        }

        const senha = formularioEmpresa.querySelector('input[name="senha"]');
        const confirmacao = formularioEmpresa.querySelector('input[name="confirmar_senha"]');

        if (senha.value.length < 8) {
            evento.preventDefault();
            senha.setCustomValidity('A senha deve ter pelo menos 8 caracteres.');
            senha.reportValidity();
            senha.setCustomValidity('');
            return;
        }

        if (senha.value !== confirmacao.value) {
            evento.preventDefault();
            confirmacao.setCustomValidity('As senhas não conferem.');
            confirmacao.reportValidity();
            confirmacao.setCustomValidity('');
            return;
        }

        const botaoEnvio = formularioEmpresa.querySelector('#btnSubmit');
        botaoEnvio.disabled = true;
        botaoEnvio.textContent = 'Criando conta...';
    });
}