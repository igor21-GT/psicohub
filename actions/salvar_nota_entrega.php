<?php
// actions/salvar_nota_entrega.php
session_start();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $entrega_id = filter_input(INPUT_POST, 'entrega_id', FILTER_VALIDATE_INT);
    $nota = filter_input(INPUT_POST, 'nota', FILTER_VALIDATE_FLOAT);
    $redirect_id = $_POST['material_id']; // Para voltar pra página certa

    if ($entrega_id) {
        $stmt = $pdo->prepare("UPDATE entregas SET nota = :nota WHERE id = :id");
        $stmt->execute(['nota' => $nota, 'id' => $entrega_id]);
    }
    
    header("Location: ../ver_entregas.php?id=" . $redirect_id);
    exit();
}