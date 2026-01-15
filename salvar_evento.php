<?php
// salvar_evento.php
session_start();
require_once 'config/db.php';

// Segurança: Apenas usuários logados
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario_id = $_SESSION['usuario_id']; // PEGA O ID DO PROFESSOR LOGADO
    $titulo = trim($_POST['titulo']);
    $data_evento = $_POST['data_evento'];
    $tipo = $_POST['tipo'];

    if(!empty($titulo) && !empty($data_evento)) {
        try {
            // Insere no banco vinculando ao usuário
            $stmt = $pdo->prepare("INSERT INTO agendamentos (usuario_id, titulo, data_evento, tipo) VALUES (:uid, :titulo, :data_evento, :tipo)");
            $stmt->execute([
                ':uid' => $usuario_id,
                ':titulo' => $titulo,
                ':data_evento' => $data_evento,
                ':tipo' => $tipo
            ]);
            
            // Volta para o planejador
            header("Location: planejador.php?msg=sucesso");
            exit();
        } catch (PDOException $e) {
            die("Erro ao agendar: " . $e->getMessage());
        }
    } else {
        // Se faltar dados, volta com erro
        header("Location: planejador.php?erro=campos_vazios");
        exit();
    }
}
?>