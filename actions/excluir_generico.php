<?php
// actions/excluir_generico.php
session_start();
require_once '../config/db.php';

// Segurança: Se não tiver logado, tchau
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../index.php");
    exit();
}

if (isset($_GET['tipo']) && isset($_GET['id'])) {
    $tipo = $_GET['tipo'];
    $id = (int)$_GET['id'];
    $usuario_id = $_SESSION['usuario_id'];

    try {
        switch ($tipo) {
            case 'material':
                // 1. Apaga arquivo físico
                $stmt = $pdo->prepare("SELECT arquivo_path FROM materiais WHERE id = :id");
                $stmt->execute(['id' => $id]);
                $alvo = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($alvo && !empty($alvo['arquivo_path'])) {
                    $caminho = '../' . $alvo['arquivo_path'];
                    if (file_exists($caminho)) unlink($caminho);
                }
                // 2. Apaga do banco
                $pdo->prepare("DELETE FROM materiais WHERE id = :id")->execute(['id' => $id]);
                break;

            case 'turma':
                // Faxina completa antes de apagar a turma (IMPORTANTE)
                $stmt = $pdo->prepare("SELECT id FROM turmas WHERE id = :id AND professor_id = :uid");
                $stmt->execute(['id' => $id, 'uid' => $usuario_id]);
                if (!$stmt->fetch()) die("Acesso negado.");

                // Apagar Materiais
                $stmtMat = $pdo->prepare("SELECT arquivo_path FROM materiais WHERE turma_id = :id");
                $stmtMat->execute(['id' => $id]);
                while ($m = $stmtMat->fetch(PDO::FETCH_ASSOC)) {
                    if (!empty($m['arquivo_path'])) {
                        $arq = '../' . $m['arquivo_path'];
                        if (file_exists($arq)) unlink($arq);
                    }
                }
                $pdo->prepare("DELETE FROM materiais WHERE turma_id = :id")->execute(['id' => $id]);

                // Apagar Quizzes (Cascata)
                $stmtQ = $pdo->prepare("SELECT id FROM quizzes WHERE turma_id = :id");
                $stmtQ->execute(['id' => $id]);
                $quizzesIds = $stmtQ->fetchAll(PDO::FETCH_COLUMN);
                if (!empty($quizzesIds)) {
                    $idsStr = implode(',', $quizzesIds);
                    // Apaga respostas dos alunos nesses quizzes
                    $pdo->query("DELETE FROM respostas_alunos WHERE quiz_id IN ($idsStr)");
                    // Apaga as opções das questões desses quizzes
                    $pdo->query("DELETE FROM opcoes WHERE questao_id IN (SELECT id FROM questoes WHERE quiz_id IN ($idsStr))");
                    // Apaga as questões desses quizzes
                    $pdo->query("DELETE FROM questoes WHERE quiz_id IN ($idsStr)");
                    // Apaga os quizzes
                    $pdo->query("DELETE FROM quizzes WHERE id IN ($idsStr)");
                }

                // Apagar resto
                $pdo->prepare("DELETE FROM alunos WHERE turma_id = :id")->execute(['id' => $id]);
                $pdo->prepare("DELETE FROM anotacoes WHERE turma_id = :id")->execute(['id' => $id]);
                $pdo->prepare("DELETE FROM turmas WHERE id = :id")->execute(['id' => $id]);
                break;

            case 'evento':
                $pdo->prepare("DELETE FROM agendamentos WHERE id = :id AND usuario_id = :uid")->execute(['id' => $id, 'uid' => $usuario_id]);
                break;

            case 'quiz':
                // 1. Apagar Respostas dos Alunos vinculadas a este quiz
                $pdo->prepare("DELETE FROM respostas_alunos WHERE quiz_id = :id")->execute(['id' => $id]);
                
                // 2. Apagar Opções das Questões vinculadas a este quiz (Cascata manual)
                $pdo->prepare("DELETE FROM opcoes WHERE questao_id IN (SELECT id FROM questoes WHERE quiz_id = :id)")->execute(['id' => $id]);
                
                // 3. Apagar Questões vinculadas a este quiz
                $pdo->prepare("DELETE FROM questoes WHERE quiz_id = :id")->execute(['id' => $id]);
                
                // 4. Finalmente, apagar o Quiz
                $pdo->prepare("DELETE FROM quizzes WHERE id = :id")->execute(['id' => $id]);
                break;

            case 'aluno':
                $pdo->prepare("DELETE FROM respostas_alunos WHERE aluno_id = :id")->execute(['id' => $id]);
                $pdo->prepare("DELETE FROM alunos WHERE id = :id")->execute(['id' => $id]);
                break;
        }

        if(isset($_SERVER['HTTP_REFERER'])) {
            header("Location: " . $_SERVER['HTTP_REFERER']);
        } else {
            header("Location: ../minhas_turmas.php");
        }
        exit();

    } catch (PDOException $e) {
        die("Erro ao excluir: " . $e->getMessage());
    }
} else {
    header("Location: ../minhas_turmas.php");
    exit();
}
?>