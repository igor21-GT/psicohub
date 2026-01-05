<?php
// index.php
require_once 'config/db.php';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login - PsicoHub</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Estilo específico apenas para o login para centralizar */
        body { justify-content: center; align-items: center; }
        .login-box { width: 300px; padding: 20px; background: white; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        input { width: 100%; padding: 10px; margin: 10px 0; }
        button { width: 100%; padding: 10px; background: #2c3e50; color: white; border: none; cursor: pointer; }
        button:hover { background: #34495e; }
    </style>
</head>
<body>

    <div class="login-box">
        <h2 style="text-align:center">Login</h2>
        <form action="valida_login.php" method="POST">
            <input type="email" name="email" placeholder="Seu e-mail" required>
            <input type="password" name="senha" placeholder="Sua senha" required>
            <button type="submit">Entrar</button>
        </form>
    </div>

</body>
</html>