<?php
// ver_turma.php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['usuario_id'])) { header("Location: index.php"); exit(); }
if (!isset($_GET['id'])) { header("Location: minhas_turmas.php"); exit(); }

$id = $_GET['id'];
$usuario_id = $_SESSION['usuario_id'];

// --- LÓGICA RÁPIDA: ADICIONAR ALUNO (NOVO) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['novo_aluno'])) {
    $nome = trim($_POST['nome']);
    $matricula = trim($_POST['matricula']);
    $email = trim($_POST['email']);
    
    if (!empty($nome)) {
        $stmt = $pdo->prepare("INSERT INTO alunos (turma_id, nome, matricula, email) VALUES (:tid, :nome, :mat, :email)");
        $stmt->execute(['tid' => $id, 'nome' => $nome, 'mat' => $matricula, 'email' => $email]);
        header("Location: ver_turma.php?id=$id&aba=alunos"); // Volta pra mesma aba
        exit();
    }
}

// 1. Busca dados da Turma (com segurança de professor)
$stmt = $pdo->prepare("SELECT * FROM turmas WHERE id = :id AND professor_id = :uid");
$stmt->execute(['id' => $id, 'uid' => $usuario_id]);
$turma = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$turma) { die("Turma não encontrada ou acesso negado."); }

// 2. Busca Anotações
$stmt_notas = $pdo->prepare("SELECT * FROM anotacoes WHERE turma_id = :id ORDER BY data_registro DESC");
$stmt_notas->execute(['id' => $id]);
$anotacoes = $stmt_notas->fetchAll(PDO::FETCH_ASSOC);

// 3. Busca Materiais
try {
    $stmt_mat = $pdo->prepare("SELECT * FROM materiais WHERE turma_id = :id ORDER BY data_postagem DESC");
    $stmt_mat->execute(['id' => $id]);
    $materiais = $stmt_mat->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) { $materiais = []; }

// 4. Busca Quizzes
try {
    $stmt_quiz = $pdo->prepare("SELECT * FROM quizzes WHERE turma_id = :id ORDER BY data_criacao DESC");
    $stmt_quiz->execute(['id' => $id]);
    $quizzes = $stmt_quiz->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) { $quizzes = []; }

// 5. Busca Alunos (NOVO)
try {
    $stmt_alunos = $pdo->prepare("SELECT * FROM alunos WHERE turma_id = :id ORDER BY nome ASC");
    $stmt_alunos->execute(['id' => $id]);
    $alunos = $stmt_alunos->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) { $alunos = []; }

$page_title = $turma['nome'];
include 'includes/header.php';

// Gera a base do link para o quiz
$baseUrl = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
$baseUrl = rtrim($baseUrl, '/\\');

// Verifica qual aba deve abrir (se veio do cadastro de aluno, abre a aba alunos)
$abaAtiva = isset($_GET['aba']) && $_GET['aba'] == 'alunos' ? 'tabAlunos' : 'tabSalaAula';
?>

