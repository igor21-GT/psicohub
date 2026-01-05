<?php
// valida_login.php
session_start(); // Inicia a sessão para salvar o usuário logado
require_once 'config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    // Prepara a busca no banco para evitar Hackers (SQL Injection)
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verifica se achou o usuário E se a senha bate
    // OBS: Como criamos a senha "123" manualmente no banco, comparamos direto.
    // Em produção, usaríamos password_verify($senha, $user['senha'])
    if ($user && $user['senha'] == $senha) {
        // Sucesso! Salva os dados na sessão
        $_SESSION['usuario_id'] = $user['id'];
        $_SESSION['usuario_nome'] = $user['nome'];
        
        header("Location: dashboard.php");
        exit();
    } else {
        // Erro: Volta pro login com aviso
        echo "<script>alert('E-mail ou senha incorretos!'); window.location.href='index.php';</script>";
    }
}
?>