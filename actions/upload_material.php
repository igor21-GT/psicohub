<?php
// upload_material.php
session_start();
require_once 'config/db.php'; // Ajuste o caminho se necessário (ex: '../config/db.php')

// Segurança: verifica login
if (!isset($_SESSION['usuario_id'])) { header("Location: index.php"); exit(); }

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $turma_id = $_POST['turma_id'];
    $titulo = $_POST['titulo'];
    $tipo = $_POST['tipo']; // Pode ser: 'Video', 'PDF', 'Atividade' ou 'Imagem'
    $conteudo = $_POST['conteudo'] ?? '';
    $arquivo_path = null;

    // Lógica para Upload de Arquivos (PDF ou Imagem)
    // Verificamos se o tipo exige upload E se o arquivo foi enviado sem erros
    if (($tipo == 'PDF' || $tipo == 'Imagem') && isset($_FILES['arquivo']) && $_FILES['arquivo']['error'] == 0) {
        
        $nomeOriginal = $_FILES['arquivo']['name'];
        $ext = strtolower(pathinfo($nomeOriginal, PATHINFO_EXTENSION));
        
        // Validação de segurança: extensões permitidas
        $permitidos = ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($ext, $permitidos)) {
            // Gera nome único para não sobrescrever arquivos
            $novo_nome = uniqid() . "." . $ext;
            
            // Verifica/Cria pasta uploads
            if (!is_dir('uploads')) {
                mkdir('uploads', 0777, true);
            }

            $destino = "uploads/" . $novo_nome;

            if (move_uploaded_file($_FILES['arquivo']['tmp_name'], $destino)) {
                $arquivo_path = $destino;
            } else {
                die("Erro ao salvar o arquivo. Verifique permissões da pasta uploads.");
            }
        } else {
            die("Erro: Tipo de arquivo não permitido. Apenas PDF e Imagens.");
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
        
        // Volta para a página da turma
        // Nota: Ajuste o caminho do header se seu ver_turma estiver em outra pasta
        header("Location: ver_turma.php?id=$turma_id&status=sucesso");
        exit();

    } catch (PDOException $e) {
        echo "Erro no banco: " . $e->getMessage();
    }
}
?>