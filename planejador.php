<?php
// planejador.php
session_start();
if (!isset($_SESSION['usuario_id'])) { header("Location: index.php"); exit(); }
require_once 'config/db.php';
$page_title = "Planejador";
include 'includes/header.php';

try {
    $stmt = $pdo->query("SELECT * FROM agendamentos WHERE data_evento >= NOW() ORDER BY data_evento ASC");
    $eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) { $eventos = []; }
?>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>Próximas Sessões</h2>
        <button onclick="abrirModal()" class="btn-primary"><i class="fa-solid fa-plus"></i> Novo Agendamento</button>
    </div>

    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="text-align: left; border-bottom: 2px solid var(--border-color);">
                <th style="padding: 10px;">Data / Hora</th>
                <th style="padding: 10px;">Evento</th>
                <th style="padding: 10px;">Tipo</th>
                <th style="padding: 10px;">Ação</th>
            </tr>
        </thead>
        <tbody>
            <?php if(count($eventos) > 0): ?>
                <?php foreach($eventos as $evento): 
                    $data = date('d/m/Y H:i', strtotime($evento['data_evento']));
                    $classeBadge = ($evento['tipo'] == 'Administrativo') ? 'inativo' : 'ativo';
                ?>
                <tr style="border-bottom: 1px solid var(--border-color);">
                    <td style="padding: 15px 10px;"><?php echo $data; ?></td>
                    <td style="padding: 15px 10px; font-weight: 500;"><?php echo htmlspecialchars($evento['titulo']); ?></td>
                    <td style="padding: 15px 10px;"><span class="badge <?php echo $classeBadge; ?>"><?php echo $evento['tipo']; ?></span></td>
                    <td style="padding: 15px 10px;">
                        <a href="excluir_generico.php?tipo=evento&id=<?php echo $evento['id']; ?>" 
                           onclick="return confirm('Cancelar este agendamento?');"
                           style="color: var(--sidebar-active); cursor:pointer;">
                            <i class="fa-solid fa-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="4" style="padding:20px; text-align:center;">Nenhum evento futuro.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div id="modalAgenda" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="fecharModal()">&times;</span>
        <h2>Agendar Sessão</h2><br>
        <form action="salvar_evento.php" method="POST">
            <div class="form-group"><label>Título</label><input type="text" name="titulo" required></div>
            <div class="form-group"><label>Data</label><input type="datetime-local" name="data_evento" required></div>
            <div class="form-group"><label>Tipo</label>
                <select name="tipo">
                    <option value="Sessão Individual">Sessão Individual</option>
                    <option value="Sessão em Grupo">Sessão em Grupo</option>
                    <option value="Avaliação">Avaliação</option>
                    <option value="Administrativo">Administrativo</option>
                </select>
            </div>
            <button type="submit" class="btn-primary" style="width:100%">Agendar</button>
        </form>
    </div>
</div>
<script>
    const modalAgenda = document.getElementById("modalAgenda");
    function abrirModal() { modalAgenda.style.display = "block"; }
    function fecharModal() { modalAgenda.style.display = "none"; }
    window.onclick = function(e) { if (e.target == modalAgenda) modalAgenda.style.display = "none"; }
</script>

<?php include 'includes/footer.php'; ?>