'use strict'

let currentStep = 1;
const totalSteps = 3;

function changeStep(direction) {
    // Esconde o passo atual
    document.getElementById(`step-${currentStep}`).classList.remove('active');

    // Atualiza o indicador (marcar como concluído se estiver avançando)
    if (direction === 1) {
        document.getElementById(`indicator-${currentStep}`).classList.remove('step-active');
        document.getElementById(`indicator-${currentStep}`).classList.add('step-done');
        document.getElementById(`indicator-${currentStep}`).querySelector('.step-circle').innerHTML = '✓';
        if (currentStep < totalSteps) document.getElementById(`line-${currentStep}`).classList.add('step-done-line');
    } else {
        // Se estiver voltando, remove o visual de concluído do passo que estamos saindo
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

// Script para gerenciar os botões de Regime de Trabalho
let regimesSelecionados = [];
function toggleRegime(btnElement, valor) {
    btnElement.classList.toggle('active');

    if (regimesSelecionados.includes(valor)) {
        regimesSelecionados = regimesSelecionados.filter(item => item !== valor);
    } else {
        regimesSelecionados.push(valor);
    }

    // Salva no input escondido para enviar pro Back-End (ex: "Presencial,Remoto")
    document.getElementById('regime_input').value = regimesSelecionados.join(',');
}