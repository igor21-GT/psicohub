<?php
// index.php
require_once 'config/db.php';
session_start();

// Se já estiver logado, manda direto pro painel
if (isset($_SESSION['usuario_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - PsicoHub</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --bg-dark: #0f172a;
            --card-bg: #1e293b;
            --primary: #6366f1;
            --primary-hover: #4f46e5;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --border: #334155;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }

        body {
            background-color: var(--bg-dark);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            /* Um gradiente sutil no fundo para dar elegância */
            background-image: radial-gradient(circle at top right, #312e81 0%, transparent 40%),
                              radial-gradient(circle at bottom left, #1e1b4b 0%, transparent 40%);
        }

        .login-card {
            background-color: var(--card-bg);
            padding: 40px;
            width: 100%;
            max-width: 400px;
            border-radius: 16px;
            border: 1px solid var(--border);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            text-align: center;
        }

        /* Logo e Títulos */
        .logo-area { margin-bottom: 30px; }
        .logo-area i {
            font-size: 3rem;
            background: linear-gradient(135deg, #6366f1, #a855f7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 10px;
        }
        .logo-area h1 { font-size: 1.5rem; font-weight: 700; color: var(--text-main); letter-spacing: -0.5px; }
        .logo-area p { color: var(--text-muted); font-size: 0.9rem; margin-top: 5px; }

        /* Campos de Entrada */
        .input-group { text-align: left; margin-bottom: 20px; }
        .input-group label { display: block; margin-bottom: 8px; color: var(--text-muted); font-size: 0.85rem; font-weight: 500; }
        
        .input-wrapper { position: relative; }
        .input-wrapper i {
            position: absolute; left: 15px; top: 50%; transform: translateY(-50%);
            color: var(--text-muted); font-size: 1rem; transition: 0.3s;
        }

        .input-wrapper input {
            width: 100%;
            background-color: #0f172a;
            border: 1px solid var(--border);
            padding: 12px 12px 12px 45px; /* Espaço para o ícone */
            border-radius: 8px;
            color: white;
            font-size: 0.95rem;
            outline: none;
            transition: all 0.3s;
        }

        .input-wrapper input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
        }
        .input-wrapper input:focus + i { color: var(--primary); }

        /* Botão */
        .btn-login {
            width: 100%;
            background: linear-gradient(to right, #6366f1, #4f46e5);
            color: white;
            padding: 14px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            margin-top: 10px;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.4);
        }

        /* Rodapé do Card */
        .card-footer { margin-top: 25px; font-size: 0.85rem; color: var(--text-muted); }
        .card-footer a { color: var(--primary); text-decoration: none; font-weight: 500; }
        .card-footer a:hover { text-decoration: underline; }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="logo-area">
            <i class="fa-solid fa-brain"></i>
            <h1>PsicoHub</h1>
            <p>Faça login para gerenciar suas turmas</p>
        </div>

        <form action="actions/valida_login.php" method="POST">
            
            <div class="input-group">
                <label>E-mail Acadêmico</label>
                <div class="input-wrapper">
                    <input type="email" name="email" placeholder="exemplo@psicohub.com" required>
                    <i class="fa-regular fa-envelope"></i>
                </div>
            </div>

            <div class="input-group">
                <label>Senha</label>
                <div class="input-wrapper">
                    <input type="password" name="senha" placeholder="••••••••" required>
                    <i class="fa-solid fa-lock"></i>
                </div>
            </div>

            <button type="submit" class="btn-login">
                Entrar no Sistema <i class="fa-solid fa-arrow-right" style="margin-left: 8px;"></i>
            </button>

        </form>

        <div class="card-footer">
            <p>Esqueceu sua senha? <a href="#">Recuperar acesso</a></p>
        </div>
    </div>

</body>
</html>