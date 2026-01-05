<?php
// salvar_anotacao.php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['usuario_id'])) { header("Location: index.php"); exit(); }

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $turma_id = $_POST['turma_id'];
    $conteudo = $_POST['conteudo'];

    if(!empty($conteudo) && !empty($turma_id)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO anotacoes (turma_id, conteudo) VALUES (:id, :conteudo)");
            $stmt->execute([
                ':id' => $turma_id,
                ':conteudo' => $conteudo
            ]);
            
            // Volta para a mesma página de detalhes
            header("Location: ver_turma.php?id=" . $turma_id);
            exit();
        } catch (PDOException $e) {
            echo "Erro: " . $e->getMessage();
        }
    }
}
header("Location: minhas_turmas.php");
?>