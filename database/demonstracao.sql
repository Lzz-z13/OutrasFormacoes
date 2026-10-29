-- Preparação manual da demonstração: importar pelo phpMyAdmin.
CREATE DATABASE IF NOT EXISTS outras_formacoes_demo CHARACTER
SET
    utf8mb4 COLLATE utf8mb4_unicode_ci;

USE outras_formacoes_demo;

CREATE TABLE
    IF NOT EXISTS usuario (
        idusuario INT NOT NULL AUTO_INCREMENT,
        nome VARCHAR(120) NOT NULL,
        cpf VARCHAR(11) NOT NULL,
        email VARCHAR(190) NOT NULL,
        dataNascimento DATE NULL,
        senha VARCHAR(255) NOT NULL,
        PRIMARY KEY (idusuario),
        UNIQUE KEY uk_usuario_cpf (cpf)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE
    IF NOT EXISTS outrasformacoes (
        idoutrasformacoes INT NOT NULL AUTO_INCREMENT,
        idusuario INT NOT NULL,
        inicio DATE NOT NULL,
        fim DATE NOT NULL,
        descricao VARCHAR(255) NOT NULL,
        PRIMARY KEY (idoutrasformacoes),
        KEY idx_outrasformacoes_usuario (idusuario),
        CONSTRAINT fk_outrasformacoes_usuario FOREIGN KEY (idusuario) REFERENCES usuario (idusuario) ON DELETE RESTRICT,
        CONSTRAINT ck_outrasformacoes_datas CHECK (fim >= inicio)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Conta exclusiva da atividade, sem senha de login utilizável.
INSERT INTO
    usuario (idusuario, nome, cpf, email, senha)
SELECT
    1,
    'Usuário de exemplo',
    '00000000000',
    'exemplo@example.invalid',
    '!CONTA_DEMO_SEM_LOGIN!'
WHERE
    NOT EXISTS (
        SELECT
            1
        FROM
            usuario
        WHERE
            idusuario = 1
    );