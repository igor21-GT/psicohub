<?php
// hub_turma.php (VERSÃO FINAL: ALUNO RESPONDE TEXTO OU MARCA X)
session_start();
require_once 'config/db.php';

$id_codificado = $_GET['t'] ?? '';
$turma_id = base64_decode($id_codificado);

if (!$turma_id || !is_numeric($turma_id)) { die("Link inválido."); }
$stmt = $pdo->prepare("SELECT * FROM turmas WHERE id = ?"); $stmt->execute([$turma_id]); $turma = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$turma) { die("Turma não encontrada."); }
$stmt = $pdo->prepare("SELECT * FROM materiais WHERE turma_id = ? ORDER BY id DESC"); $stmt->execute([$turma_id]); $materiais = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($turma['nome']); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #0f172a; color: #f1f5f9; margin: 0; padding: 0; min-height: 100vh; display: flex; flex-direction: column; align-items: center; }
        .container { width: 100%; max-width: 700px; padding: 20px; }
        .material-item { background: #1e293b; border: 1px solid #334155; padding: 15px; border-radius: 8px; margin-bottom: 10px; display: flex; align-items: center; justify-content: space-between; }
        .btn-entrega { background: #ea580c; color: #fff; padding: 6px 12px; border-radius: 6px; text-decoration: none; cursor: pointer; font-size: 0.8rem; }
        
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); }
        .modal-content { background: #1e293b; margin: 10% auto; padding: 20px; width: 90%; max-width: 500px; border-radius: 10px; border: 1px solid #334155; }
        .form-group { margin-bottom: 15px; }
        textarea, input[type=text] { width: 100%; background: #0f172a; color: white; border: 1px solid #334155; padding: 10px; border-radius: 6px; }
    </style>
</head>
<body>
    <div class="container">
        <h2 style="text-align:center;"><?php echo htmlspecialchars($turma['nome']); ?></h2>
        
        <?php foreach($materiais as $m): 
            $formato = $m['formato'] ?? 'upload';
            $isAtiv = ($m['tipo'] == 'Atividade');
            $opcoes = json_decode($m['opcoes_json'] ?? '{}', true);
        ?>
            <div class="material-item">
                <div>
                    <strong><?php echo htmlspecialchars($m['titulo']); ?></strong>
                    <div style="font-size:0.8rem; color:#94a3b8;"><?php echo htmlspecialchars($m['conteudo']); ?></div>
                </div>
                <?php if($isAtiv): ?>
                    <button onclick='abrirModal(<?php echo json_encode($m); ?>, <?php echo json_encode($opcoes); ?>)' class="btn-entrega">Responder</button>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <div id="modalEntrega" class="modal">
        <div class="modal-content">
            <span onclick="document.getElementById('modalEntrega').style.display='none'" style="float:right; cursor:pointer;">&times;</span>
            <h3 id="tituloModal">Responder</h3>
            <p id="descModal" style="color:#94a3b8;"></p>
            
            <form action="actions/enviar_atividade.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="material_id" id="inputMaterialId">
                <input type="hidden" name="turma_token" value="<?php echo $id_codificado; ?>">
                
                <div class="form-group"><label>Seu Nome</label><input type="text" name="aluno_nome" required></div>

                <div id="areaResposta"></div>

                <button type="submit" class="btn-entrega" style="width:100%; margin-top:10px;">Enviar Resposta</button>
            </form>
        </div>
    </div>

    <script>
        function abrirModal(item, opcoes) {
            document.getElementById('inputMaterialId').value = item.id;
            document.getElementById('tituloModal').innerText = item.titulo;
            document.getElementById('descModal').innerText = item.conteudo;
            const area = document.getElementById('areaResposta');
            area.innerHTML = '';

            if (item.formato === 'discursiva') {
                area.innerHTML = '<label>Sua Resposta:</label><textarea name="resposta_texto" rows="5" required></textarea>';
            } else if (item.formato === 'multipla') {
                let html = '<label>Escolha uma opção:</label><br>';
                if(opcoes.a) html += `<input type="radio" name="resposta_texto" value="A) ${opcoes.a}"> A) ${opcoes.a}<br>`;
                if(opcoes.b) html += `<input type="radio" name="resposta_texto" value="B) ${opcoes.b}"> B) ${opcoes.b}<br>`;
                if(opcoes.c) html += `<input type="radio" name="resposta_texto" value="C) ${opcoes.c}"> C) ${opcoes.c}<br>`;
                if(opcoes.d) html += `<input type="radio" name="resposta_texto" value="D) ${opcoes.d}"> D) ${opcoes.d}<br>`;
                area.innerHTML = html;
            } else {
                area.innerHTML = '<label>Envie seu arquivo:</label><input type="file" name="arquivo" required>';
            }
            document.getElementById('modalEntrega').style.display = 'block';
        }
    </script>
</body>
</html>