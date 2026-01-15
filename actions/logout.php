<?php
session_start(); // actions/logout.php
$_SESSION = array();// Destrói todas as variáveis de sessão
session_destroy();
header("Location: ../index.php");// Redireciona para o login, // O "../" serve para VOLTAR uma pasta (sair de actions e ir para a raiz)
exit();
?>