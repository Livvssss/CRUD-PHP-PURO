<?php

define('DB_HOST', 'mysql');
define('DB_NOME', 'biblioteca_pw');
define('DB_USUARIO', 'usuario_app');
define('DB_SENHA', 'senha_app_123');
define('DB_CHARSET', 'utf8mb4');

function getConexao(): PDO
{
    $dsn = "mysql:host=" . DB_HOST .
        ";dbname=" . DB_NOME .
        ";charset=" . DB_CHARSET;

    $opcoes = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    try {

        return new PDO(
            $dsn,
            DB_USUARIO,
            DB_SENHA,
            $opcoes
        );

    } catch (PDOException $e) {

        die('Erro ao conectar no banco: ' . $e->getMessage());
    }
}