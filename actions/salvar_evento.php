<?php
// salvar_evento.php
session_start();
require_once 'config/db.php';

// Apenas usuários logados
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titulo = $_POST['titulo'];
    $data_evento = $_POST['data_evento'];
    $tipo = $_POST['tipo'];

    if(!empty($titulo) && !empty($data_evento)) {
        try {
            // Insere no banco
            $stmt = $pdo->prepare("INSERT INTO agendamentos (titulo, data_evento, tipo) VALUES (:titulo, :data_evento, :tipo)");
            $stmt->execute([
                ':titulo' => $titulo,
                ':data_evento' => $data_evento,
                ':tipo' => $tipo
            ]);
            
            // Volta para o planejador com sucesso
            header("Location: planejador.php?msg=sucesso");
            exit();
        } catch (PDOException $e) {
            echo "Erro ao agendar: " . $e->getMessage();
        }
    } else {
        echo "Título e Data são obrigatórios.";
    }
}
?>