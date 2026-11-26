'use strict'

const formularioCandidato = document.getElementById('registerForm');
let currentStep = formularioCandidato ? Number(formularioCandidato.dataset.currentStep || 1) : 1;
const totalSteps = 3;

function changeStep(direction) {
    const passoAtualEl = document.getElementById(`step-${currentStep}`);

    // Se estiver avançando, valida os campos obrigatórios do passo atual antes de esconder
    if (direction === 1) {
        const camposEtapa = passoAtualEl.querySelectorAll('input:not([type="hidden"]), select, textarea');
        for (let input of camposEtapa) {
            if (!input.checkValidity()) {
                input.reportValidity(); // Mostra o balãozinho de erro do navegador na hora
                return; // Trava o avanço até preencher corretamente
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

const campoRegime = document.getElementById('regime_input');
let regimesSelecionados = campoRegime && campoRegime.value !== '' ? campoRegime.value.split(',') : [];
function toggleRegime(btnElement, valor) {
    btnElement.classList.toggle('active');

    if (regimesSelecionados.includes(valor)) {
        regimesSelecionados = regimesSelecionados.filter(item => item !== valor);
    } else {
        regimesSelecionados.push(valor);
    }

    campoRegime.value = regimesSelecionados.join(',');
}

if (formularioCandidato) {
    const campoFoto = formularioCandidato.querySelector('input[name="foto_perfil"]');
    const fotoLabel = document.getElementById('foto-perfil-label');
    const fotoPreview = document.getElementById('foto-preview');
    let urlFotoPreview = null;

    if (campoFoto && fotoLabel && fotoPreview) {
        const textoPadraoFoto = fotoLabel.dataset.defaultText;

        campoFoto.addEventListener('change', function () {
            const arquivo = campoFoto.files[0];
            if (urlFotoPreview) {
                URL.revokeObjectURL(urlFotoPreview);
                urlFotoPreview = null;
            }

            if (!arquivo) {
                campoFoto.setCustomValidity('');
                fotoLabel.textContent = textoPadraoFoto;
                fotoPreview.hidden = true;
                fotoPreview.removeAttribute('src');
                return;
            }

            fotoLabel.textContent = `${arquivo.name} (${(arquivo.size / 1024 / 1024).toFixed(2)} MB)`;
            if (arquivo.size > 2 * 1024 * 1024) {
                fotoLabel.textContent += ' · O limite é 2 MB.';
                campoFoto.setCustomValidity('A foto deve ter no máximo 2 MB.');
                campoFoto.reportValidity();
                fotoPreview.hidden = true;
                return;
            }

            campoFoto.setCustomValidity('');
            if (arquivo.type === 'image/jpeg' || arquivo.type === 'image/png') {
                urlFotoPreview = URL.createObjectURL(arquivo);
                fotoPreview.src = urlFotoPreview;
                fotoPreview.hidden = false;
            } else {
                fotoPreview.hidden = true;
                fotoPreview.removeAttribute('src');
            }
        });
    }

    formularioCandidato.addEventListener('submit', function (evento) {
        const senha = formularioCandidato.querySelector('input[name="senha"]');
        const confirmacao = formularioCandidato.querySelector('input[name="confirmar_senha"]');

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

        const botaoEnvio = formularioCandidato.querySelector('#btnSubmit');
        botaoEnvio.disabled = true;
        botaoEnvio.textContent = 'Criando perfil...';
    });
}