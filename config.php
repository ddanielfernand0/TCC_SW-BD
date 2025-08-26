<?php
// config.php

// Definições de conexão com o banco de dados
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root'); // Ex: root
define('DB_PASSWORD', '');   // Ex: "" ou "root"
define('DB_NAME', 'bd');

// Tenta estabelecer a conexão com o banco de dados
$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Verifica se a conexão foi bem-sucedida
if($link === false){
    // Se houver um erro, exibe uma mensagem e encerra o script
    die("ERRO: Não foi possível conectar ao banco de dados. " . mysqli_connect_error());
}

// Define o charset para UTF-8 para evitar problemas com acentuação
mysqli_set_charset($link, "utf8mb4");
?>