<style>
    /* Lista de Alunos */
    .student-item {
        display: flex; justify-content: space-between; align-items: center;
        padding: 15px; border-bottom: 1px solid var(--border-color);
        transition: 0.2s;
    }
    .student-item:last-child { border-bottom: none; }
    .student-item:hover { background-color: var(--input-bg); }
    
    .student-info strong { display: block; color: var(--text-color); font-size: 1rem; }
    .student-info span { color: var(--text-muted); font-size: 0.85rem; }
    
    .btn-icon {
        background: transparent; border: none; cursor: pointer; font-size: 1rem;
        padding: 8px; border-radius: 6px; transition: 0.2s;
    }
    .btn-delete { color: #ef4444; background: rgba(239, 68, 68, 0.1); }
    .btn-delete:hover { background: rgba(239, 68, 68, 0.2); }
</style>

<div style="margin-bottom: 20px;">
    <a href="minhas_turmas.php" style="text-decoration: none; color: var(--text-muted);">
        <i class="fa-solid fa-arrow-left"></i> Voltar
    </a>
</div>

<div class="card" style="margin-bottom: 20px;">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <div>
            <h2><?php echo htmlspecialchars($turma['nome']); ?></h2>
            <p style="color: var(--text-muted);">
                <?php echo htmlspecialchars($turma['periodo']); ?> • <?php echo htmlspecialchars($turma['turno']); ?>
            </p>
        </div>
        <div style="text-align:right;">
             <span class="badge ativo" style="font-size:0.9rem; padding: 5px 15px;">Ativa</span>
             <p style="margin:5px 0 0 0; font-size:0.8rem; color:var(--text-muted);"><?php echo count($alunos); ?> Alunos</p>
        </div>
    </div>
</div>

<div class="tab-container">
    <button class="tab-btn <?php echo ($abaAtiva=='tabSalaAula')?'active':''; ?>" onclick="openTab(event, 'tabSalaAula')">
        <i class="fa-solid fa-graduation-cap"></i> Sala de Aula & Conteúdo
    </button>
    <button class="tab-btn <?php echo ($abaAtiva=='tabAlunos')?'active':''; ?>" onclick="openTab(event, 'tabAlunos')">
        <i class="fa-solid fa-users"></i> Alunos (<?php echo count($alunos); ?>)
    </button>
    <button class="tab-btn" onclick="openTab(event, 'tabProntuario')">
        <i class="fa-solid fa-notes-medical"></i> Prontuário / Notas
    </button>
</div>

<div id="tabSalaAula" class="tab-content <?php echo ($abaAtiva=='tabSalaAula')?'active':''; ?>">
    
    <div style="margin-bottom: 25px; display: flex; gap: 10px; flex-wrap: wrap;">
        <button onclick="abrirModalMaterial()" class="btn-primary">
            <i class="fa-solid fa-cloud-arrow-up"></i> Postar Material / Foto
        </button>
        
        <a href="criar_quiz.php?turma_id=<?php echo $id; ?>" class="btn-primary" style="background-color: #6f42c1; border-color: #6f42c1; text-decoration: none; padding: 10px 15px; border-radius: 5px; display: inline-block;">
            <i class="fa-solid fa-clipboard-question"></i> Criar Novo Quiz
        </a>
    </div>

    <?php if(count($quizzes) > 0): ?>
    <h4 style="margin-bottom: 15px; border-bottom: 1px solid var(--border-color); padding-bottom: 5px; color:var(--text-color);">Avaliações Ativas</h4>
    <div class="grid-container" style="grid-template-columns: 1fr; margin-bottom: 30px;">
        <?php foreach($quizzes as $quiz): 
            $linkQuiz = $baseUrl . "/responder_quiz.php?token=" . $quiz['token_acesso'];
        ?>
            <div class="material-item" style="border-left: 4px solid #6f42c1;">
                <div style="display:flex; align-items:center; width: 100%;">
                    <div class="material-icon" style="background: #f3e8ff; color: #6f42c1;">
                        <i class="fa-solid fa-puzzle-piece"></i>
                    </div>
                    <div style="flex-grow: 1; padding-right: 15px;">
                        <h4 style="margin-bottom: 5px; color:var(--text-color);"><?php echo htmlspecialchars($quiz['titulo']); ?></h4>
                        <div style="display: flex; gap: 5px;">
                            <input type="text" value="<?php echo $linkQuiz; ?>" id="link_<?php echo $quiz['id']; ?>" readonly style="width: 100%; padding: 5px; font-size: 0.85rem; border: 1px solid var(--border-color); border-radius: 4px; background: var(--input-bg); color: var(--text-color);">
                            <button onclick="copiarLink('link_<?php echo $quiz['id']; ?>')" style="cursor: pointer; background: #6f42c1; color: white; border: none; border-radius: 4px; padding: 0 10px;" title="Copiar Link">
                                <i class="fa-regular fa-copy"></i>
                            </button>
                        </div>
                    </div>
                    <div style="min-width: 100px; text-align: right;">
                        <a href="ver_respostas.php?id=<?php echo $quiz['id']; ?>" class="btn-primary" style="padding: 5px 10px; font-size: 0.8rem; background-color: #28a745; border: none; text-decoration:none;">
                            Ver Notas
                        </a>
                        <br><br>
                         <a href="actions/excluir_generico.php?tipo=quiz&id=<?php echo $quiz['id']; ?>" 
                           onclick="return confirm('Excluir este quiz?');" style="color:#ef4444; font-size: 0.9rem; text-decoration:none;">
                            <i class="fa-solid fa-trash"></i> Excluir
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <h4 style="margin-bottom: 15px; border-bottom: 1px solid var(--border-color); padding-bottom: 5px; color:var(--text-color);">Materiais e Galeria</h4>
    <div class="grid-container" style="grid-template-columns: 1fr;">
        <?php if(count($materiais) > 0): ?>
            <?php foreach($materiais as $item): ?>
                <div class="material-item">
                    <div style="display:flex; align-items:center;">
                        <div class="material-icon">
                            <?php if($item['tipo']=='Video'): ?><i class="fa-brands fa-youtube" style="color:#ff0000;"></i>
                            <?php elseif($item['tipo']=='PDF'): ?><i class="fa-solid fa-file-pdf" style="color:#dc3545;"></i>
                            <?php elseif($item['tipo']=='Imagem'): ?><i class="fa-regular fa-image" style="color:#28a745;"></i>
                            <?php else: ?><i class="fa-solid fa-file-lines"></i><?php endif; ?>
                        </div>
                        <div>
                            <h4 style="margin-bottom: 5px; color:var(--text-color);"><?php echo htmlspecialchars($item['titulo']); ?></h4>
                            
                            <?php if($item['tipo'] == 'Video'): ?>
                                <a href="<?php echo htmlspecialchars($item['conteudo']); ?>" target="_blank" style="color:var(--sidebar-active); font-size:0.9rem; text-decoration:none;">
                                    <i class="fa-solid fa-play"></i> Assistir Aula
                                </a>
                            <?php elseif($item['tipo'] == 'PDF'): ?>
                                <a href="<?php echo htmlspecialchars($item['arquivo_path']); ?>" download class="btn-primary" style="padding: 5px 10px; font-size: 0.8rem; text-decoration:none;">
                                    <i class="fa-solid fa-download"></i> Baixar PDF
                                </a>
                            <?php elseif($item['tipo'] == 'Imagem'): ?>
                                <a href="<?php echo htmlspecialchars($item['arquivo_path']); ?>" target="_blank" class="btn-primary" style="padding: 5px 10px; font-size: 0.8rem; background-color: #17a2b8; border:none; text-decoration:none;">
                                    <i class="fa-solid fa-eye"></i> Visualizar Foto
                                </a>
                            <?php else: ?>
                                <p style="font-size:0.9rem; color:var(--text-muted);"><?php echo htmlspecialchars($item['conteudo']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <a href="actions/excluir_generico.php?tipo=material&id=<?php echo $item['id']; ?>" 
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

<div id="tabAlunos" class="tab-content <?php echo ($abaAtiva=='tabAlunos')?'active':''; ?>">
    <div class="card">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h3 style="margin:0; color:var(--text-color);">Alunos Matriculados</h3>
            <button onclick="abrirModalAluno()" class="btn-primary" style="font-size:0.9rem;">
                <i class="fa-solid fa-user-plus"></i> Adicionar Aluno
            </button>
        </div>

        <?php if(count($alunos) > 0): ?>
            <?php foreach($alunos as $aluno): ?>
            <div class="student-item">
                <div class="student-info">
                    <strong><?php echo htmlspecialchars($aluno['nome']); ?></strong>
                    <span>Matrícula: <?php echo htmlspecialchars($aluno['matricula']); ?> | <?php echo htmlspecialchars($aluno['email']); ?></span>
                </div>
                <div>
                    <a href="actions/excluir_generico.php?tipo=aluno&id=<?php echo $aluno['id']; ?>" 
                       onclick="return confirm('Tem certeza que deseja remover este aluno?');" 
                       class="btn-icon btn-delete" title="Remover Aluno">
                        <i class="fa-solid fa-trash"></i>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="text-align:center; padding:40px; color:var(--text-muted);">
                <i class="fa-solid fa-users-slash" style="font-size:2.5rem; margin-bottom:15px; opacity:0.3;"></i>
                <p>Nenhum aluno cadastrado nesta turma.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<div id="tabProntuario" class="tab-content">
    <div class="card">
        <h3>Histórico de Evolução</h3>
        <br>
        <form action="actions/salvar_anotacao.php" method="POST" style="margin-bottom: 30px;">
            <input type="hidden" name="turma_id" value="<?php echo $id; ?>">
            <div class="form-group">
                <textarea name="conteudo" rows="3" placeholder="Escreva uma nova anotação sobre a turma ou aluno..." required style="resize: vertical;"></textarea>
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
                    <div style="margin-top: 5px; background: var(--bg-color); padding: 15px; border-radius: 8px; border: 1px solid var(--border-color); color: var(--text-color);">
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

<div id="modalMaterial" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="fecharModalMaterial()">&times;</span>
        <h2>Adicionar Conteúdo</h2><br>
        <form action="actions/upload_material.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="turma_id" value="<?php echo $id; ?>">
            <div class="form-group"><label>Título</label><input type="text" name="titulo" required></div>
            <div class="form-group"><label>Tipo</label>
                <select name="tipo" id="tipoSelect" onchange="mudarInput()">
                    <option value="Imagem">Imagem / Foto</option>
                    <option value="PDF">Arquivo PDF</option>
                    <option value="Video">Link de Vídeo</option>
                    <option value="Atividade">Aviso / Texto</option>
                </select>
            </div>
            <div class="form-group" id="inputConteudo" style="display:none;"><label>Link/Texto</label><input type="text" name="conteudo"></div>
            <div class="form-group" id="inputArquivo"><label>Arquivo</label><input type="file" name="arquivo" id="campoArquivo"></div>
            <button type="submit" class="btn-primary" style="width:100%; margin-top:15px;">Salvar</button>
        </form>
    </div>
</div>

<div id="modalAluno" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="fecharModalAluno()">&times;</span>
        <h2>Novo Aluno</h2>
        <form method="POST">
            <input type="hidden" name="novo_aluno" value="1">
            <div class="form-group">
                <label>Nome Completo</label>
                <input type="text" name="nome" required placeholder="Ex: João da Silva">
            </div>
            <div class="form-group">
                <label>Matrícula</label>
                <input type="text" name="matricula" placeholder="Ex: 2024001">
            </div>
            <div class="form-group">
                <label>E-mail</label>
                <input type="email" name="email" placeholder="joao@email.com">
            </div>
            <button type="submit" class="btn-primary" style="width:100%; margin-top:10px;">Cadastrar</button>
        </form>
    </div>
</div>

<script>
    // Controle de Abas
    function openTab(evt, tabName) {
        var i, tabcontent, tablinks;
        tabcontent = document.getElementsByClassName("tab-content");
        for (i = 0; i < tabcontent.length; i++) { 
            tabcontent[i].style.display = "none"; 
            tabcontent[i].classList.remove("active"); 
        }
        tablinks = document.getElementsByClassName("tab-btn");
        for (i = 0; i < tablinks.length; i++) { 
            tablinks[i].classList.remove("active"); 
        }
        document.getElementById(tabName).style.display = "block";
        document.getElementById(tabName).classList.add("active");
        evt.currentTarget.classList.add("active");
    }

    // Modal Material
    const modalMat = document.getElementById("modalMaterial");
    function abrirModalMaterial() { modalMat.style.display = "block"; }
    function fecharModalMaterial() { modalMat.style.display = "none"; }
    
    // Modal Aluno
    const modalAlu = document.getElementById("modalAluno");
    function abrirModalAluno() { modalAlu.style.display = "block"; }
    function fecharModalAluno() { modalAlu.style.display = "none"; }
    
    // Upload Dinâmico
    function mudarInput() {
        const tipo = document.getElementById("tipoSelect").value;
        const divConteudo = document.getElementById("inputConteudo");
        const divArquivo = document.getElementById("inputArquivo");
        
        if (tipo === 'PDF' || tipo === 'Imagem') {
            divConteudo.style.display = 'none'; divArquivo.style.display = 'block';
        } else {
            divConteudo.style.display = 'block'; divArquivo.style.display = 'none';
        }
    }
    
    // Copiar Link
    function copiarLink(elementId) {
        var copyText = document.getElementById(elementId);
        copyText.select(); navigator.clipboard.writeText(copyText.value); alert("Link copiado!");
    }
    
    // Iniciar
    document.addEventListener("DOMContentLoaded", function() { mudarInput(); });
    // Fechar modais ao clicar fora
    window.onclick = function(e) { 
        if (e.target == modalMat) fecharModalMaterial();
        if (e.target == modalAlu) fecharModalAluno();
    }
</script>

<?php include 'includes/footer.php'; ?>