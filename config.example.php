<?php
// Copie este arquivo para config.php e preencha os dados do seu servidor.

$host = "SEU_HOST_MYSQL";
$port = 3306;
$user = "SEU_USUARIO_MYSQL";
$pass = "SUA_SENHA_MYSQL";
$db   = "NOME_DO_BANCO";

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die("Erro de conexão com o banco de dados.");
}

$conn->set_charset("utf8mb4");
?>