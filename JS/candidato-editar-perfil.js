"use strict";

const campoFotoPerfil = document.getElementById('foto_perfil');
const fotoLabel = document.getElementById('foto-perfil-label');

if (campoFotoPerfil && fotoLabel) {
    const textoPadraoFoto = fotoLabel.dataset.defaultText;

    campoFotoPerfil.addEventListener('change', function () {
        const arquivo = campoFotoPerfil.files[0];
        if (!arquivo) {
            campoFotoPerfil.setCustomValidity('');
            fotoLabel.textContent = textoPadraoFoto;
            return;
        }

        fotoLabel.textContent = `${arquivo.name} (${(arquivo.size / 1024 / 1024).toFixed(2)} MB)`;
        if (arquivo.size > 2 * 1024 * 1024) {
            fotoLabel.textContent += ' · O limite é 2 MB.';
            campoFotoPerfil.setCustomValidity('A foto deve ter no máximo 2 MB.');
            campoFotoPerfil.reportValidity();
            return;
        }

        campoFotoPerfil.setCustomValidity('');
    });
}
