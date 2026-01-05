<?php
// excluir_generico.php
session_start();
require_once 'config/db.php';

// Segurança
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}

if (isset($_GET['id']) && isset($_GET['tipo'])) {
    $id = $_GET['id'];
    $tipo = $_GET['tipo'];

    try {
        if ($tipo == 'turma') {
            // Apaga Turma
            $stmt = $pdo->prepare("DELETE FROM turmas WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $redirect = "minhas_turmas.php";
        
        } elseif ($tipo == 'evento') {
            // Apaga Agendamento
            $stmt = $pdo->prepare("DELETE FROM agendamentos WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $redirect = "planejador.php";
        
        } elseif ($tipo == 'anotacao') {
            // Apaga Anotação
            $stmtcheck = $pdo->prepare("SELECT turma_id FROM anotacoes WHERE id = :id");
            $stmtcheck->execute(['id' => $id]);
            $nota = $stmtcheck->fetch();
            $turma_id = $nota['turma_id'];

            $stmt = $pdo->prepare("DELETE FROM anotacoes WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $redirect = "ver_turma.php?id=" . $turma_id;

        } elseif ($tipo == 'material') {
            // APAGA MATERIAL (Novo)
            // Primeiro busca info para saber se tem arquivo físico para deletar
            $stmtcheck = $pdo->prepare("SELECT arquivo_path, turma_id FROM materiais WHERE id = :id");
            $stmtcheck->execute(['id' => $id]);
            $mat = $stmtcheck->fetch();
            
            // Se tiver arquivo na pasta, apaga
            if ($mat && $mat['arquivo_path'] && file_exists($mat['arquivo_path'])) {
                unlink($mat['arquivo_path']);
            }

            $stmt = $pdo->prepare("DELETE FROM materiais WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $redirect = "ver_turma.php?id=" . $mat['turma_id'];
        }

        header("Location: $redirect?msg=deletado");
        exit();

    } catch (PDOException $e) {
        echo "Erro ao excluir: " . $e->getMessage();
    }
} else {
    header("Location: dashboard.php");
}
?>