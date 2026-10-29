# Outras Formações — integração MVC

## O que foi entregue

Controller `OutrasFormacoesController` com `inserir()`, `remover()` e `gerarLista()`, rotas em `index.php`, Model com consultas parametrizadas e View conectada ao banco. A lista agora é montada pelo PHP e permanece após atualizar a página, desde que o ambiente esteja configurado e as operações concluam com sucesso.

**Estado da entrega:** código revisado por leitura; nenhum comando PHP, teste de aplicação, importação SQL ou captura de tela foi executado, conforme solicitação do usuário. A homologação permanece pendente.

## Preparação no XAMPP, sem terminal

1. A pasta instalada é `C:\xampp\htdocs\OutrasFormações`. Para usar o ZIP em outro computador, extraia a pasta `OutrasFormacoes` dentro de `htdocs`.
2. Abra o painel XAMPP e inicie Apache e MySQL.
3. Abra [phpMyAdmin](http://localhost/phpmyadmin/), clique em **Importar**, selecione `database/demonstracao.sql` deste módulo e execute a importação. O script cria o banco separado `outras_formacoes_demo`, duas tabelas e o usuário de exemplo. Não importa formações prontas.
4. Confira `Config/database.php`: host `127.0.0.1`, porta `3301`, banco `outras_formacoes_demo`, usuário `root`. A senha está vazia, como no padrão do XAMPP; se você definiu uma senha, preencha-a. A senha e a conexão não foram testadas. Se seu MySQL usa outra porta, ajuste este arquivo.
5. Abra [Outras Formações](http://localhost/OutrasForma%C3%A7%C3%B5es/). Se extraiu o ZIP com o nome sem acentos, abra [OutrasFormacoes](http://localhost/OutrasFormacoes/).
6. Siga `docs/TESTES_MANUAIS.md` e salve as capturas reais em `docs/capturas/`.

O módulo precisa agora do MySQL: os dados deixaram de ser apenas linhas temporárias do navegador. Se a preparação estiver incompleta, a tela informa a falha e desabilita o botão de inserir; isso não significa uma lista vazia.

## Arquivos e responsabilidades

| Arquivo                                    | Responsabilidade                                                                                  |
| ------------------------------------------ | ------------------------------------------------------------------------------------------------- |
| `index.php`                                | Front controller: rotas, verbos HTTP, sessão, CSRF, mensagens temporárias e redirecionamento 303. |
| `Controller/OutrasFormacoesController.php` | Validação, inserção, remoção, listagem e conversão de falhas em mensagens para a View.            |
| `Model/OutrasFormacoes.php`                | Persistência com parâmetros SQL, filtro por usuário e retorno dos registros como arrays.          |
| `Model/ConexaoBD.php`                      | Conexão MySQLi com charset `utf8mb4`.                                                             |
| `Config/database.php` e `Config/app.php`   | Dados de conexão e escolha entre demonstração local e sessão do portal.                           |
| `View/principal.php`                       | Formulários POST, mensagens e tabela; saída escapada para HTML.                                   |
| `View/assets/`                             | Layout anterior e validação/confirmacão complementares em JavaScript.                             |
| `database/demonstracao.sql`                | Preparação manual de um banco exclusivo da atividade.                                             |
| `docs/`                                    | Log de co-criação, justificativas, roteiro de testes e reflexão.                                  |

Fluxo: navegador → `index.php` → Controller → Model → MySQL. No retorno, o Controller fornece os dados; `index.php` prepara a View. Após um POST, a resposta é `303 Location: index.php?acao=listar`; o GET seguinte consulta novamente o banco. Atualizar esse GET não repete a inserção/remoção. Isso não impede dois cliques/envios independentes: descrições repetidas são permitidas.

## Rotas e parâmetros

| Verbo | Rota                                   | Parâmetros                                            |
| ----- | -------------------------------------- | ----------------------------------------------------- |
| GET   | `index.php` ou `index.php?acao=listar` | Nenhum. Chama `gerarLista()`.                         |
| POST  | `index.php?acao=inserir`               | `txtInicioOF`, `txtFimOF`, `txtDescEP`, `csrf_token`. |
| POST  | `index.php?acao=remover`               | `id` (ID da formação), `csrf_token`.                  |

`btnAddOF` é o nome/id do botão. O campo `txtDescEP` foi preservado conforme a View anterior, apesar do sufixo EP. Todos os valores `idusuario` enviados por GET/POST são ignorados: o usuário vem do contexto confiável da aplicação.

Ações desconhecidas retornam HTTP 404; verbos incorretos, HTTP 405 com cabeçalho `Allow`. Acesso direto a `View/principal.php` redireciona ao front controller. Este módulo retorna páginas HTML, não uma API JSON.

### Exemplo de inserção (ilustrativo, não executado)

```http
POST /OutrasFormacoes/index.php?acao=inserir
Content-Type: application/x-www-form-urlencoded
Cookie: PHPSESSID=<sessao-do-navegador>

txtInicioOF=2026-02-01&txtFimOF=2026-06-30&txtDescEP=Curso+de+ingles+-+Instituto+Exemplo&csrf_token=<token-do-formulario>
```

Resposta esperada: `303` para a listagem. Na nova página, mensagem **Formação adicionada com sucesso.** e linha com os dados. Para remover, envie `id=<id-da-linha>&csrf_token=<token-do-formulario>` à rota `remover`, na mesma sessão.

## Validação e mensagens

- Início/fim obrigatórios em `AAAA-MM-DD`, datas reais entre os anos 1000 e 9999; fim não pode anteceder início.
- Descrição obrigatória, com até 255 caracteres Unicode; espaços nas extremidades são removidos.
- ID de remoção inteiro positivo, limitado ao tipo INT da tabela. A remoção exige também o ID do usuário atual e confirma uma linha afetada.
- Arrays enviados no lugar de texto/ID são recusados. O token CSRF é validado antes de chamar o Controller para alterações.
- Falha de validação/inserção preserva os valores do formulário na sessão, com limites de tamanho. A mensagem é exibida uma vez após o redirecionamento.

Mensagens comuns: **Informe uma data inicial válida.**, **A data final deve ser igual ou posterior à data inicial.**, **Informe a descrição da formação.**, **A descrição deve ter no máximo 255 caracteres.**, **Identificador de formação inválido.**, **Formação não encontrada para este usuário.**, **O formulário expirou. Atualize a página e tente novamente.**

Falhas do Model geram mensagens amigáveis. Detalhes técnicos vão para `error_log` do PHP (normalmente `C:\xampp\apache\logs\error.log` no XAMPP), sem exibir SQL na página. Verifique configuração, importação e serviço MySQL em caso de indisponibilidade.

## Identidade do usuário e integração com o portal

A pasta recebida não contém login. Por isso, `modo_demonstracao=true` usa a conta de exemplo de ID 1, exclusivamente em requisições de `127.0.0.1`/`::1`, no banco separado da atividade. Todos os acessos desse modo compartilham essa conta. Isso não autentica usuários e não deve ser usado como login do portal.

No portal real: configure o banco em `Config/database.php`, disponibilize a tabela `outrasformacoes` com as colunas previstas no SQL, defina `modo_demonstracao=false` e faça o login existente preencher `$_SESSION['idusuario']` com o ID autenticado (regenerando a sessão no login). Sem essa sessão, o módulo responde 401 e não permite alterações. O exemplo não implementa um sistema completo de login.

`excluirBD()` agora exige `(id, idusuario)` e `listaFormacoes()` retorna um array em vez de `mysqli_result`. Não havia outros consumidores desses métodos na pasta recebida; adapte eventuais consumidores externos. As classes legadas de usuário, formação acadêmica e experiência profissional foram mantidas como arquivos de origem e não fazem parte das rotas entregues.

## Dependências e adaptações do enunciado

PHP 7.4+ com `mysqli`, sessões e extensões padrão; MySQL/MariaDB com InnoDB; Apache 2.4 no XAMPP. Não há Composer, CDN ou dependência de `mysqlnd/get_result()`. A página funciona sem JavaScript; a validação do servidor continua obrigatória. Os `.htaccess` das pastas internas negam acesso HTTP direto no Apache.

Os campos curso, instituição, carga horária e conclusão aparecem em um **prompt sugerido**, mas não existem na View/Model fornecidos. A implementação aplica as regras aos campos reais início, fim e descrição, mantendo compatibilidade com a atividade anterior. Exigir os campos sugeridos demandaria alterar o formulário e o esquema do banco, uma expansão não feita nesta integração.

## Recomendações aplicadas e fontes

Consultas preparadas substituem concatenação SQL; a documentação do [MySQLi](https://www.php.net/manual/en/mysqli.quickstart.prepared-statements.php) fundamenta essa escolha. A View usa [htmlspecialchars](https://www.php.net/manual/en/function.htmlspecialchars.php) para tratar conteúdo como texto. O redirecionamento usa [header](https://www.php.net/manual/en/function.header.php) antes de qualquer saída. Também foram adicionados CSRF, filtro por usuário, validação no servidor e mensagens sem detalhes internos.

As seis interações e suas justificativas estão em `docs/LOG_COCRIACAO.md`. A reflexão está em `docs/REFLEXAO.md`. Resultados e imagens de testes permanecem pendentes de execução pelo aluno.
