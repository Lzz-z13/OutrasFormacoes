<?php
require_once __DIR__ . '/ConexaoBD.php';

class OutrasFormacoes
{
    private $id;
    private $idusuario;
    private $inicio;
    private $fim;
    private $descricao;
    private $conexao;

    public function __construct(?ConexaoBD $conexao = null)
    {
        $this->conexao = $conexao ?? new ConexaoBD();
    }

    public function setID($id)
    {
        $this->id = $id;
    }
    public function getID()
    {
        return $this->id;
    }
    public function setIdUsuario($idusuario)
    {
        $this->idusuario = $idusuario;
    }
    public function getIdUsuario()
    {
        return $this->idusuario;
    }
    public function setInicio($inicio)
    {
        $this->inicio = $inicio;
    }
    public function getInicio()
    {
        return $this->inicio;
    }
    public function setFim($fim)
    {
        $this->fim = $fim;
    }
    public function getFim()
    {
        return $this->fim;
    }
    public function setDescricao($descricao)
    {
        $this->descricao = $descricao;
    }
    public function getDescricao()
    {
        return $this->descricao;
    }

    /** Confirma a existência do usuário da sessão/demonstração. */
    public function usuarioExiste(int $idusuario): bool
    {
        $conn = $this->conexao->conectar();
        try {
            $stmt = $conn->prepare('SELECT idusuario FROM usuario WHERE idusuario = ?');
            try {
                $stmt->bind_param('i', $idusuario);
                $stmt->execute();
                $stmt->store_result();
                return $stmt->num_rows === 1;
            } finally {
                $stmt->close();
            }
        } finally {
            $conn->close();
        }
    }

    /** Insere os valores já validados pelo Controller. */
    public function inserirBD(): bool
    {
        $conn = $this->conexao->conectar();
        try {
            $stmt = $conn->prepare(
                'INSERT INTO outrasformacoes (idusuario, inicio, fim, descricao) VALUES (?, ?, ?, ?)'
            );
            try {
                $stmt->bind_param('isss', $this->idusuario, $this->inicio, $this->fim, $this->descricao);
                $stmt->execute();
                $this->id = $stmt->insert_id;
                return $stmt->affected_rows === 1;
            } finally {
                $stmt->close();
            }
        } finally {
            $conn->close();
        }
    }

    /** Remove somente um registro pertencente ao usuário atual. */
    public function excluirBD(int $id, int $idusuario): bool
    {
        $conn = $this->conexao->conectar();
        try {
            $stmt = $conn->prepare('DELETE FROM outrasformacoes WHERE idoutrasformacoes = ? AND idusuario = ?');
            try {
                $stmt->bind_param('ii', $id, $idusuario);
                $stmt->execute();
                return $stmt->affected_rows === 1;
            } finally {
                $stmt->close();
            }
        } finally {
            $conn->close();
        }
    }

    /** Retorna arrays de dados, sem gerar HTML nem expor mysqli_result à View. */
    public function listaFormacoes(int $idusuario): array
    {
        $conn = $this->conexao->conectar();
        try {
            $stmt = $conn->prepare(
                'SELECT idoutrasformacoes, inicio, fim, descricao FROM outrasformacoes
                 WHERE idusuario = ? ORDER BY inicio DESC, idoutrasformacoes DESC'
            );
            try {
                $stmt->bind_param('i', $idusuario);
                $stmt->execute();
                $stmt->bind_result($id, $inicio, $fim, $descricao);
                $registros = [];
                while ($stmt->fetch()) {
                    $registros[] = ['idoutrasformacoes' => $id, 'inicio' => $inicio, 'fim' => $fim, 'descricao' => $descricao];
                }
                return $registros;
            } finally {
                $stmt->close();
            }
        } finally {
            $conn->close();
        }
    }
}
