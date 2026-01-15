<?php
// salvar_turma.php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['usuario_id'])) { header("Location: index.php"); exit(); }

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'];
    $disciplina = $_POST['disciplina']; // Novo
    $turno = $_POST['turno'];           // Novo
    $descricao = $_POST['descricao'];
    $horario = $_POST['horario'];
    $status = $_POST['status'];

    if(!empty($nome)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO turmas (nome, disciplina, turno, descricao, horario, status) VALUES (:nome, :disciplina, :turno, :descricao, :horario, :status)");
            $stmt->execute([
                ':nome' => $nome,
                ':disciplina' => $disciplina,
                ':turno' => $turno,
                ':descricao' => $descricao,
                ':horario' => $horario,
                ':status' => $status
            ]);
            
            header("Location: minhas_turmas.php?msg=sucesso");
            exit();
        } catch (PDOException $e) {
            echo "Erro ao salvar: " . $e->getMessage();
        }
    }
}
?>