'use strict'

let currentStep = 1;
const totalSteps = 3;

function changeStep(direction) {
    const passoAtualEl = document.getElementById(`step-${currentStep}`);

    // Se estiver avançando, valida os campos obrigatórios do passo atual antes de esconder
    if (direction === 1) {
        const inputsObrigatorios = passoAtualEl.querySelectorAll('input[required], select[required], textarea[required]');
        for (let input of inputsObrigatorios) {
            if (!input.checkValidity()) {
                input.reportValidity(); // Mostra o balãozinho de erro do navegador na hora
                return; // Trava o avanço até preencher corretamente
            }
        }
    }

    // Esconde o passo atual
    passoAtualEl.classList.remove('active');

    // Atualiza o indicador
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

    // Atualiza o contador do passo
    currentStep += direction;

    // Mostra o novo passo
    document.getElementById(`step-${currentStep}`).classList.add('active');

    // Atualiza visual do indicador do novo passo
    document.getElementById(`indicator-${currentStep}`).classList.remove('step-done');
    document.getElementById(`indicator-${currentStep}`).classList.add('step-active');
    document.getElementById(`indicator-${currentStep}`).querySelector('.step-circle').innerHTML = currentStep;

    // Controla a visibilidade dos botões
    document.getElementById('btnPrev').style.display = (currentStep === 1) ? 'none' : 'block';

    if (currentStep === totalSteps) {
        document.getElementById('btnNext').style.display = 'none';
        document.getElementById('btnSubmit').style.display = 'block';
    } else {
        document.getElementById('btnNext').style.display = 'block';
        document.getElementById('btnSubmit').style.display = 'none';
    }
}

let regimesSelecionados = [];
function toggleRegime(btnElement, valor) {
    btnElement.classList.toggle('active');

    if (regimesSelecionados.includes(valor)) {
        regimesSelecionados = regimesSelecionados.filter(item => item !== valor);
    } else {
        regimesSelecionados.push(valor);
    }

    document.getElementById('regime_input').value = regimesSelecionados.join(',');
}