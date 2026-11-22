<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verifica se existe uma sessão autenticada.
if (empty($_SESSION['usuario_idUsuario'])) {
    header("Location: login.php");
    exit();
}

// Aceita somente os níveis existentes no banco.
$nivel = $_SESSION['usuario_nivel'] ?? '';

if (!in_array($nivel, ['admin', 'colaborador'], true)) {
    http_response_code(403);
    exit('Acesso negado. Nivel de usuario invalido.');
}

// Restringe páginas e operações aos administradores.
function restringirAdmin(): void
{
    if (($_SESSION['usuario_nivel'] ?? '') !== 'admin') {
        header("Location: index.php?erro=acesso_negado");
        exit();
    }
}

?>