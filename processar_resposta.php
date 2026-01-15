<?php
// actions/processar_resposta.php
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Acesso inválido.");
}

// 1. Recebe os dados
$quiz_id = $_POST['quiz_id'];
$token = $_POST['token'];
$nome_aluno = trim($_POST['nome_aluno']);
$respostas_aluno = $_POST['respostas'] ?? []; // Array com [questao_id => opcao_id]

if (empty($nome_aluno)) {
    die("Por favor, volte e preencha seu nome.");
}

try {
    // 2. Busca o gabarito oficial do banco
    // Precisamos saber quais opções são as corretas (eh_correta = 1)
    $stmt = $pdo->prepare("
        SELECT q.id as questao_id, o.id as opcao_correta_id 
        FROM questoes q
        JOIN opcoes o ON q.id = o.questao_id
        WHERE q.quiz_id = :quiz_id AND o.eh_correta = 1
    ");
    $stmt->execute(['quiz_id' => $quiz_id]);
    $gabarito = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // Cria array [questao_id => opcao_correta_id]

    // 3. Calcula a Nota
    $total_questoes = count($gabarito);
    $acertos = 0;

    if ($total_questoes > 0) {
        foreach ($gabarito as $q_id => $correta_id) {
            // Verifica se o aluno respondeu essa questão E se acertou o ID
            if (isset($respostas_aluno[$q_id]) && $respostas_aluno[$q_id] == $correta_id) {
                $acertos++;
            }
        }
        
        // Regra de 3 simples para nota de 0 a 10
        $nota_final = ($acertos / $total_questoes) * 10;
    } else {
        $nota_final = 0;
    }

    // 4. Salva no Banco
    $stmtSalvar = $pdo->prepare("INSERT INTO respostas_alunos (quiz_id, nome_aluno, nota_final) VALUES (:quiz, :nome, :nota)");
    $stmtSalvar->execute([
        ':quiz' => $quiz_id,
        ':nome' => $nome_aluno,
        ':nota' => $nota_final
    ]);

} catch (Exception $e) {
    die("Erro ao salvar sua prova: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultado | PsicoHub</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #12141a;
            color: #e0e0e0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            text-align: center;
        }
        .result-card {
            background-color: #1e2129;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            max-width: 400px;
            width: 90%;
            border-top: 5px solid <?php echo ($nota_final >= 6) ? '#28a745' : '#dc3545'; ?>;
        }
        h1 { margin-bottom: 10px; color: #fff; }
        .score {
            font-size: 3rem;
            font-weight: bold;
            color: <?php echo ($nota_final >= 6) ? '#28a745' : '#dc3545'; ?>;
            margin: 20px 0;
        }
        .btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #2d303e;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
    </style>
</head>
<body>

<div class="result-card">
    <h1>Prova Enviada!</h1>
    <p>Obrigado, <strong><?php echo htmlspecialchars($nome_aluno); ?></strong>.</p>
    
    <div class="score">
        <?php echo number_format($nota_final, 1); ?>
    </div>
    <p>Sua Nota Final</p>

    <a href="../responder_quiz.php?token=<?php echo $token; ?>" class="btn">Voltar ao Início</a>
</div>

</body>
</html>