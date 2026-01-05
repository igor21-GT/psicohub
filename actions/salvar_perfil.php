<?php
// salvar_perfil.php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['usuario_id'])) { header("Location: index.php"); exit(); }

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_SESSION['usuario_id'];
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $nova_senha = $_POST['nova_senha'];
    $confirma_senha = $_POST['confirma_senha'];

    // Atualiza nome e email
    $sql = "UPDATE usuarios SET nome = :nome, email = :email";
    $params = [':nome' => $nome, ':email' => $email, ':id' => $id];

    // Se usuário digitou senha nova
    if (!empty($nova_senha)) {
        if ($nova_senha === $confirma_senha) {
            // Em produção use: $senhaHash = password_hash($nova_senha, PASSWORD_DEFAULT);
            // E no SQL: senha = :senha
            // Como estamos usando texto puro para teste:
            $sql .= ", senha = :senha";
            $params[':senha'] = $nova_senha;
        } else {
            header("Location: perfil.php?msg=erro_senha");
            exit();
        }
    }

    $sql .= " WHERE id = :id";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        // Atualiza o nome na sessão também
        $_SESSION['usuario_nome'] = $nome;

        header("Location: perfil.php?msg=sucesso");
        exit();
    } catch (PDOException $e) {
        echo "Erro: " . $e->getMessage();
    }
}
?>