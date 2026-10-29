"use strict";
// Melhoria de usabilidade. O Controller valida tudo novamente no servidor.
const formularioOF = document.getElementById("formOutrasFormacoes");
const inicioOF = document.getElementById("txtInicioOF");
const fimOF = document.getElementById("txtFimOF");
const descricaoOF = document.getElementById("txtDescEP");

function validarDatasOF() {
  fimOF.setCustomValidity(
    inicioOF.value && fimOF.value && fimOF.value < inicioOF.value
      ? "A data final deve ser igual ou posterior à data inicial."
      : "",
  );
}
inicioOF.addEventListener("input", validarDatasOF);
fimOF.addEventListener("input", validarDatasOF);
descricaoOF.addEventListener("input", () => descricaoOF.setCustomValidity(""));
formularioOF.addEventListener("submit", (evento) => {
  validarDatasOF();
  descricaoOF.setCustomValidity(
    descricaoOF.value.trim() ? "" : "Informe a descrição da formação.",
  );
  if (!formularioOF.reportValidity()) evento.preventDefault();
  // O envio válido segue por POST; somente o PHP atualiza a listagem após salvar.
});

document.querySelectorAll(".form-remover").forEach((formulario) => {
  formulario.addEventListener("submit", (evento) => {
    if (!window.confirm("Deseja apagar esta formação?"))
      evento.preventDefault();
  });
});
