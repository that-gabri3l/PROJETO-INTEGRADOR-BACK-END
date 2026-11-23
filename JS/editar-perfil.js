"use strict";

var formulario = document.getElementById("form-editar-perfil");
var botaoSalvar = document.getElementById("botao-salvar-perfil");

if (formulario && botaoSalvar) {
    formulario.addEventListener("submit", function () {
        botaoSalvar.disabled = true;
        botaoSalvar.textContent = "Salvando...";
    });
}
