'use strict';

const campoLogo = document.getElementById('logo');
const logoFeedback = document.getElementById('logo-feedback');
const logoPreview = document.getElementById('logo-preview');
let urlLogoPreview = null;

if (campoLogo && logoFeedback && logoPreview) {
    campoLogo.addEventListener('change', function () {
        const arquivo = campoLogo.files[0];
        if (urlLogoPreview) {
            URL.revokeObjectURL(urlLogoPreview);
            urlLogoPreview = null;
        }

        if (!arquivo) {
            logoFeedback.textContent = 'Nenhuma logo selecionada.';
            logoPreview.hidden = true;
            logoPreview.removeAttribute('src');
            return;
        }

        logoFeedback.textContent = `${arquivo.name} (${(arquivo.size / 1024 / 1024).toFixed(2)} MB)`;
        if (arquivo.size > 2 * 1024 * 1024) {
            logoFeedback.textContent += ' · O limite é 2 MB.';
            campoLogo.setCustomValidity('A logo deve ter no máximo 2 MB.');
            campoLogo.reportValidity();
            logoPreview.hidden = true;
            return;
        }

        campoLogo.setCustomValidity('');
        if (arquivo.type === 'image/jpeg' || arquivo.type === 'image/png') {
            urlLogoPreview = URL.createObjectURL(arquivo);
            logoPreview.src = urlLogoPreview;
            logoPreview.hidden = false;
        } else {
            logoPreview.hidden = true;
            logoPreview.removeAttribute('src');
        }
    });
}