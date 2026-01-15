<?php
// ver_respostas.php
session_start();
require_once 'config/db.php';

// Segurança
if (!isset($_SESSION['usuario_id'])) { header("Location: index.php"); exit(); }
if (!isset($_GET['id'])) { header("Location: minhas_turmas.php"); exit(); }

$quiz_id = $_GET['id'];

// Consultas
$stmt = $pdo->prepare("SELECT * FROM quizzes WHERE id = :id");
$stmt->execute(['id' => $quiz_id]);
$quiz = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$quiz) { die("Quiz não encontrado."); }

$stmtResp = $pdo->prepare("SELECT * FROM respostas_alunos WHERE quiz_id = :id ORDER BY nota_final DESC, data_envio DESC");
$stmtResp->execute(['id' => $quiz_id]);
$respostas = $stmtResp->fetchAll(PDO::FETCH_ASSOC);

$total_entregas = count($respostas);
$media_turma = 0;
if ($total_entregas > 0) {
    $soma = array_sum(array_column($respostas, 'nota_final'));
    $media_turma = $soma / $total_entregas;
}

include 'includes/header.php';
?>

<style>
    /* Estilos integrados ao style.css */
    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 1px solid var(--border-color); }
    h1 { font-weight: 700; color: var(--text-color); margin: 0; font-size: 1.8rem; }
    p.subtitle { color: var(--text-muted); margin: 5px 0 0 0; }

    /* Botões */
    .btn-print { 
        background-color: #e0e7ff; color: #4338ca; 
        padding: 10px 20px; border-radius: 8px; text-decoration: none; 
        font-weight: 600; display: inline-flex; align-items: center; gap: 8px; 
        transition: 0.2s;
    }
    .btn-print:hover { transform: translateY(-2px); }

    .btn-back {
        background: var(--card-bg); color: var(--text-muted); 
        padding: 10px 20px; border-radius: 8px;
        text-decoration: none; font-weight: 500; border: 1px solid var(--border-color);
        display: inline-flex; align-items: center; gap: 8px; transition: 0.2s;
    }
    .btn-back:hover { background: var(--border-color); color: var(--text-color); }

    /* Stats Grid */
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 30px; }
    
    .stat-card {
        background: var(--card-bg); border: 1px solid var(--border-color);
        border-radius: 12px; padding: 25px; display: flex; align-items: center; justify-content: space-between;
        box-shadow: var(--card-shadow);
    }
    
    .stat-info h3 { font-size: 2.2rem; font-weight: 700; margin: 0; color: var(--text-color); }
    .stat-info p { margin: 0; color: var(--text-muted); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; }

    .stat-icon { width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; }

    /* Tabela */
    .table-container { background: var(--card-bg); border-radius: 12px; border: 1px solid var(--border-color); overflow: hidden; }
    .custom-table { width: 100%; border-collapse: collapse; }
    
    .custom-table th { 
        background-color: var(--input-bg); 
        padding: 18px 25px; text-align: left; 
        color: var(--text-muted); font-weight: 600; 
        border-bottom: 2px solid var(--border-color); 
    }
    
    .custom-table td { 
        padding: 20px 25px; 
        border-bottom: 1px solid var(--border-color); 
        color: var(--text-color); 
        vertical-align: middle; 
    }

    /* Badges */
    .badge-grade { padding: 6px 12px; border-radius: 20px; font-weight: 700; font-size: 0.9rem; display: inline-block; }
    .grade-good { background: rgba(16, 185, 129, 0.15); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2); }
    .grade-bad { background: rgba(239, 68, 68, 0.15); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); }
</style>

<div class="page-header">
    <div>
        <h1><?php echo htmlspecialchars($quiz['titulo']); ?></h1>
        <p class="subtitle">Relatório de Desempenho</p>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="relatorio_notas.php?id=<?php echo $quiz_id; ?>" target="_blank" class="btn-print">
            <i class="fa-solid fa-print"></i> Imprimir Pauta
        </a>
        <a href="ver_turma.php?id=<?php echo $quiz['turma_id']; ?>" class="btn-back">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-info">
            <h3><?php echo $total_entregas; ?></h3>
            <p>Provas Entregues</p>
        </div>
        <div class="stat-icon" style="background: rgba(139, 92, 246, 0.15); color: #8b5cf6;">
            <i class="fa-solid fa-users"></i>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <h3><?php echo number_format($media_turma, 1); ?></h3>
            <p>Média da Turma</p>
        </div>
        <div class="stat-icon" style="background: rgba(251, 191, 36, 0.15); color: #fbbf24;">
            <i class="fa-solid fa-star"></i>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <h3 style="font-size: 1.5rem;">Concluído</h3>
            <p>Status</p>
        </div>
        <div class="stat-icon" style="background: rgba(16, 185, 129, 0.15); color: #10b981;">
            <i class="fa-solid fa-check-circle"></i>
        </div>
    </div>
</div>

<div class="table-container">
    <table class="custom-table">
        <thead>
            <tr>
                <th>Aluno</th>
                <th>Matrícula</th>
                <th>Data</th>
                <th class="text-center">Nota</th>
                <th class="text-right">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if(count($respostas) > 0): ?>
                <?php foreach($respostas as $resp): ?>
                <tr>
                    <td>
                        <strong><?php echo htmlspecialchars($resp['nome_aluno']); ?></strong><br>
                        <small style="color:var(--text-muted);"><?php echo htmlspecialchars($resp['email_aluno']); ?></small>
                    </td>
                    <td><span style="background:var(--border-color); color:var(--text-muted); padding:4px 8px; border-radius:4px; font-family:monospace;"><?php echo htmlspecialchars($resp['matricula_aluno']); ?></span></td>
                    <td><?php echo date('d/m H:i', strtotime($resp['data_envio'])); ?></td>
                    <td class="text-center">
                        <span class="badge-grade <?php echo ($resp['nota_final'] >= 6) ? 'grade-good' : 'grade-bad'; ?>">
                            <?php echo number_format($resp['nota_final'], 1); ?>
                        </span>
                    </td>
                    <td class="text-right">
                        <a href="actions/excluir_generico.php?tipo=resposta&id=<?php echo $resp['id']; ?>" onclick="return confirm('Apagar?')" style="color:#ef4444;"><i class="fa-solid fa-trash"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="5" style="text-align:center; padding:30px; color: var(--text-muted);">Sem respostas.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'includes/footer.php'; ?>