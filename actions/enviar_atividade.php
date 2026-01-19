<?php
// actions/enviar_atividade.php (FINAL: ACEITA TEXTO OU ARQUIVO)
session_start();
require_once '../config/db.php';

// Configurações
$tamanho_maximo_bytes = 40 * 1024 * 1024; // 40MB

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $material_id = filter_input(INPUT_POST, 'material_id', FILTER_VALIDATE_INT);
    $nome_aluno = trim($_POST['aluno_nome']);
    $resposta_texto = $_POST['resposta_texto'] ?? null;
    $turma_token = $_POST['turma_token']; 

    if (!$material_id || empty($nome_aluno)) {
        $_SESSION['msg'] = "Preencha seu nome.";
        $_SESSION['msg_tipo'] = "erro";
        header("Location: ../hub_turma.php?t=" . $turma_token);
        exit();
    }

    $arquivo_path = null;

    // SE TIVER ARQUIVO (UPLOAD), TENTA SALVAR
    if (!empty($_FILES['arquivo']['name'])) {
        $erro_upload = $_FILES['arquivo']['error'];
        if ($erro_upload !== UPLOAD_ERR_OK) {
             // Tratamento de erro básico
             $_SESSION['msg'] = "Erro no envio do arquivo. Cód: $erro_upload";
             $_SESSION['msg_tipo'] = "erro";
             header("Location: ../hub_turma.php?t=" . $turma_token);
             exit();
        }

        $pasta_raiz = dirname(__DIR__); 
        $diretorio_destino = $pasta_raiz . '/uploads/';

        if (!is_dir($diretorio_destino)) {
            mkdir($diretorio_destino, 0777, true);
        }

        $info_arquivo = pathinfo($_FILES['arquivo']['name']);
        $extensao = isset($info_arquivo['extension']) ? strtolower($info_arquivo['extension']) : 'bin';
        $nome_aluno_limpo = preg_replace('/[^a-zA-Z0-9]/', '', $nome_aluno);
        $novo_nome = $material_id . "_" . $nome_aluno_limpo . "_" . time() . "." . $extensao;
        $caminho_final_hd = $diretorio_destino . $novo_nome;

        if (move_uploaded_file($_FILES['arquivo']['tmp_name'], $caminho_final_hd)) {
            $arquivo_path = 'uploads/' . $novo_nome;
        } else {
            $_SESSION['msg'] = "Falha ao salvar arquivo.";
            $_SESSION['msg_tipo'] = "erro";
            header("Location: ../hub_turma.php?t=" . $turma_token);
            exit();
        }
    }

    try {
        // Salva no Banco (Pode ser Arquivo, Texto ou os dois)
        $stmt = $pdo->prepare("INSERT INTO entregas (material_id, aluno_nome, arquivo_path, resposta_texto) VALUES (:mid, :nome, :path, :txt)");
        $stmt->execute([
            'mid' => $material_id,
            'nome' => $nome_aluno,
            'path' => $arquivo_path,
            'txt' => $resposta_texto
        ]);

        $_SESSION['msg'] = "Atividade entregue com sucesso!";
        $_SESSION['msg_tipo'] = "sucesso";

    } catch (Exception $e) {
        $_SESSION['msg'] = "Erro no banco: " . $e->getMessage();
        $_SESSION['msg_tipo'] = "erro";
    }

    header("Location: ../hub_turma.php?t=" . $turma_token);
    exit();
}