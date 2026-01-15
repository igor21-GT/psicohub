<?php
// perfil.php
session_start();
require_once 'config/db.php';
if (!isset($_SESSION['usuario_id'])) { header("Location: index.php"); exit(); }

$page_title = "Meu Perfil";
include 'includes/header.php';

// Busca dados atuais do usuário
$id = $_SESSION['usuario_id'];
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = :id");
$stmt->execute(['id' => $id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<div class="card" style="max-width: 600px; margin: 0 auto;">
    <h2 style="margin-bottom: 20px;"><i class="fa-solid fa-user-gear"></i> Editar Meus Dados</h2>
    
    <?php if(isset($_GET['msg']) && $_GET['msg'] == 'erro_senha'): ?>
        <p style="color: #ef4444; background: #fee2e2; padding: 10px; border-radius: 6px; margin-bottom: 15px;">
            As senhas não coincidem ou estão vazias.
        </p>
    <?php endif; ?>

    <?php if(isset($_GET['msg']) && $_GET['msg'] == 'sucesso'): ?>
        <p style="color: #166534; background: #dcfce7; padding: 10px; border-radius: 6px; margin-bottom: 15px;">
            Perfil atualizado com sucesso!
        </p>
    <?php endif; ?>

    <form action="salvar_perfil.php" method="POST">
        <div class="form-group">
            <label>Nome Completo</label>
            <input type="text" name="nome" value="<?php echo htmlspecialchars($usuario['nome']); ?>" required>
        </div>

        <div class="form-group">
            <label>E-mail (Login)</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
        </div>

        <hr style="margin: 20px 0; border:0; border-top: 1px solid var(--border-color);">
        <p style="margin-bottom: 15px; font-size: 0.9rem; color: var(--text-muted);">
            <i class="fa-solid fa-lock"></i> Alterar Senha (deixe em branco para manter a atual)
        </p>

        <div class="form-group">
            <label>Nova Senha</label>
            <input type="password" name="nova_senha" placeholder="Digite a nova senha">
        </div>

        <div class="form-group">
            <label>Confirmar Nova Senha</label>
            <input type="password" name="confirma_senha" placeholder="Repita a nova senha">
        </div>

        <button type="submit" class="btn-primary" style="width:100%">Salvar Alterações</button>
    </form>
</div>

<?php include 'includes/footer.php'; ?>