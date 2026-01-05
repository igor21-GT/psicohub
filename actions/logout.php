<?php
session_start();  // logout.php
$_SESSION = array();  // Limpa todas as variáveis de sessão
session_destroy();   // Destrói a sessão
header("Location: index.php");   // Redireciona para o login
exit();
?>