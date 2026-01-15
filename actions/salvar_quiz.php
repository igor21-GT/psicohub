<?php
// actions/salvar_quiz.php
session_start();
require_once '../config/db.php';

// Verifica se veio do formulário
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../minhas_turmas.php");
    exit();
}

try {
    // INICIA A TRANSAÇÃO (Tudo ou Nada)
    $pdo->beginTransaction();

    // 1. Recebe os dados básicos
    $turma_id = $_POST['turma_id'];
    $titulo = $_POST['titulo'];
    $descricao = $_POST['descricao'];
    
    // Gera um token único para o link do aluno (ex: a1b2c3d4...)
    $token = bin2hex(random_bytes(8)); 

    // 2. Insere o Quiz (Cabeçalho)
    $stmt = $pdo->prepare("INSERT INTO quizzes (turma_id, titulo, descricao, token_acesso) VALUES (:turma, :titulo, :desc, :token)");
    $stmt->execute([
        ':turma' => $turma_id,
        ':titulo' => $titulo,
        ':desc' => $descricao,
        ':token' => $token
    ]);
    
    // Pega o ID do quiz que acabamos de criar
    $quiz_id = $pdo->lastInsertId();

    // 3. Processa as Questões
    if (isset($_POST['questoes']) && is_array($_POST['questoes'])) {
        
        // Prepara as queries fora do loop para ser mais rápido
        $stmtQuestao = $pdo->prepare("INSERT INTO questoes (quiz_id, enunciado) VALUES (:quiz_id, :enunciado)");
        $stmtOpcao = $pdo->prepare("INSERT INTO opcoes (questao_id, texto_opcao, eh_correta) VALUES (:q_id, :texto, :correta)");

        foreach ($_POST['questoes'] as $q) {
            // Insere a Pergunta
            $stmtQuestao->execute([
                ':quiz_id' => $quiz_id,
                ':enunciado' => $q['enunciado']
            ]);
            $questao_id = $pdo->lastInsertId();

            // 4. Processa as Alternativas daquela Pergunta
            // $q['opcoes'] é um array com os textos das opções
            // $q['correta'] é o índice (0, 1, 2...) da opção correta marcada no radio button
            foreach ($q['opcoes'] as $index => $texto_opcao) {
                // Verifica se este índice é o correto
                $eh_correta = ($index == $q['correta']) ? 1 : 0;

                $stmtOpcao->execute([
                    ':q_id' => $questao_id,
                    ':texto' => $texto_opcao,
                    ':correta' => $eh_correta
                ]);
            }
        }
    }

    // Se chegou até aqui sem erro, SALVA TUDO
    $pdo->commit();

    // Redireciona de volta para a turma
    header("Location: ../ver_turma.php?id=" . $turma_id . "&status=sucesso");
    exit();

} catch (Exception $e) {
    // Se deu erro, cancela tudo que foi feito
    $pdo->rollBack();
    die("Erro ao salvar o quiz: " . $e->getMessage());
}