"use strict";
// Etapa View: as linhas permanecem somente enquanto a página está aberta.
const formularioOF = document.getElementById("formOutrasFormacoes");
const inicioOF = document.getElementById("txtInicioOF");
const fimOF = document.getElementById("txtFimOF");
const descricaoOF = document.getElementById("txtDescEP");
const listaOF = document.getElementById("listaOutrasFormacoes");
const statusOF = document.getElementById("statusOF");

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
  evento.preventDefault();
  validarDatasOF();
  const descricao = descricaoOF.value.trim();
  descricaoOF.setCustomValidity(
    descricao ? "" : "Informe a descrição da formação.",
  );
  if (!formularioOF.reportValidity()) return;
  const linha = document.createElement("tr");
  for (const valor of [
    inicioOF.value.split("-").reverse().join("/"),
    fimOF.value.split("-").reverse().join("/"),
    descricao,
  ]) {
    const celula = document.createElement("td");
    celula.textContent = valor;
    linha.appendChild(celula);
  }
  const celulaApagar = document.createElement("td");
  const apagar = document.createElement("button");
  apagar.type = "button";
  apagar.className = "apagar";
  apagar.title = "Apagar formação";
  apagar.setAttribute("aria-label", "Apagar formação: " + descricao);
  const icone = document.createElementNS("http://www.w3.org/2000/svg", "svg");
  icone.classList.add("icone");
  icone.setAttribute("aria-hidden", "true");
  const desenho = document.createElementNS("http://www.w3.org/2000/svg", "use");
  desenho.setAttribute("href", "#apagar");
  icone.appendChild(desenho);
  apagar.appendChild(icone);
  apagar.addEventListener("click", () => {
    const proximo =
      linha.nextElementSibling?.querySelector("button") ||
      linha.previousElementSibling?.querySelector("button");
    linha.remove();
    statusOF.textContent = "Formação apagada: " + descricao;
    (proximo || inicioOF).focus();
  });
  celulaApagar.appendChild(apagar);
  linha.appendChild(celulaApagar);
  listaOF.appendChild(linha);
  formularioOF.reset();
  statusOF.textContent = "Formação adicionada: " + descricao;
  inicioOF.focus();
});
