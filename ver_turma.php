<?php
// ver_turma.php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['usuario_id'])) { header("Location: index.php"); exit(); }
if (!isset($_GET['id'])) { header("Location: minhas_turmas.php"); exit(); }

$id = $_GET['id'];

// 1. Busca dados da Turma/Paciente
$stmt = $pdo->prepare("SELECT * FROM turmas WHERE id = :id");
$stmt->execute(['id' => $id]);
$turma = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$turma) { echo "Turma não encontrada."; exit(); }

// 2. Busca Anotações (Histórico)
$stmt_notas = $pdo->prepare("SELECT * FROM anotacoes WHERE turma_id = :id ORDER BY data_registro DESC");
$stmt_notas->execute(['id' => $id]);
$anotacoes = $stmt_notas->fetchAll(PDO::FETCH_ASSOC);

// 3. Busca Materiais (Aulas/PDFs)
// (Nota: Só vai funcionar se você tiver rodado o SQL da tabela 'materiais' antes)
try {
    $stmt_mat = $pdo->prepare("SELECT * FROM materiais WHERE turma_id = :id ORDER BY data_postagem DESC");
    $stmt_mat->execute(['id' => $id]);
    $materiais = $stmt_mat->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $materiais = [];
}

$page_title = $turma['nome'];
include 'includes/header.php';
?>

<div style="margin-bottom: 20px;">
    <a href="minhas_turmas.php" style="text-decoration: none; color: var(--text-muted);">
        <i class="fa-solid fa-arrow-left"></i> Voltar
    </a>
</div>

<div class="card" style="margin-bottom: 20px;">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <div>
            <h2><?php echo htmlspecialchars($turma['nome']); ?></h2>
            <p style="color: var(--text-muted);"><?php echo htmlspecialchars($turma['descricao']); ?></p>
        </div>
        <span class="badge <?php echo strtolower($turma['status']); ?>"><?php echo $turma['status']; ?></span>
    </div>
</div>

<div class="tab-container">
    <button class="tab-btn active" onclick="openTab(event, 'tabProntuario')">
        <i class="fa-solid fa-notes-medical"></i> Prontuário
    </button>
    <button class="tab-btn" onclick="openTab(event, 'tabSalaAula')">
        <i class="fa-solid fa-graduation-cap"></i> Sala de Aula
    </button>
</div>

