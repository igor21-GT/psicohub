<?php
// actions/upload_material.php (FINAL: SUPORTA DISCURSIVA E MULTIPLA)
session_start();
require_once '../config/db.php';

$TAMANHO_MAXIMO = 40 * 1024 * 1024; 
$TIPOS_PERMITIDOS = ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'webp', 'doc', 'docx', 'ppt', 'pptx', 'zip', 'rar'];

if (!isset($_SESSION['usuario_id'])) { header("Location: ../index.php"); exit(); }

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $turma_id = filter_input(INPUT_POST, 'turma_id', FILTER_VALIDATE_INT);
    $titulo = trim($_POST['titulo']);
    $tipo = $_POST['tipo'];
    $conteudo = $_POST['conteudo'] ?? '';
    $data_limite = $_POST['data_limite'] ?? null;
    $valor_nota = $_POST['valor_nota'] ?? null;
    $formato = $_POST['formato'] ?? 'upload'; // Novo campo

    // Tratamento JSON para Multipla Escolha
    $opcoes_json = null;
    if ($formato == 'multipla') {
        $opts = [
            'a' => $_POST['opt_a'] ?? '',
            'b' => $_POST['opt_b'] ?? '',
            'c' => $_POST['opt_c'] ?? '',
            'd' => $_POST['opt_d'] ?? ''
        ];
        $opcoes_json = json_encode($opts);
    }

    if (empty($data_limite)) $data_limite = null;
    if ($valor_nota === '') $valor_nota = null;

    $arquivo_path = null;

    // Se for upload, processa o arquivo
    if (($tipo == 'PDF' || $tipo == 'Imagem') && isset($_FILES['arquivo']) && $_FILES['arquivo']['error'] == 0) {
        // ... (Lógica de upload de sempre)
        $arquivo = $_FILES['arquivo'];
        $nomeOriginal = $arquivo['name'];
        $ext = strtolower(pathinfo($nomeOriginal, PATHINFO_EXTENSION));
        if (!in_array($ext, $TIPOS_PERMITIDOS)) die("Arquivo invalido");
        
        $pasta_turma = 'turma_' . $turma_id;
        $diretorio_fisico = '../uploads/' . $pasta_turma . '/';
        if (!is_dir($diretorio_fisico)) mkdir($diretorio_fisico, 0777, true);
        
        $novo_nome = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9\._-]/', '', pathinfo($nomeOriginal, PATHINFO_FILENAME)) . "." . $ext;
        if (move_uploaded_file($arquivo['tmp_name'], $diretorio_fisico . $novo_nome)) {
            $arquivo_path = 'uploads/' . $pasta_turma . '/' . $novo_nome;
        }
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO materiais (turma_id, titulo, tipo, conteudo, arquivo_path, data_limite, valor_nota, formato, opcoes_json) VALUES (:tid, :tit, :tipo, :cont, :path, :limit, :valor, :fmt, :opts)");
        $stmt->execute([
            'tid' => $turma_id, 'tit' => $titulo, 'tipo' => $tipo, 'cont' => $conteudo,
            'path' => $arquivo_path, 'limit' => $data_limite, 'valor' => $valor_nota,
            'fmt' => $formato, 'opts' => $opcoes_json
        ]);
        
        header("Location: ../ver_turma.php?id=$turma_id&status=sucesso");
        exit();

    } catch (PDOException $e) {
        echo "Erro no banco: " . $e->getMessage();
    }
}
?>