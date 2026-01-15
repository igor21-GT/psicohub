<?php
// relatorio_notas.php
session_start();
require_once 'config/db.php';

// Verifica login
if (!isset($_SESSION['usuario_id'])) { die("Acesso negado."); }
if (!isset($_GET['id'])) { die("ID do Quiz não fornecido."); }

$quiz_id = $_GET['id'];

// 1. Busca Dados do Quiz e da Turma
$stmt = $pdo->prepare("
    SELECT q.*, t.nome as nome_turma 
    FROM quizzes q 
    JOIN turmas t ON q.turma_id = t.id 
    WHERE q.id = :id
");
$stmt->execute(['id' => $quiz_id]);
$dados = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$dados) { die("Quiz não encontrado."); }

// 2. Busca as Respostas (Ordenado por Nome para facilitar a chamada)
$stmtResp = $pdo->prepare("SELECT * FROM respostas_alunos WHERE quiz_id = :id ORDER BY nome_aluno ASC");
$stmtResp->execute(['id' => $quiz_id]);
$alunos = $stmtResp->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Relatório - <?php echo htmlspecialchars($dados['titulo']); ?></title>
    <style>
        /* Estilos Gerais (Tela e Papel) */
        body { font-family: 'Times New Roman', serif; color: #000; padding: 20px; max-width: 800px; margin: 0 auto; }
        
        .header { text-align: center; margin-bottom: 40px; border-bottom: 2px solid #000; padding-bottom: 20px; }
        .logo { font-size: 24px; font-weight: bold; text-transform: uppercase; letter-spacing: 2px; }
        .sub-header { margin-top: 10px; font-size: 14px; }
        
        .info-box { display: flex; justify-content: space-between; margin-bottom: 30px; font-size: 16px; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 50px; }
        th, td { border: 1px solid #000; padding: 8px 12px; text-align: left; }
        th { background-color: #f0f0f0; font-weight: bold; }
        
        .aprovado { font-weight: bold; }
        .reprovado { color: #555; font-style: italic; }

        .footer-signature {
            margin-top: 80px;
            display: flex;
            justify-content: space-between;
        }
        .line { border-top: 1px solid #000; width: 40%; text-align: center; padding-top: 10px; font-size: 14px; }

        /* Botão de Imprimir (Só aparece na tela) */
        .no-print {
            position: fixed; top: 20px; right: 20px;
            background: #6f42c1; color: white; padding: 10px 20px;
            text-decoration: none; border-radius: 5px; font-family: sans-serif;
            box-shadow: 0 4px 6px rgba(0,0,0,0.2); cursor: pointer;
        }
        .no-print:hover { background: #5a32a3; }

        /* Configurações de Impressão */
        @media print {
            .no-print { display: none; }
            body { padding: 0; margin: 0; }
        }
    </style>
</head>
<body>

    <button onclick="window.print()" class="no-print">🖨️ Imprimir / Salvar PDF</button>

    <div class="header">
        <div class="logo">PsicoHub Educacional</div>
        <div class="sub-header">Relatório de Rendimento Acadêmico</div>
    </div>

    <div class="info-box">
        <div>
            <strong>Turma:</strong> <?php echo htmlspecialchars($dados['nome_turma']); ?><br>
            <strong>Avaliação:</strong> <?php echo htmlspecialchars($dados['titulo']); ?>
        </div>
        <div style="text-align: right;">
            <strong>Data de Emissão:</strong> <?php echo date('d/m/Y'); ?><br>
            <strong>Total de Alunos:</strong> <?php echo count($alunos); ?>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 10%;">#</th>
                <th style="width: 50%;">Nome do Aluno</th>
                <th style="width: 20%;">Matrícula</th>
                <th style="width: 20%; text-align: center;">Nota Final</th>
            </tr>
        </thead>
        <tbody>
            <?php if(count($alunos) > 0): ?>
                <?php foreach($alunos as $i => $aluno): ?>
                <tr>
                    <td><?php echo $i + 1; ?></td>
                    <td>
                        <?php echo htmlspecialchars($aluno['nome_aluno']); ?>
                        <br><small style="font-size: 10px;"><?php echo htmlspecialchars($aluno['email_aluno']); ?></small>
                    </td>
                    <td><?php echo htmlspecialchars($aluno['matricula_aluno']); ?></td>
                    <td style="text-align: center;">
                        <?php if($aluno['nota_final'] >= 6.0): ?>
                            <span class="aprovado"><?php echo number_format($aluno['nota_final'], 1); ?></span>
                        <?php else: ?>
                            <span class="reprovado"><?php echo number_format($aluno['nota_final'], 1); ?></span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="4" style="text-align: center;">Nenhum registro encontrado.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="footer-signature">
        <div class="line">
            Assinatura do Professor
        </div>
        <div class="line">
            Coordenação Pedagógica
        </div>
    </div>

    <script>
        // Opcional: abre a caixa de impressão automaticamente ao carregar
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>