<div id="tabProntuario" class="tab-content active">
    <div class="card">
        <h3>Histórico de Evolução</h3>
        <br>
        <form action="salvar_anotacao.php" method="POST" style="margin-bottom: 30px;">
            <input type="hidden" name="turma_id" value="<?php echo $id; ?>">
            <div class="form-group">
                <textarea name="conteudo" rows="3" placeholder="Escreva uma nova anotação..." required style="resize: vertical;"></textarea>
            </div>
            <div style="text-align: right;">
                <button type="submit" class="btn-primary">Adicionar Nota</button>
            </div>
        </form>

        <div class="timeline">
            <?php if(count($anotacoes) > 0): ?>
                <?php foreach($anotacoes as $nota): 
                    $data = date('d/m/Y - H:i', strtotime($nota['data_registro']));
                ?>
                <div style="border-left: 3px solid var(--sidebar-active); padding-left: 20px; margin-bottom: 20px; position: relative;">
                    <div style="position: absolute; left: -8px; top: 0; width: 13px; height: 13px; background: var(--sidebar-active); border-radius: 50%;"></div>
                    
                    <small style="color: var(--text-muted); font-weight: 600;"><?php echo $data; ?></small>
                    <div style="margin-top: 5px; background: var(--bg-color); padding: 15px; border-radius: 8px; border: 1px solid var(--border-color);">
                        <?php echo nl2br(htmlspecialchars($nota['conteudo'])); ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="text-align: center; color: var(--text-muted);">Nenhuma anotação registrada ainda.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<div id="tabSalaAula" class="tab-content">
    
    <button onclick="abrirModalMaterial()" class="btn-primary" style="margin-bottom: 20px;">
        <i class="fa-solid fa-cloud-arrow-up"></i> Adicionar Material
    </button>

    <div class="grid-container" style="grid-template-columns: 1fr;">
        <?php if(count($materiais) > 0): ?>
            <?php foreach($materiais as $item): ?>
                <div class="material-item">
                    <div style="display:flex; align-items:center;">
                        <div class="material-icon">
                            <?php if($item['tipo']=='Video'): ?><i class="fa-brands fa-youtube"></i>
                            <?php elseif($item['tipo']=='PDF'): ?><i class="fa-solid fa-file-pdf"></i>
                            <?php else: ?><i class="fa-solid fa-list-check"></i><?php endif; ?>
                        </div>
                        <div>
                            <h4 style="margin-bottom: 5px;"><?php echo htmlspecialchars($item['titulo']); ?></h4>
                            
                            <?php if($item['tipo'] == 'Video'): ?>
                                <a href="<?php echo htmlspecialchars($item['conteudo']); ?>" target="_blank" style="color:var(--sidebar-active); font-size:0.9rem; text-decoration:none;">
                                    Assistir Aula <i class="fa-solid fa-external-link-alt"></i>
                                </a>
                            <?php elseif($item['tipo'] == 'PDF'): ?>
                                <a href="<?php echo htmlspecialchars($item['arquivo_path']); ?>" download class="btn-primary" style="padding: 5px 10px; font-size: 0.8rem;">
                                    Baixar PDF
                                </a>
                            <?php else: ?>
                                <p style="font-size:0.9rem; color:var(--text-muted);"><?php echo htmlspecialchars($item['conteudo']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <a href="excluir_generico.php?tipo=material&id=<?php echo $item['id']; ?>" 
                       onclick="return confirm('Excluir este material?');" style="color:#ef4444;">
                        <i class="fa-solid fa-trash"></i>
                    </a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align:center; color: var(--text-muted); margin-top:20px;">Nenhum material postado ainda.</p>
        <?php endif; ?>
    </div>
</div>

<div id="modalMaterial" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="fecharModalMaterial()">&times;</span>
        <h2>Adicionar Conteúdo</h2><br>
        
        <form action="upload_material.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="turma_id" value="<?php echo $id; ?>">
            
            <div class="form-group">
                <label>Título do Conteúdo</label>
                <input type="text" name="titulo" placeholder="Ex: Aula 1 - Introdução" required>
            </div>

            <div class="form-group">
                <label>Tipo</label>
                <select name="tipo" id="tipoSelect" onchange="mudarInput()">
                    <option value="Video">Link de Vídeo (YouTube/Vimeo)</option>
                    <option value="PDF">Arquivo PDF</option>
                    <option value="Atividade">Texto / Atividade</option>
                </select>
            </div>

            <div class="form-group" id="inputConteudo">
                <label>Link do Vídeo</label>
                <input type="url" name="conteudo" placeholder="https://youtube.com/...">
            </div>
            
            <div class="form-group" id="inputArquivo" style="display:none;">
                <label>Selecione o PDF</label>
                <input type="file" name="arquivo" accept=".pdf">
            </div>

            <button type="submit" class="btn-primary" style="width:100%">Postar Material</button>
        </form>
    </div>
</div>

<script>
    // JS das Abas
    function openTab(evt, tabName) {
        var i, tabcontent, tablinks;
        tabcontent = document.getElementsByClassName("tab-content");
        for (i = 0; i < tabcontent.length; i++) { tabcontent[i].style.display = "none"; tabcontent[i].classList.remove("active"); }
        tablinks = document.getElementsByClassName("tab-btn");
        for (i = 0; i < tablinks.length; i++) { tablinks[i].classList.remove("active"); }
        document.getElementById(tabName).style.display = "block";
        document.getElementById(tabName).classList.add("active");
        evt.currentTarget.classList.add("active");
    }

    // JS do Modal
    const modalMat = document.getElementById("modalMaterial");
    function abrirModalMaterial() { modalMat.style.display = "block"; }
    function fecharModalMaterial() { modalMat.style.display = "none"; }
    
    // Troca os campos do formulário
    function mudarInput() {
        const tipo = document.getElementById("tipoSelect").value;
        const divConteudo = document.getElementById("inputConteudo");
        const divArquivo = document.getElementById("inputArquivo");
        
        if (tipo === 'PDF') {
            divConteudo.style.display = 'none';
            divArquivo.style.display = 'block';
        } else {
            divConteudo.style.display = 'block';
            divArquivo.style.display = 'none';
            divConteudo.querySelector('label').innerText = (tipo === 'Video') ? 'Link do Vídeo' : 'Descrição da Atividade';
        }
    }
</script>

<?php include 'includes/footer.php'; ?>