<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Corrigido para 'usuario_idUsuario' (mesmo nome que está no seu login.php)
if (!isset($_SESSION['usuario_idUsuario'])) {
    header("Location: login.php");
    exit();
}

// 2. Função auxiliar para restringir páginas apenas para Admin
// Adicionado o '??' para evitar erro caso o nível não esteja definido
function restringirAdmin() {
    $nivel = $_SESSION['usuario_nivel'] ?? 'user';
    if ($nivel !== 'admin') {
        echo "<script>alert('Acesso negado: Área exclusiva para Administradores.'); window.location.href='index.php';</script>";
        exit();
    }
}
?>