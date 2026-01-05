<?php
// config/db.php
$host = 'localhost';
$dbname = 'psicohub_db'; // Nome do seu banco de dados
$username = 'root';      // Seu usuário do MySQL
$password = '';          // Sua senha do MySQL

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    // Configura para gerar erro em caso de falha
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}
?>