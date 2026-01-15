<?php
// planejador.php
session_start();
if (!isset($_SESSION['usuario_id'])) { header("Location: index.php"); exit(); }

require_once 'config/db.php';
$page_title = "Planejador";
include 'includes/header.php';

// Busca apenas eventos do usuário logado e futuros
try {
    $stmt = $pdo->prepare("SELECT * FROM agendamentos WHERE usuario_id = :uid AND data_evento >= CURDATE() ORDER BY data_evento ASC");
    $stmt->execute(['uid' => $_SESSION['usuario_id']]);
    $eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) { $eventos = []; }
?>

<style>
    /* Usamos as variáveis do style.css para garantir compatibilidade */
    
    /* Layout do Card */
    .custom-card {
        background-color: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 25px;
        box-shadow: var(--card-shadow);
    }
    
    .custom-card h2 { color: var(--text-color); margin: 0; }

    /* Tabela Estilizada */
    .table-custom { width: 100%; border-collapse: collapse; margin-top: 15px; }
    
    .table-custom th {
        text-align: left;
        padding: 15px;
        background-color: var(--input-bg); /* Fundo sutil do header da tabela */
        color: var(--text-muted);
        font-weight: 600;
        border-bottom: 2px solid var(--border-color);
        text-transform: uppercase;
        font-size: 0.85rem;
    }

    .table-custom td {
        padding: 18px 15px;
        border-bottom: 1px solid var(--border-color);
        color: var(--text-color);
        vertical-align: middle;
    }
    
    .table-custom strong { color: var(--text-color); }
    .table-custom small { color: var(--text-muted); }

    /* Badges de Tipo */
    .badge-event {
        padding: 5px 10px;
        border-radius: 6px;
        font-size: 0.8rem;
        font-weight: 600;
        display: inline-block;
    }
    /* Cores específicas para os badges (mantendo fixas pois são indicadores visuais) */
    .tipo-Aula { background: rgba(99, 102, 241, 0.15); color: #818cf8; border: 1px solid rgba(99, 102, 241, 0.2); }
    .tipo-Prova { background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.2); }
    .tipo-Reuniao { background: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.2); }
    .tipo-Admin { background: rgba(100, 116, 139, 0.15); color: #94a3b8; border: 1px solid rgba(100, 116, 139, 0.2); }

    /* Modal */
    .modal {
        display: none; position: fixed; z-index: 1000; left: 0; top: 0;
        width: 100%; height: 100%; overflow: auto;
        background-color: rgba(0,0,0,0.5); backdrop-filter: blur(4px);
    }
    .modal-content {
        background-color: var(--card-bg);
        margin: 10% auto; padding: 30px; 
        border: 1px solid var(--border-color);
        width: 90%; max-width: 500px; border-radius: 16px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        position: relative;
    }
    .modal-content h2 { color: var(--text-color); margin-top: 0; }
    .modal-content p { color: var(--text-muted); }

    .close-btn {
        color: var(--text-muted); float: right; font-size: 28px; font-weight: bold;
        cursor: pointer; transition: 0.2s;
    }
    .close-btn:hover { color: var(--text-color); }

    /* Formulário */
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; margin-bottom: 8px; color: var(--text-muted); font-size: 0.9rem; font-weight: 500; }
    
    .form-control {
        width: 100%; padding: 12px; border-radius: 8px;
        border: 1px solid var(--border-color);
        background-color: var(--input-bg); 
        color: var(--text-color);
        font-size: 1rem;
        outline: none;
    }
    .form-control:focus { border-color: var(--sidebar-active); }

    .btn-primary {
        background-color: var(--sidebar-active); color: white; padding: 12px 20px;
        border: none; border-radius: 8px; cursor: pointer; font-weight: 600;
        display: inline-flex; align-items: center; gap: 8px; transition: 0.2s;
        text-decoration: none; font-size: 0.9rem;
    }
    .btn-primary:hover { opacity: 0.9; transform: translateY(-2px); }

    .btn-trash { 
        color: #ef4444; cursor: pointer; transition: 0.2s; 
        background: transparent; border: none; font-size: 1rem;
    }
    .btn-trash:hover { transform: scale(1.2); color: #dc2626; }
</style>

<div class="custom-card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>Próximas Sessões</h2>
        <button onclick="abrirModal()" class="btn-primary">
            <i class="fa-solid fa-plus"></i> Novo Agendamento
        </button>
    </div>

    <div style="overflow-x: auto;">
        <table class="table-custom">
            <thead>
                <tr>
                    <th>Data / Hora</th>
                    <th>Evento</th>
                    <th>Tipo</th>
                    <th style="text-align: right;">Ação</th>
                </tr>
            </thead>
            <tbody>
                <?php if(count($eventos) > 0): ?>
                    <?php foreach($eventos as $evento): 
                        $data = date('d/m/Y', strtotime($evento['data_evento']));
                        $hora = date('H:i', strtotime($evento['data_evento']));
                        
                        // Define classe do badge baseado no texto
                        $tipoClass = 'tipo-Admin';
                        if(stripos($evento['tipo'], 'Aula') !== false) $tipoClass = 'tipo-Aula';
                        if(stripos($evento['tipo'], 'Prova') !== false || stripos($evento['tipo'], 'Avaliação') !== false) $tipoClass = 'tipo-Prova';
                        if(stripos($evento['tipo'], 'Reunião') !== false) $tipoClass = 'tipo-Reuniao';
                    ?>
                    <tr>
                        <td>
                            <strong><?php echo $data; ?></strong>
                            <small><?php echo $hora; ?></small>
                        </td>
                        <td style="font-weight: 500; font-size: 1.05rem;"><?php echo htmlspecialchars($evento['titulo']); ?></td>
                        <td><span class="badge-event <?php echo $tipoClass; ?>"><?php echo $evento['tipo']; ?></span></td>
                        <td style="text-align: right;">
                            <a href="actions/excluir_generico.php?tipo=evento&id=<?php echo $evento['id']; ?>" 
                               onclick="return confirm('Cancelar este agendamento?');"
                               class="btn-trash" title="Excluir">
                                <i class="fa-solid fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4" style="padding:40px; text-align:center; color: var(--text-muted);">
                        <i class="fa-regular fa-calendar-xmark" style="font-size: 2rem; margin-bottom: 10px;"></i><br>
                        Nenhum evento futuro encontrado.
                    </td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="modalAgenda" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="fecharModal()">&times;</span>
        <h2>Agendar Sessão</h2>
        <p style="margin-bottom: 20px;">Preencha os dados do novo compromisso.</p>
        
        <form action="salvar_evento.php" method="POST">
            <div class="form-group">
                <label>Título do Evento</label>
                <input type="text" name="titulo" class="form-control" placeholder="Ex: Prova de Psicologia" required>
            </div>
            
            <div class="form-group">
                <label>Data e Hora</label>
                <input type="datetime-local" name="data_evento" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label>Tipo de Atividade</label>
                <select name="tipo" class="form-control">
                    <option value="Sessão Individual">Sessão Individual</option>
                    <option value="Sessão em Grupo">Sessão em Grupo</option>
                    <option value="Avaliação">Avaliação / Prova</option>
                    <option value="Reunião">Reunião</option>
                    <option value="Administrativo">Administrativo</option>
                </select>
            </div>
            
            <button type="submit" class="btn-primary" style="width:100%; justify-content: center; margin-top: 10px;">
                Confirmar Agendamento
            </button>
        </form>
    </div>
</div>

<script>
    const modalAgenda = document.getElementById("modalAgenda");
    function abrirModal() { modalAgenda.style.display = "block"; }
    function fecharModal() { modalAgenda.style.display = "none"; }
    
    // Fecha ao clicar fora
    window.onclick = function(e) { 
        if (e.target == modalAgenda) fecharModal(); 
    }
</script>

<?php include 'includes/footer.php'; ?>