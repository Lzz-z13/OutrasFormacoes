# Roteiro de testes manuais — Outras Formações

**Status de todos os casos: NÃO EXECUTADO.** O usuário pediu somente código e roteiro. Os resultados abaixo são expectativas, não comprovações. Preencha a coluna Resultado observado após executar pessoalmente. Não há capturas geradas pela IA.

## Preparação

1. Siga o README para importar o SQL em `outras_formacoes_demo`, configurar a conexão e ligar Apache/MySQL.
2. Use somente o banco da demonstração nos casos abaixo. Abra a página do módulo no navegador.
3. Para observar POST e redirecionamentos, abra F12 → Rede/Network e ative **Preserve log/Preservar registro**.
4. Para capturas, use `Win + Shift + S` e salve na pasta `docs/capturas/` os arquivos indicados, sem expor senhas ou cookies.
5. Execute T01 antes de inserir formações. Se já houver dados de demonstração, remova-os pela própria tela, sem mexer nos bancos dos outros projetos.

## Casos principais

| Caso | Passos e dados | Resultado esperado | Resultado observado / evidência |
| --- | --- | --- | --- |
| T01 — Listagem vazia | Abra a página após a preparação, sem formações cadastradas para o usuário 1. | Tabela com os quatro títulos e “Nenhuma formação cadastrada.”; não aparece alerta de conexão. | Pendente — `01-lista-vazia.png` |
| T02 — Inserção válida | Informe início `01/02/2026`, fim `30/06/2026`, descrição `Curso de inglês - Instituto Exemplo`. Clique no botão azul. | POST inserir → 303 → GET listar. Mensagem “Formação adicionada com sucesso.” e linha com datas e descrição; campos limpos. | Pendente — `02-insercao.png` |
| T03 — Refresh e persistência | Após T02, pressione F5 duas vezes; depois feche e reabra a aba. | A formação continua listada uma única vez. F5 repete GET, sem reenviar POST; mensagem de sucesso desaparece após o próximo GET. | Pendente — `03-listagem-refresh.png` |
| T04 — Campos obrigatórios | Deixe a descrição vazia; tente adicionar. Repita sem uma das datas. | O navegador impede envio e indica o campo obrigatório. Nenhuma linha é inserida. | Pendente |
| T05 — Datas invertidas | Preencha início `30/06/2026`, fim `01/02/2026` e uma descrição. Tente adicionar. | Mensagem de que a data final deve ser igual ou posterior à inicial. Nenhum registro criado. | Pendente — `04-validacao.png` |
| T06 — Limite de texto | Tente colar mais de 255 caracteres no campo de descrição. | A interface limita o texto. Para comprovar a regra no servidor, use o procedimento de requisição alterada abaixo. | Pendente |
| T07 — Cancelar remoção | Clique na lixeira da formação de T02 e escolha Cancelar. | Nenhum POST; a linha permanece. Com JavaScript desligado, a confirmação não aparece, mas a remoção por POST continua disponível. | Pendente |
| T08 — Remover existente | Clique novamente na lixeira e confirme. | POST remover → 303 → GET listar. “Formação removida com sucesso.” e ausência da linha; lista vazia se era o único item. | Pendente — `05-remocao.png` |
| T09 — Remover inexistente | Crie uma formação, abra uma segunda aba com a listagem; remova na primeira aba e depois envie a lixeira da linha desatualizada na segunda. | “Formação não encontrada para este usuário.”; nenhuma outra linha é afetada. | Pendente — `06-id-inexistente.png` |
| T10 — Falha do banco | Com a página carregada e uma formação de teste existente, pare somente o MySQL da demonstração. Tente inserir, listar e remover. Reinicie-o ao concluir. | Mensagens amigáveis; sem detalhes de SQL/senha. Na listagem, “Listagem indisponível.”, e não “Nenhuma formação cadastrada.”. Falha de inserção mantém os campos preenchidos. | Pendente |
| T11 — Sem JavaScript | Desative JavaScript no navegador, recarregue e execute uma inserção e remoção válidas. | Formulários POST e tabela funcionam. As validações do PHP continuam ativas; a lixeira remove sem diálogo JavaScript. Reative-o ao concluir. | Pendente |

## Validação direta do servidor (manual, pelo navegador)

