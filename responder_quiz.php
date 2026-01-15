<?php
// responder_quiz.php
require_once 'config/db.php';

// 1. Verifica se tem token
if (!isset($_GET['token'])) {
    die("Link inválido.");
}

$token = $_GET['token'];

// 2. Busca o Quiz pelo Token
$stmt = $pdo->prepare("SELECT * FROM quizzes WHERE token_acesso = :token");
$stmt->execute(['token' => $token]);
$quiz = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$quiz) {
    die("Quiz não encontrado ou link expirado.");
}

// 3. Busca as Questões e Opções
$stmtQ = $pdo->prepare("SELECT * FROM questoes WHERE quiz_id = :id ORDER BY id ASC");
$stmtQ->execute(['id' => $quiz['id']]);
$questoes = $stmtQ->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($quiz['titulo']); ?> | PsicoHub</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --bg-color: #12141a;
            --card-bg: #1e2129;
            --primary: #6f42c1;
            --text: #e0e0e0;
            --border: #2d303e;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-color);
            color: var(--text);
            line-height: 1.6;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        /* Cabeçalho do Quiz */
        .quiz-header {
            background-color: var(--card-bg);
            border-top: 5px solid var(--primary);
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        }

        .quiz-title {
            font-size: 2rem;
            margin-bottom: 10px;
            color: #fff;
        }

        .quiz-desc {
            color: #aeb0b8;
            font-size: 1rem;
        }

        /* Card de Identificação */
        .student-info {
            background-color: var(--card-bg);
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 30px;
            border: 1px solid var(--border);
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #fff;
        }

        .form-input {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid var(--border);
            background-color: #12141a;
            color: #fff;
            font-size: 1rem;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary);
        }

        /* Questões */
        .question-card {
            background-color: var(--card-bg);
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 20px;
            border: 1px solid var(--border);
        }

        .enunciado {
            font-size: 1.1rem;
            color: #fff;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .option-label {
            display: flex;
            align-items: center;
            padding: 12px;
            border: 1px solid var(--border);
            border-radius: 8px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .option-label:hover {
            background-color: #2a2e3a;
            border-color: #4a4e5a;
        }

        .option-radio {
            margin-right: 15px;
            transform: scale(1.2);
            accent-color: var(--primary);
        }

        /* Botão Enviar */
        .btn-submit {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 15px 30px;
            font-size: 1.1rem;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            font-weight: 600;
            margin-top: 20px;
            transition: background 0.3s;
        }

        .btn-submit:hover {
            background-color: #5a32a3;
        }
    </style>
</head>
<body>

<div class="container">
    <form action="actions/processar_resposta.php" method="POST">
        <input type="hidden" name="quiz_id" value="<?php echo $quiz['id']; ?>">
        <input type="hidden" name="token" value="<?php echo $token; ?>">

        <div class="quiz-header">
            <h1 class="quiz-title"><?php echo htmlspecialchars($quiz['titulo']); ?></h1>
            <p class="quiz-desc"><?php echo nl2br(htmlspecialchars($quiz['descricao'])); ?></p>
        </div>

        <div class="student-info">
            <div style="margin-bottom: 15px;">
                <label>Nome Completo *</label>
                <input type="text" name="nome_aluno" class="form-input" required placeholder="Seu nome completo">
            </div>
            
            <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 250px;">
                    <label>E-mail *</label>
                    <input type="email" name="email_aluno" class="form-input" required placeholder="seu@email.com">
                </div>
                <div style="flex: 1; min-width: 250px;">
                    <label>Matrícula / Código *</label>
                    <input type="text" name="matricula_aluno" class="form-input" required placeholder="Ex: 2024001">
                </div>
            </div>
        </div>

        <?php 
        $contador = 1;
        foreach ($questoes as $q): 
            // Busca as opções dessa questão
            $stmtOp = $pdo->prepare("SELECT * FROM opcoes WHERE questao_id = :qid ORDER BY id ASC");
            $stmtOp->execute(['qid' => $q['id']]);
            $opcoes = $stmtOp->fetchAll(PDO::FETCH_ASSOC);
        ?>
            <div class="question-card">
                <div class="enunciado">
                    <span style="color: var(--primary); font-weight:bold;"><?php echo $contador++; ?>.</span> 
                    <?php echo nl2br(htmlspecialchars($q['enunciado'])); ?>
                </div>

                <?php foreach ($opcoes as $op): ?>
                    <label class="option-label">
                        <input type="radio" name="respostas[<?php echo $q['id']; ?>]" value="<?php echo $op['id']; ?>" class="option-radio" required>
                        <span><?php echo htmlspecialchars($op['texto_opcao']); ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>

        <button type="submit" class="btn-submit">
            <i class="fa-solid fa-paper-plane"></i> Enviar Respostas
        </button>
        <br><br>
    </form>
</div>

</body>
</html>