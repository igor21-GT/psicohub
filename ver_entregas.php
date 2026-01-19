<?php
// ver_entregas.php (VERSÃO COM DETECTOR DE ATRASO)
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['usuario_id']) || !isset($_GET['id'])) { header("Location: minhas_turmas.php"); exit(); }
$material_id = (int)$_GET['id']; $usuario_id = $_SESSION['usuario_id'];

// Busca atividade e data limite
$stmt = $pdo->prepare("SELECT m.titulo, m.turma_id, m.data_limite, t.nome as nome_turma FROM materiais m JOIN turmas t ON m.turma_id = t.id WHERE m.id = :mid AND t.professor_id = :uid");
$stmt->execute(['mid' => $material_id, 'uid' => $usuario_id]);
$atividade = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$atividade) { die("Acesso negado."); }

$stmt = $pdo->prepare("SELECT * FROM entregas WHERE material_id = :mid ORDER BY aluno_nome ASC");
$stmt->execute(['mid' => $material_id]);
$entregas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Correção: <?php echo htmlspecialchars($atividade['titulo']); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body { background-color: #0f172a; color: #f1f5f9; font-family: 'Inter', sans-serif; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; }
        .header-box { background: #1e293b; padding: 20px; border-radius: 12px; border: 1px solid #334155; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }
        .header-box h2 { margin: 0; font-size: 1.2rem; }
        .header-box p { margin: 5px 0 0 0; color: #94a3b8; font-size: 0.9rem; }
        .delivery-item { background: #1e293b; border: 1px solid #334155; padding: 15px; border-radius: 8px; margin-bottom: 10px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px; }
        .aluno-info strong { display: block; color: #f1f5f9; font-size: 1rem; }
        .aluno-info span { color: #94a3b8; font-size: 0.85rem; }
        .grade-area { display: flex; align-items: center; gap: 10px; background: #0f172a; padding: 8px; border-radius: 6px; border: 1px solid #334155; }
        .input-nota { background: transparent; border: none; border-bottom: 1px solid #6366f1; color: white; width: 50px; text-align: center; font-weight: bold; font-size: 1rem; }
        .btn-download { text-decoration: none; padding: 8px 15px; border-radius: 6px; font-size: 0.9rem; background: #334155; color: white; display: flex; align-items: center; gap: 8px; }
        .nota-badge { background: #065f46; color: #34d399; padding: 2px 8px; border-radius: 4px; font-size: 0.8rem; font-weight: bold; }
        .tag-atraso { background: #7f1d1d; color: #fca5a5; font-size: 0.7rem; padding: 2px 6px; border-radius: 4px; font-weight: bold; margin-left: 5px; }
    </style>
</head>
<body>
<div class="container">
    <a href="ver_turma.php?id=<?php echo $atividade['turma_id']; ?>" style="color: #94a3b8; text-decoration: none; display: inline-block; margin-bottom: 15px;"><i class="fa-solid fa-arrow-left"></i> Voltar</a>
    <div class="header-box">
        <div><h2>Correção</h2><p>Atividade: <strong><?php echo htmlspecialchars($atividade['titulo']); ?></strong> 
        <?php if($atividade['data_limite']): echo " (Prazo: ".date('d/m/Y', strtotime($atividade['data_limite'])).")"; endif; ?></p></div>
        <div style="text-align: right;"><span style="font-size: 1.5rem; font-weight: bold; color: #6366f1;"><?php echo count($entregas); ?></span><span style="display: block; font-size: 0.8rem; color: #94a3b8;">Entregas</span></div>
    </div>
    <?php if(count($entregas) > 0): foreach($entregas as $ent): 
            $data = date('d/m H:i', strtotime($ent['data_entrega']));
            $linkDownload = 'uploads/' . basename($ent['arquivo_path']);
            
            // VERIFICA SE ESTÁ ATRASADO
            $atrasado = false;
            if ($atividade['data_limite']) {
                $prazoFim = $atividade['data_limite'] . ' 23:59:59';
                if ($ent['data_entrega'] > $prazoFim) { $atrasado = true; }
            }
        ?>
        <div class="delivery-item">
            <div class="aluno-info">
                <strong><?php echo htmlspecialchars($ent['aluno_nome']); ?> <?php if($atrasado): ?><span class="tag-atraso">ATRASADO</span><?php endif; ?></strong>
                <span><i class="fa-regular fa-clock"></i> <?php echo $data; ?> <?php if($ent['nota'] !== null): ?><span class="nota-badge"><i class="fa-solid fa-check"></i> Nota: <?php echo $ent['nota']; ?></span><?php endif; ?></span>
            </div>
            <form action="actions/salvar_nota_entrega.php" method="POST" class="grade-area">
                <input type="hidden" name="entrega_id" value="<?php echo $ent['id']; ?>"><input type="hidden" name="material_id" value="<?php echo $material_id; ?>">
                <label style="font-size: 0.8rem; color: #94a3b8;">Nota:</label>
                <input type="number" name="nota" step="0.1" min="0" max="10" class="input-nota" value="<?php echo $ent['nota']; ?>" required>
                <button type="submit" style="background:none; border:none; color:#6366f1; cursor:pointer;"><i class="fa-solid fa-floppy-disk"></i></button>
            </form>
            <a href="<?php echo $linkDownload; ?>" download class="btn-download"><i class="fa-solid fa-download"></i> Baixar</a>
        </div>
    <?php endforeach; else: ?><div style="text-align:center; padding:40px; color:#64748b; border:2px dashed #334155; border-radius:12px;">Nenhuma entrega recebida ainda.</div><?php endif; ?>
</div>
</body>
</html>