Para não confundir bloqueio do navegador com validação do PHP: abra F12 → Elementos, localize `formOutrasFormacoes`, acrescente `novalidate` e desative o JavaScript nas configurações das ferramentas de desenvolvimento. Recarregue se necessário e reaplique as alterações ao HTML, pois recarregar restaura o documento. Preserve `csrf_token` e a sessão. Não altere o arquivo-fonte da aplicação para estes testes.

| Caso | Alteração/envio manual | Resultado esperado | Resultado observado |
| --- | --- | --- | --- |
| T12 — Data inexistente | Em Elementos, troque o input de data para `type="text"`, envie `2026-02-30`; demais campos válidos. | PHP recusa a data, retorna 303 e exibe erro. Nenhum registro salvo. | Pendente |
| T13 — Descrição inválida | Com validação do navegador desativada, envie só espaços. Depois retire `maxlength`, envie 256 caracteres e, por fim, 255 caracteres válidos. | Espaços e 256 caracteres recusados no PHP; 255 aceitos. | Pendente |
| T14 — Datas invertidas no servidor | Envie `2026-06-30` como início e `2026-02-01` como fim sem validação do navegador. | Controller recusa com mensagem de ordem das datas, mesmo sem JavaScript. | Pendente |
| T15 — ID inválido | Em um formulário de remoção existente, edite o hidden `id` para `0`, `-1` e `abc`, um de cada vez, recarregando entre tentativas. | “Identificador de formação inválido.”; banco inalterado. | Pendente |
| T16 — Token inválido | Edite `csrf_token` para `invalido` e envie um formulário; repita removendo esse hidden. | “O formulário expirou. Atualize a página e tente novamente.”; nenhuma alteração. | Pendente |
| T17 — Texto com aspas/HTML | Insira `Curso d'Inglês <b>exemplo</b>` com datas válidas. | Texto salvo literalmente, incluindo aspas e tags; não fica em negrito nem executa HTML. Remova a formação depois. | Pendente |
| T18 — Método e rota | Abra `index.php?acao=remover` pela barra do navegador; depois `index.php?acao=desconhecida`. | Respectivamente HTTP 405 com Allow POST, e HTTP 404; nenhuma alteração. | Pendente |

Restaure o navegador/recarregue ao terminar os testes com HTML alterado. Os tokens devem vir sempre do formulário recém-carregado; valores inventados não demonstram a validação dos campos, pois serão recusados primeiro pelo CSRF.

## Isolamento entre usuários (somente no banco de demonstração)

Para este teste, use phpMyAdmin → banco `outras_formacoes_demo` → SQL e execute pessoalmente os dados de apoio abaixo. Se o ID 2 já existir, confirme antes que pertence a outra conta de teste; não sobrescreva um usuário existente.

```sql
INSERT INTO usuario (idusuario, nome, cpf, email, senha)
SELECT 2, 'Segundo usuário de teste', '00000000001', 'segundo@example.invalid', '!SEM_LOGIN!'
WHERE NOT EXISTS (SELECT 1 FROM usuario WHERE idusuario = 2);
INSERT INTO outrasformacoes (idusuario, inicio, fim, descricao)
VALUES (2, '2026-01-01', '2026-01-31', 'Teste de isolamento do usuário 2');
```

| Caso | Passos | Resultado esperado | Resultado observado |
| --- | --- | --- | --- |
| T19 — Outro usuário | Veja no phpMyAdmin o ID da formação do usuário 2. Na página, que usa o usuário 1, ela não deve aparecer. Em um formulário de remoção do usuário 1, substitua manualmente o hidden `id` pelo ID da formação do usuário 2 e envie com o token válido. | A formação de usuário 2 não é listada nem excluída. Mensagem “Formação não encontrada para este usuário.”. Confirme sua permanência pelo phpMyAdmin. | Pendente |
| T20 — Usuário enviado pelo cliente | Acrescente um hidden `idusuario=2` ao formulário de inserção do usuário 1, com dados válidos. | O registro continua associado ao usuário 1. O campo do cliente é ignorado. | Pendente |
| T21 — Sessão ausente | Em Config/app.php, altere temporariamente modo_demonstracao para false. Abra janela privativa sem login. | HTTP 401, aviso para entrar no portal e nenhuma operação permitida. Restaure true ao concluir a demonstração. | Pendente |

Remova depois, pelo phpMyAdmin, apenas os registros criados para estes testes, conferindo seus IDs. Guarde as capturas reais de inserção, listagem e remoção junto ao log. Só marque um caso como aprovado após verificar o resultado observado.
