'use strict';

const campoLogo = document.getElementById('logo');
const logoLabel = document.getElementById('logo-label');
const logoPreview = document.getElementById('logo-preview');
let urlLogoPreview = null;

if (campoLogo && logoLabel && logoPreview) {
    const textoPadraoLogo = logoLabel.dataset.defaultText;

    campoLogo.addEventListener('change', function () {
        const arquivo = campoLogo.files[0];
        if (urlLogoPreview) {
            URL.revokeObjectURL(urlLogoPreview);
            urlLogoPreview = null;
        }

        if (!arquivo) {
            campoLogo.setCustomValidity('');
            logoLabel.textContent = textoPadraoLogo;
            logoPreview.hidden = true;
            logoPreview.removeAttribute('src');
            return;
        }

        logoLabel.textContent = `${arquivo.name} (${(arquivo.size / 1024 / 1024).toFixed(2)} MB)`;
        if (arquivo.size > 2 * 1024 * 1024) {
            logoLabel.textContent += ' · O limite é 2 MB.';
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