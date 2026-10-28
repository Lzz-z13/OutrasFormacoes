<?php
// Aceita acesso pela raiz do projeto e diretamente pela pasta View.
$assets = basename($_SERVER['SCRIPT_NAME'] ?? '') === 'principal.php' ? 'assets/' : 'View/assets/';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Outras Formações</title>
    <link rel="stylesheet" href="<?= $assets ?>outras-formacoes.css">
    <script src="<?= $assets ?>outras-formacoes.js" defer></script>
</head>
<body>
    <!-- Ícones locais, sem bibliotecas externas. -->
    <svg class="icones" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <symbol id="calendario" viewBox="0 0 24 24">
            <rect x="3" y="5" width="18" height="17" rx="1"/>
            <path d="M7 2v6M17 2v6M3 10h18M9 10v12M15 10v12M3 14h18M3 18h18"/>
        </symbol>
        <symbol id="descricao" viewBox="0 0 24 24"><path d="M3 5h18M3 10h18M3 15h18M3 20h18"/></symbol>
        <symbol id="adicionar" viewBox="0 0 24 24">
            <circle cx="9" cy="6" r="4" fill="currentColor" stroke="none"/>
            <path d="M2 21v-4a7 7 0 0 1 14 0v4Z" fill="currentColor" stroke="none"/>
            <path d="M19 8v10M14 13h9"/>
        </symbol>
        <symbol id="apagar" viewBox="0 0 24 24"><path d="M3 6h18M9 6V3h6v3M6 6l1 15h10l1-15M10 10v7M14 10v7"/></symbol>
    </svg>
    <main>
        <div id="outrasFormacoes" class="painel" aria-labelledby="tituloOF">
            <h1 id="tituloOF">Outras Formações</h1>
            <div class="conteudo">
                <div class="div-formulario">
                    <form id="formOutrasFormacoes">
                        <div class="datas">
                            <div class="campo">
                                <label for="txtInicioOF">Data Inicial</label>
                                <div class="entrada">
                                    <svg class="icone" aria-hidden="true"><use href="#calendario"/></svg>
                                    <input type="date" id="txtInicioOF" name="txtInicioOF" required>
                                </div>
                            </div>
                            <div class="campo">
                                <label for="txtFimOF">Data Final</label>
                                <div class="entrada">
                                    <svg class="icone" aria-hidden="true"><use href="#calendario"/></svg>
                                    <input type="date" id="txtFimOF" name="txtFimOF" required>
                                </div>
                            </div>
                        </div>
                        <div class="campo descricao">
                            <!-- Nome txtDescEP mantido conforme o enunciado. -->
                            <label for="txtDescEP" class="somente-leitor">Descrição</label>
                            <div class="entrada">
                                <svg class="icone" aria-hidden="true"><use href="#descricao"/></svg>
                                <input type="text" id="txtDescEP" name="txtDescEP" placeholder="Ex: Curso de inglês - Inglês City" maxlength="255" required>
                            </div>
                        </div>
                        <div class="acoes">
                            <button type="submit" id="btnAddOF" name="btnAddOF" title="Adicionar formação" aria-label="Adicionar formação">
                                <svg class="icone" aria-hidden="true"><use href="#adicionar"/></svg>
                            </button>
                        </div>
                    </form>
                </div>
                <div class="div-tabela">
                    <table>
                        <caption class="somente-leitor">Outras formações adicionadas</caption>
                        <thead><tr><th scope="col">Início</th><th scope="col">Fim</th><th scope="col">Descrição</th><th scope="col">Apagar</th></tr></thead>
                        <tbody id="listaOutrasFormacoes"></tbody>
                    </table>
                </div>
                <p id="statusOF" class="somente-leitor" role="status" aria-live="polite"></p>
                <noscript>Ative o JavaScript para adicionar e apagar formações nesta interface.</noscript>
            </div>
        </div>
    </main>
</body>
</html>
