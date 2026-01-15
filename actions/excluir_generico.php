<?php
// actions/excluir_generico.php
session_start();
require_once '../config/db.php'; // Note o '../' para voltar uma pasta

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
            case 'evento':
                // Apaga do Planejador (Só se for dono do evento)
                $stmt = $pdo->prepare("DELETE FROM agendamentos WHERE id = :id AND usuario_id = :uid");
                $stmt->execute(['id' => $id, 'uid' => $usuario_id]);
                break;

            case 'quiz':
                // Apaga o Quiz e as Respostas atreladas (Cascade manual se necessário)
                // Primeiro apaga respostas dos alunos
                $pdo->prepare("DELETE FROM respostas_alunos WHERE quiz_id = :id")->execute(['id' => $id]);
                // Depois apaga o quiz
                $stmt = $pdo->prepare("DELETE FROM quizzes WHERE id = :id"); // Adicionar validação de turma/professor aqui seria ideal
                $stmt->execute(['id' => $id]);
                break;

            case 'resposta':
                // Apaga uma nota específica de aluno
                $stmt = $pdo->prepare("DELETE FROM respostas_alunos WHERE id = :id");
                $stmt->execute(['id' => $id]);
                break;
                
            case 'turma':
                // Apaga uma turma inteira (Cuidado!)
                $stmt = $pdo->prepare("DELETE FROM turmas WHERE id = :id AND professor_id = :uid");
                $stmt->execute(['id' => $id, 'uid' => $usuario_id]);
                break;
        }

        // Redireciona de volta para a página de onde veio
        if(isset($_SERVER['HTTP_REFERER'])) {
            header("Location: " . $_SERVER['HTTP_REFERER']);
        } else {
            header("Location: ../dashboard.php");
        }
        exit();

    } catch (PDOException $e) {
        die("Erro ao excluir: " . $e->getMessage());
    }
} else {
    // Se tentar acessar direto sem ID
    header("Location: ../dashboard.php");
    exit();
}
?>