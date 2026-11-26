"use strict";

const campoFotoPerfil = document.getElementById('foto_perfil');
const fotoLabel = document.getElementById('foto-perfil-label');
const fotoPreview = document.getElementById('foto-perfil-preview');
let urlFotoPreview = null;

if (campoFotoPerfil && fotoLabel && fotoPreview) {
    const textoPadraoFoto = fotoLabel.dataset.defaultText;

    campoFotoPerfil.addEventListener('change', function () {
        const arquivo = campoFotoPerfil.files[0];
        if (urlFotoPreview) {
            URL.revokeObjectURL(urlFotoPreview);
            urlFotoPreview = null;
        }

        if (!arquivo) {
            campoFotoPerfil.setCustomValidity('');
            fotoLabel.textContent = textoPadraoFoto;
            fotoPreview.hidden = true;
            fotoPreview.removeAttribute('src');
            return;
        }

        fotoLabel.textContent = `${arquivo.name} (${(arquivo.size / 1024 / 1024).toFixed(2)} MB)`;
        if (arquivo.size > 2 * 1024 * 1024) {
            fotoLabel.textContent += ' · O limite é 2 MB.';
            campoFotoPerfil.setCustomValidity('A foto deve ter no máximo 2 MB.');
            campoFotoPerfil.reportValidity();
            fotoPreview.hidden = true;
            fotoPreview.removeAttribute('src');
            return;
        }

        campoFotoPerfil.setCustomValidity('');
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
