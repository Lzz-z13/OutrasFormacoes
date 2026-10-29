<?php
require_once __DIR__ . '/../Model/OutrasFormacoes.php';

class OutrasFormacoesController
{
    private $model;

    public function __construct(?OutrasFormacoes $model = null)
    {
        $this->model = $model ?? new OutrasFormacoes();
    }

    /** Valida os campos da View, associa o usuário e solicita a inserção ao Model. */
    public function inserir(array $dados, int $idusuario): array
    {
        $valores = [];
        foreach (['txtInicioOF', 'txtFimOF', 'txtDescEP'] as $campo) {
            $valores[$campo] = isset($dados[$campo]) && is_string($dados[$campo]) ? trim($dados[$campo]) : '';
        }
        $erros = [];
        if (!$this->dataValida($valores['txtInicioOF']))
            $erros[] = 'Informe uma data inicial válida.';
        if (!$this->dataValida($valores['txtFimOF']))
            $erros[] = 'Informe uma data final válida.';
        if (!$erros && $valores['txtFimOF'] < $valores['txtInicioOF']) {
            $erros[] = 'A data final deve ser igual ou posterior à data inicial.';
        }
        $descricao = $valores['txtDescEP'];
        if ($descricao === '' || preg_match('/\S/u', $descricao) !== 1) {
            $erros[] = 'Informe a descrição da formação.';
        } elseif (preg_match_all('/./us', $descricao) > 255) {
            $erros[] = 'A descrição deve ter no máximo 255 caracteres.';
        }
        // Limita o tamanho dos valores preservados na sessão após uma falha.
        $antigos = [
            'txtInicioOF' => substr($valores['txtInicioOF'], 0, 10),
            'txtFimOF' => substr($valores['txtFimOF'], 0, 10),
            'txtDescEP' => substr($descricao, 0, 2048),
        ];
        if ($idusuario < 1)
            return $this->resultado(false, 'Sua sessão de usuário não está disponível.', $antigos);
        if ($erros)
            return $this->resultado(false, implode(' ', $erros), $antigos);

        try {
            if (!$this->model->usuarioExiste($idusuario)) {
                return $this->resultado(false, 'Usuário não encontrado. Conclua o cadastro ou a preparação da demonstração.', $antigos);
            }
            $this->model->setIdUsuario($idusuario);
            $this->model->setInicio($valores['txtInicioOF']);
            $this->model->setFim($valores['txtFimOF']);
            $this->model->setDescricao($descricao);
            if (!$this->model->inserirBD())
                throw new RuntimeException('Inserção sem registro afetado.');
            return $this->resultado(true, 'Formação adicionada com sucesso.');
        } catch (Throwable $erro) {
            $this->registrarErro('inserir', $erro);
            return $this->resultado(false, 'Não foi possível salvar a formação. Verifique o banco de dados e tente novamente.', $antigos);
        }
    }

    /** Valida o identificador e remove apenas uma formação do usuário atual. */
    public function remover(array $dados, int $idusuario): array
    {
        $valor = $dados['id'] ?? null;
        $id = is_string($valor) || is_int($valor)
            ? filter_var($valor, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 2147483647]]) : false;
        if ($idusuario < 1)
            return $this->resultado(false, 'Sua sessão de usuário não está disponível.');
        if ($id === false)
            return $this->resultado(false, 'Identificador de formação inválido.');
        try {
            if (!$this->model->excluirBD($id, $idusuario)) {
                return $this->resultado(false, 'Formação não encontrada para este usuário.');
            }
            return $this->resultado(true, 'Formação removida com sucesso.');
        } catch (Throwable $erro) {
            $this->registrarErro('remover', $erro);
            return $this->resultado(false, 'Não foi possível remover a formação. Tente novamente.');
        }
    }

    /** Carrega as formações do usuário e diferencia lista vazia de falha no banco. */
    public function gerarLista(int $idusuario): array
    {
        if ($idusuario < 1)
            return ['sucesso' => false, 'mensagem' => 'Sua sessão de usuário não está disponível.', 'itens' => []];
        try {
            if (!$this->model->usuarioExiste($idusuario)) {
                return ['sucesso' => false, 'mensagem' => 'Usuário não encontrado. Conclua o cadastro ou a preparação da demonstração.', 'itens' => []];
            }
            return ['sucesso' => true, 'mensagem' => '', 'itens' => $this->model->listaFormacoes($idusuario)];
        } catch (Throwable $erro) {
            $this->registrarErro('gerarLista', $erro);
            return ['sucesso' => false, 'mensagem' => 'Não foi possível carregar as formações. Confira se o MySQL está ligado e o banco foi configurado.', 'itens' => []];
        }
    }

    private function dataValida(string $valor): bool
    {
        if (!preg_match('/^[1-9][0-9]{3}-[0-9]{2}-[0-9]{2}$/D', $valor))
            return false;
        $data = DateTimeImmutable::createFromFormat('!Y-m-d', $valor);
        return $data !== false && $data->format('Y-m-d') === $valor;
    }

    private function resultado(bool $sucesso, string $mensagem, array $valores = []): array
    {
        return ['sucesso' => $sucesso, 'mensagem' => $mensagem, 'valores' => $valores];
    }

    private function registrarErro(string $acao, Throwable $erro): void
    {
        // Detalhes ficam no log do servidor, nunca são enviados como HTML ao usuário.
        error_log('[OutrasFormacoes/' . $acao . '] ' . get_class($erro) . ': ' . $erro->getMessage());
    }
}
