<?php

class ConexaoBD
{
    /** Abre conexão com a configuração central e propaga erros ao Controller. */
    public function conectar(): mysqli
    {
        $config = require __DIR__ . '/../Config/database.php';
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $conexao = new mysqli(
            $config['host'],
            $config['username'],
            $config['password'],
            $config['database'],
            $config['port']
        );
        $conexao->set_charset('utf8mb4');
        return $conexao;
    }
}
