<?php
session_start();
session_unset();   // Libera todas as variáveis de sessão atualmente registradas
session_destroy(); // Destrói todos os dados associados à sessão
header("Location: login.php");
exit();
?>