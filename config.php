<?php
// config.php
$host = "127.0.0.1"; // Usar IP evita problemas de DNS do localhost
$port = 3308;        // Sua porta do MySQL
$user = "root";
$pass = "";          // Padrão XAMPP é vazio
$db   = "Ecommerce"; // Verifique se o nome no MySQL é exatamente este

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");
?>