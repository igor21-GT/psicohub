<?php
// editar_turma.php
session_start();
require_once 'config/db.php';
if (!isset($_SESSION['usuario_id'])) { header("Location: index.php"); exit(); }

// POST: Salvar Edição
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $nome = $_POST['nome'];
    $disciplina = $_POST['disciplina']; // Novo
    $turno = $_POST['turno'];           // Novo
    $descricao = $_POST['descricao'];
    $horario = $_POST['horario'];
    $status = $_POST['status'];

    $stmt = $pdo->prepare("UPDATE turmas SET nome=?, disciplina=?, turno=?, descricao=?, horario=?, status=? WHERE id=?");
    $stmt->execute([$nome, $disciplina, $turno, $descricao, $horario, $status, $id]);

    header("Location: minhas_turmas.php?msg=atualizado");
    exit();
}

// GET: Carregar dados
if (!isset($_GET['id'])) { header("Location: minhas_turmas.php"); exit(); }
$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM turmas WHERE id = :id");
$stmt->execute(['id' => $id]);
$turma = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$turma) { echo "Turma não encontrada."; exit(); }

$page_title = "Editar Turma";
include 'includes/header.php';
?>

<div class="card" style="max-width: 600px; margin: 0 auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>Editar: <?php echo htmlspecialchars($turma['nome']); ?></h2>
        <a href="minhas_turmas.php" style="color: var(--text-muted); text-decoration:none;">Cancelar</a>
    </div>

    <form action="editar_turma.php" method="POST">
        <input type="hidden" name="id" value="<?php echo $turma['id']; ?>">

        <div class="form-group">
            <label>Nome da Turma</label>
            <input type="text" name="nome" value="<?php echo htmlspecialchars($turma['nome']); ?>" required>
        </div>

        <div class="form-group" style="display:grid; grid-template-columns: 1fr 1fr; gap: 15px;">
            <div>
                <label>Disciplina</label>
                <input type="text" name="disciplina" value="<?php echo htmlspecialchars($turma['disciplina'] ?? ''); ?>" required>
            </div>
            <div>
                <label>Turno</label>
                <select name="turno">
                    <option value="Manhã" <?php echo ($turma['turno'] == 'Manhã') ? 'selected' : ''; ?>>Manhã</option>
                    <option value="Noite" <?php echo ($turma['turno'] == 'Noite') ? 'selected' : ''; ?>>Noite</option>
                </select>
            </div>
        </div>
        
        <div class="form-group">
            <label>Descrição</label>
            <textarea name="descricao" rows="3"><?php echo htmlspecialchars($turma['descricao']); ?></textarea>
        </div>

        <div class="form-group">
            <label>Horário</label>
            <input type="text" name="horario" value="<?php echo htmlspecialchars($turma['horario']); ?>">
        </div>

        <div class="form-group">
            <label>Status</label>
            <select name="status">
                <option value="Ativo" <?php echo ($turma['status'] == 'Ativo') ? 'selected' : ''; ?>>Ativo</option>
                <option value="Inativo" <?php echo ($turma['status'] == 'Inativo') ? 'selected' : ''; ?>>Inativo</option>
            </select>
        </div>

        <button type="submit" class="btn-primary" style="width:100%">Salvar Alterações</button>
    </form>
</div>

<?php include 'includes/footer.php'; ?>