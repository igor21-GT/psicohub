<?php
// actions/valida_login.php
session_start();

// ATENÇÃO: Adicionamos o "../" porque agora estamos dentro da pasta 'actions'
require_once '../config/db.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    // Prepara a busca
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verifica senha (aqui comparando texto puro conforme seu exemplo)
    if ($user && $user['senha'] == $senha) {
        $_SESSION['usuario_id'] = $user['id'];
        $_SESSION['usuario_nome'] = $user['nome'];
        
        // CORREÇÃO: Redireciona voltando uma pasta para achar o dashboard
        header("Location: ../dashboard.php");
        exit();
    } else {
        // CORREÇÃO: Volta para o login com erro
        header("Location: ../index.php?erro=1");
        exit();
    }
}
?>