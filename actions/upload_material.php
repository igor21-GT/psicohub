<?php
// upload_material.php
session_start();
require_once 'config/db.php';

// Segurança
if (!isset($_SESSION['usuario_id'])) { header("Location: index.php"); exit(); }

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $turma_id = $_POST['turma_id'];
    $titulo = $_POST['titulo'];
    $tipo = $_POST['tipo'];
    $conteudo = $_POST['conteudo'] ?? '';
    $arquivo_path = null;

    // Se for upload de PDF
    if ($tipo == 'PDF' && isset($_FILES['arquivo']) && $_FILES['arquivo']['error'] == 0) {
        $ext = pathinfo($_FILES['arquivo']['name'], PATHINFO_EXTENSION);
        // Gera nome aleatório para não dar conflito
        $novo_nome = uniqid() . "." . $ext;
        
        // Verifica se a pasta uploads existe, se não, cria (opcional)
        if (!is_dir('uploads')) {
            mkdir('uploads', 0777, true);
        }

        $destino = "uploads/" . $novo_nome;

        if (move_uploaded_file($_FILES['arquivo']['tmp_name'], $destino)) {
            $arquivo_path = $destino;
        } else {
            echo "Erro ao salvar o arquivo. Verifique permissões da pasta uploads.";
            exit();
        }
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO materiais (turma_id, titulo, tipo, conteudo, arquivo_path) VALUES (:turma_id, :titulo, :tipo, :conteudo, :arquivo_path)");
        $stmt->execute([
            ':turma_id' => $turma_id,
            ':titulo' => $titulo,
            ':tipo' => $tipo,
            ':conteudo' => $conteudo,
            ':arquivo_path' => $arquivo_path
        ]);
        
        // Volta para a aba de aula
        header("Location: ver_turma.php?id=$turma_id&tab=aula");
        exit();

    } catch (PDOException $e) {
        echo "Erro no banco: " . $e->getMessage();
    }
}
?>