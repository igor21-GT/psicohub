<?php
// minhas_turmas.php
session_start();
if (!isset($_SESSION['usuario_id'])) { header("Location: index.php"); exit(); }
require_once 'config/db.php';
$page_title = "Minhas Turmas";
include 'includes/header.php';

// Busca todas as turmas
try {
    $busca = filter_input(INPUT_GET, 'busca', FILTER_SANITIZE_SPECIAL_CHARS);
    if ($busca) {
        $termo = "%" . $busca . "%";
        $stmt = $pdo->prepare("SELECT * FROM turmas WHERE nome LIKE :termo OR disciplina LIKE :termo ORDER BY id DESC");
        $stmt->execute(['termo' => $termo]);
    } else {
        $stmt = $pdo->query("SELECT * FROM turmas ORDER BY id DESC");
    }
    $todas_turmas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) { $todas_turmas = []; }

// Separação por Turnos (Arrays)
$turmasManha = [];
$turmasNoite = [];

foreach ($todas_turmas as $t) {
    if ($t['turno'] == 'Manhã') {
        $turmasManha[] = $t;
    } else {
        $turmasNoite[] = $t;
    }
}
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
    <h2>Gerenciar Turmas</h2>
    
    <div style="display: flex; gap: 10px; align-items: center;">
        <form action="minhas_turmas.php" method="GET" style="display: flex; gap: 5px;">
            <input type="text" name="busca" placeholder="Pesquisar turma ou disciplina..." value="<?php echo $busca ?? ''; ?>" 
                   style="padding: 10px; border-radius: 8px; border: 1px solid var(--border-color); width: 250px;">
            <button type="submit" class="btn-primary" style="padding: 10px 15px;"><i class="fa-solid fa-magnifying-glass"></i></button>
        </form>
        <button onclick="abrirModal()" class="btn-primary"><i class="fa-solid fa-plus"></i> Nova Turma</button>
    </div>
</div>

<h3 style="margin: 30px 0 15px 0; border-bottom: 2px solid var(--sidebar-active); display:inline-block; padding-bottom:5px;">
    <i class="fa-solid fa-sun" style="color: #f59e0b;"></i> Turno da Manhã
</h3>

<div class="grid-container">
    <?php if(count($turmasManha) > 0): ?>
        <?php foreach($turmasManha as $turma): ?>
            <div class="class-card">
                <div class="class-header">
                    <span class="badge <?php echo strtolower($turma['status']); ?>"><?php echo $turma['status']; ?></span>
                    <div>
                        <a href="editar_turma.php?id=<?php echo $turma['id']; ?>" title="Editar" style="color: var(--text-muted); margin-right: 10px;">
                            <i class="fa-solid fa-pen"></i>
                        </a>
                        <a href="excluir_generico.php?tipo=turma&id=<?php echo $turma['id']; ?>" 
                           onclick="return confirm('Excluir esta turma?');" style="color: #ef4444;">
                            <i class="fa-solid fa-trash"></i>
                        </a>
                    </div>
                </div>
                
                <h3 style="margin-bottom: 5px;"><?php echo htmlspecialchars($turma['nome']); ?></h3>
                
                <p style="color: var(--sidebar-active); font-weight:600; margin-bottom: 10px;">
                    <i class="fa-solid fa-book"></i> <?php echo htmlspecialchars($turma['disciplina'] ?? 'Geral'); ?>
                </p>

                <p style="color: var(--text-muted); margin-bottom: 15px; font-size: 0.9rem;">
                    <?php echo htmlspecialchars($turma['descricao']); ?>
                </p>
                <div style="margin-bottom: 15px; font-size: 0.9rem;">
                    <i class="fa-regular fa-clock"></i> <?php echo $turma['horario']; ?>
                </div>
                
                <a href="ver_turma.php?id=<?php echo $turma['id']; ?>" class="btn-primary" style="width: 100%; text-align:center;">Acessar Aula</a>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p style="color: var(--text-muted);">Nenhuma turma cadastrada no turno da manhã.</p>
    <?php endif; ?>
</div>

<h3 style="margin: 40px 0 15px 0; border-bottom: 2px solid var(--sidebar-active); display:inline-block; padding-bottom:5px;">
    <i class="fa-solid fa-moon" style="color: #6366f1;"></i> Turno da Noite
</h3>

<div class="grid-container">
    <?php if(count($turmasNoite) > 0): ?>
        <?php foreach($turmasNoite as $turma): ?>
            <div class="class-card">
                <div class="class-header">
                    <span class="badge <?php echo strtolower($turma['status']); ?>"><?php echo $turma['status']; ?></span>
                    <div>
                        <a href="editar_turma.php?id=<?php echo $turma['id']; ?>" title="Editar" style="color: var(--text-muted); margin-right: 10px;">
                            <i class="fa-solid fa-pen"></i>
                        </a>
                        <a href="excluir_generico.php?tipo=turma&id=<?php echo $turma['id']; ?>" 
                           onclick="return confirm('Excluir esta turma?');" style="color: #ef4444;">
                            <i class="fa-solid fa-trash"></i>
                        </a>
                    </div>
                </div>
                
                <h3 style="margin-bottom: 5px;"><?php echo htmlspecialchars($turma['nome']); ?></h3>
                
                <p style="color: var(--sidebar-active); font-weight:600; margin-bottom: 10px;">
                    <i class="fa-solid fa-book"></i> <?php echo htmlspecialchars($turma['disciplina'] ?? 'Geral'); ?>
                </p>

                <p style="color: var(--text-muted); margin-bottom: 15px; font-size: 0.9rem;">
                    <?php echo htmlspecialchars($turma['descricao']); ?>
                </p>
                <div style="margin-bottom: 15px; font-size: 0.9rem;">
                    <i class="fa-regular fa-clock"></i> <?php echo $turma['horario']; ?>
                </div>
                
                <a href="ver_turma.php?id=<?php echo $turma['id']; ?>" class="btn-primary" style="width: 100%; text-align:center;">Acessar Aula</a>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p style="color: var(--text-muted);">Nenhuma turma cadastrada no turno da noite.</p>
    <?php endif; ?>
</div>

<div id="modalTurma" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="fecharModal()">&times;</span>
        <h2>Nova Turma</h2><br>
        <form action="salvar_turma.php" method="POST">
            
            <div class="form-group">
                <label>Nome da Turma</label>
                <input type="text" name="nome" placeholder="Ex: Turma A - Psicologia" required>
            </div>

            <div class="form-group" style="display:grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div>
                    <label>Disciplina</label>
                    <input type="text" name="disciplina" placeholder="Ex: Anatomia" required>
                </div>
                <div>
                    <label>Turno</label>
                    <select name="turno">
                        <option value="Manhã">Manhã</option>
                        <option value="Noite">Noite</option>
                    </select>
                </div>
            </div>

            <div class="form-group"><label>Descrição</label><textarea name="descricao" rows="3"></textarea></div>
            <div class="form-group"><label>Horário</label><input type="text" name="horario" placeholder="Ex: Segunda e Quarta"></div>
            
            <div class="form-group"><label>Status</label>
                <select name="status"><option value="Ativo">Ativo</option><option value="Inativo">Inativo</option></select>
            </div>
            
            <button type="submit" class="btn-primary" style="width:100%">Salvar</button>
        </form>
    </div>
</div>

<script>
    const modal = document.getElementById("modalTurma");
    function abrirModal() { modal.style.display = "block"; }
    function fecharModal() { modal.style.display = "none"; }
    window.onclick = function(e) { if (e.target == modal) modal.style.display = "none"; }
</script>

<?php include 'includes/footer.php'; ?>