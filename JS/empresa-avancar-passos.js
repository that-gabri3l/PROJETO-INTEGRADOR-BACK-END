'use strict'

let currentStep = 1;
const totalSteps = 3;

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

const formulario = document.querySelector('form[action="/src/processar-empresa.php"]');
if (formulario) {
    formulario.addEventListener('submit', function (evento) {
        const senha = formulario.querySelector('input[name="senha"]');
        const confirmacao = formulario.querySelector('input[name="confirmar_senha"]');

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
        }
    });
}