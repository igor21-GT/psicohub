<?php
// ver_turma.php (VERSÃO: ATIVIDADE DISCURSIVA E MULTIPLA ESCOLHA)
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['usuario_id'])) { header("Location: index.php"); exit(); }
if (!isset($_GET['id'])) { header("Location: minhas_turmas.php"); exit(); }

$id = $_GET['id'];
$usuario_id = $_SESSION['usuario_id'];

// --- ADICIONAR ALUNO ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['novo_aluno'])) {
    $nome = trim($_POST['nome']);
    $matricula = trim($_POST['matricula']);
    $email = trim($_POST['email']);
    if (!empty($nome)) {
        $stmt = $pdo->prepare("INSERT INTO alunos (turma_id, nome, matricula, email) VALUES (:tid, :nome, :mat, :email)");
        $stmt->execute(['tid' => $id, 'nome' => $nome, 'mat' => $matricula, 'email' => $email]);
        header("Location: ver_turma.php?id=$id&aba=alunos");
        exit();
    }
}

// Buscas
$stmt = $pdo->prepare("SELECT * FROM turmas WHERE id = :id AND professor_id = :uid");
$stmt->execute(['id' => $id, 'uid' => $usuario_id]);
$turma = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$turma) { die("Turma não encontrada."); }

$stmt_notas = $pdo->prepare("SELECT * FROM anotacoes WHERE turma_id = :id ORDER BY data_registro DESC");
$stmt_notas->execute(['id' => $id]);
$anotacoes = $stmt_notas->fetchAll(PDO::FETCH_ASSOC);

// Busca Materiais
try {
    $query = "SELECT m.*, (SELECT COUNT(*) FROM entregas e WHERE e.material_id = m.id) as qtd_entregas FROM materiais m WHERE m.turma_id = :id ORDER BY m.id DESC";
    $stmt_mat = $pdo->prepare($query);
    $stmt_mat->execute(['id' => $id]);
    $materiais = $stmt_mat->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) { $materiais = []; }

try {
    $stmt_quiz = $pdo->prepare("SELECT * FROM quizzes WHERE turma_id = :id ORDER BY data_criacao DESC");
    $stmt_quiz->execute(['id' => $id]);
    $quizzes = $stmt_quiz->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) { $quizzes = []; }

try {
    $stmt_alunos = $pdo->prepare("SELECT * FROM alunos WHERE turma_id = :id ORDER BY nome ASC");
    $stmt_alunos->execute(['id' => $id]);
    $alunos = $stmt_alunos->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) { $alunos = []; }

$page_title = $turma['nome'];
include 'includes/header.php';

$baseUrl = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
$baseUrl = rtrim($baseUrl, '/\\');
$linkHubAluno = $baseUrl . "/hub_turma.php?t=" . base64_encode($id);
$abaAtiva = isset($_GET['aba']) && $_GET['aba'] == 'alunos' ? 'tabAlunos' : 'tabSalaAula';
?>

<style>
    .materials-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 20px; margin-top: 20px; }
    .mat-card { background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 12px; overflow: hidden; transition: transform 0.2s, box-shadow 0.2s; display: flex; flex-direction: column; position: relative; text-decoration: none; height: 100%; }
    .mat-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.15); border-color: var(--sidebar-active); }
    .mat-cover { height: 130px; background-color: #334155; background-size: cover; background-position: center; display: flex; align-items: center; justify-content: center; position: relative; }
    .mat-cover i { font-size: 3rem; color: white; z-index: 2; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.5)); }
    .mat-body { padding: 15px; display: flex; flex-direction: column; flex: 1; }
    .mat-title { font-size: 0.95rem; font-weight: 600; color: var(--text-color); margin-bottom: 5px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .mat-meta { font-size: 0.75rem; color: var(--text-muted); margin-top: auto; padding-top: 10px; display: flex; justify-content: space-between; align-items: center; }
    
    .btn-trash-card { color: #ef4444; background: rgba(255,255,255,0.9); width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; position: absolute; top: 10px; right: 10px; z-index: 5; box-shadow: 0 2px 4px rgba(0,0,0,0.2); transition: 0.2s; }
    .btn-trash-card:hover { background: #ef4444; color: white; transform: scale(1.1); }
    .btn-eye { background: rgba(99, 102, 241, 0.1); color: #818cf8; border: 1px solid rgba(99, 102, 241, 0.2); padding: 5px 12px; border-radius: 6px; text-decoration: none; font-size: 0.85rem; display: flex; align-items: center; gap: 6px; transition: 0.2s; }
    .btn-eye:hover { background: #6366f1; color: white; }
    
    .badge-entregas { background: #f59e0b; color: #fff; font-size: 0.7rem; padding: 2px 8px; border-radius: 10px; font-weight: bold; display: inline-flex; align-items: center; gap: 4px; }
    .badge-valor { background: #eab308; color: #000; font-size: 0.7rem; padding: 2px 6px; border-radius: 4px; font-weight: bold; margin-right: 5px; }

    .student-item { display: flex; justify-content: space-between; align-items: center; padding: 15px; border-bottom: 1px solid var(--border-color); }
    .btn-icon { background: transparent; border: none; cursor: pointer; padding: 8px; border-radius: 6px; }
    .btn-delete { color: #ef4444; background: rgba(239, 68, 68, 0.1); }
</style>

<div style="margin-bottom: 20px;">
    <a href="minhas_turmas.php" style="text-decoration: none; color: var(--text-muted);"><i class="fa-solid fa-arrow-left"></i> Voltar</a>
</div>

<div class="card" style="margin-bottom: 20px;">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <div>
            <h2><?php echo htmlspecialchars($turma['nome']); ?></h2>
            <p style="color: var(--text-muted);"><?php echo htmlspecialchars($turma['periodo']); ?> • <?php echo htmlspecialchars($turma['turno']); ?></p>
        </div>
        <div style="text-align:right; display: flex; flex-direction: column; align-items: flex-end;">
             <div style="display: flex; gap: 10px; align-items: center; margin-bottom: 5px;">
                 <a href="<?php echo $linkHubAluno; ?>" target="_blank" class="btn-eye"><i class="fa-solid fa-eye"></i> Ver como Aluno</a>
                 <span class="badge ativo" style="font-size:0.9rem; padding: 5px 15px;">Ativa</span>
             </div>
             <p style="margin:0; font-size:0.8rem; color:var(--text-muted);"><?php echo count($alunos); ?> Alunos</p>
        </div>
    </div>
</div>

<div class="tab-container">
    <button class="tab-btn <?php echo ($abaAtiva=='tabSalaAula')?'active':''; ?>" onclick="openTab(event, 'tabSalaAula')"><i class="fa-solid fa-graduation-cap"></i> Sala de Aula & Conteúdo</button>
    <button class="tab-btn <?php echo ($abaAtiva=='tabAlunos')?'active':''; ?>" onclick="openTab(event, 'tabAlunos')"><i class="fa-solid fa-users"></i> Alunos (<?php echo count($alunos); ?>)</button>
    <button class="tab-btn" onclick="openTab(event, 'tabProntuario')"><i class="fa-solid fa-notes-medical"></i> Prontuário / Notas</button>
</div>

<div id="tabSalaAula" class="tab-content <?php echo ($abaAtiva=='tabSalaAula')?'active':''; ?>">
    <div style="margin-bottom: 25px; display: flex; flex-wrap: wrap; gap: 15px; align-items: center; justify-content: space-between; background: var(--card-bg); padding: 15px; border-radius: 12px; border: 1px solid var(--border-color);">
        
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <button onclick="abrirModalMaterial()" class="btn-primary" style="background-color: #334155; border:none;">
                <i class="fa-solid fa-cloud-arrow-up"></i> Postar Material
            </button>
            <button onclick="abrirModalParcial()" class="btn-primary" style="background-color: #ea580c; border:none;">
                <i class="fa-solid fa-file-pen"></i> Atividade Parcial
            </button>
            <a href="criar_quiz.php?turma_id=<?php echo $id; ?>" class="btn-primary" style="background-color: #6f42c1; border-color: #6f42c1; text-decoration: none; padding: 10px 15px; border-radius: 5px; display: inline-flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-clipboard-question"></i> Quiz
            </a>
        </div>

        <div style="position: relative; flex: 1; min-width: 200px; max-width: 400px;">
            <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
            <input type="text" id="filtroMateriais" onkeyup="filtrarMateriais()" placeholder="Pesquisar..." style="width: 100%; padding: 10px 10px 10px 35px; border-radius: 8px; border: 1px solid var(--border-color); background: var(--input-bg); color: var(--text-color); outline: none;">
        </div>
    </div>

    <div style="background: linear-gradient(to right, #1e293b, #0f172a); border: 1px solid #334155; padding: 15px; border-radius: 10px; margin-bottom: 30px; display: flex; align-items: center; gap: 15px;">
        <div style="background: rgba(99, 102, 241, 0.2); color: #818cf8; width: 45px; height: 45px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;"><i class="fa-solid fa-share-nodes"></i></div>
        <div style="flex: 1;">
            <h4 style="margin: 0 0 5px 0; color: #fff;">Link da Área do Aluno</h4>
            <p style="margin: 0; font-size: 0.85rem; color: #94a3b8;">Compartilhe este link para os alunos acessarem os materiais.</p>
        </div>
        <div style="display: flex; gap: 8px;">
            <input type="text" value="<?php echo $linkHubAluno; ?>" id="linkHubInput" readonly style="background: #020617; border: 1px solid #334155; color: #cbd5e1; padding: 8px; border-radius: 6px; width: 220px; font-size: 0.85rem;">
            <button onclick="copiarLink('linkHubInput')" style="background: #6366f1; color: white; border: none; padding: 8px 15px; border-radius: 6px; cursor: pointer;"><i class="fa-regular fa-copy"></i> Copiar</button>
        </div>
    </div>

    <?php if(count($quizzes) > 0): ?>
    <h4 style="margin-bottom: 15px; border-bottom: 1px solid var(--border-color); padding-bottom: 5px; color:var(--text-color);">Avaliações (Quiz)</h4>
    <div style="display:grid; gap:10px; margin-bottom: 30px;">
        <?php foreach($quizzes as $quiz): $linkQuiz = $baseUrl . "/responder_quiz.php?token=" . $quiz['token_acesso']; ?>
            <div style="background: var(--card-bg); border: 1px solid var(--border-color); border-left: 4px solid #6f42c1; padding: 15px; border-radius: 8px; display:flex; align-items:center;">
                <div style="flex:1;"><strong style="color:var(--text-color);"><?php echo htmlspecialchars($quiz['titulo']); ?></strong>
                    <div style="display: flex; gap: 5px; margin-top:5px;">
                        <input type="text" value="<?php echo $linkQuiz; ?>" id="link_<?php echo $quiz['id']; ?>" readonly style="font-size: 0.8rem; padding: 4px; width: 250px; background:var(--input-bg); border:1px solid var(--border-color); color:var(--text-muted);">
                        <button onclick="copiarLink('link_<?php echo $quiz['id']; ?>')" style="cursor: pointer; background: #6f42c1; color: white; border: none; border-radius: 4px; padding: 0 10px;"><i class="fa-regular fa-copy"></i></button>
                    </div>
                </div>
                <div style="display:flex; gap:10px;">
                    <a href="ver_respostas.php?id=<?php echo $quiz['id']; ?>" style="color:#28a745; text-decoration:none; font-size:0.9rem;"><i class="fa-solid fa-chart-bar"></i> Notas</a>
                    <a href="actions/excluir_generico.php?tipo=quiz&id=<?php echo $quiz['id']; ?>" onclick="return confirm('Excluir?');" style="color:#ef4444; text-decoration:none; font-size:0.9rem;"><i class="fa-solid fa-trash"></i></a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <h4 style="margin-bottom: 15px; border-bottom: 1px solid var(--border-color); padding-bottom: 5px; color:var(--text-color);">Materiais e Atividades Parciais</h4>
    <?php if(count($materiais) > 0): ?>
        <div class="materials-grid">
            <?php foreach($materiais as $item): 
                $tipo = $item['tipo']; $bgStyle = ""; $icone = ""; $isAtividade = ($tipo == 'Atividade');
                $valor = $item['valor_nota'] ?? null;
                $formato = $item['formato'] ?? 'upload';
                
                if ($tipo == 'Imagem' && !empty($item['arquivo_path'])) { $bgStyle = "background-image: url('".htmlspecialchars($item['arquivo_path'])."');"; }
                elseif ($tipo == 'PDF') { $bgStyle = "background: linear-gradient(135deg, #ef4444 0%, #991b1b 100%);"; $icone = "<i class='fa-solid fa-file-pdf'></i>"; }
                elseif ($tipo == 'Video') { $bgStyle = "background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);"; $icone = "<i class='fa-regular fa-circle-play'></i>"; }
                elseif ($isAtividade) { $bgStyle = "background: linear-gradient(135deg, #ea580c 0%, #9a3412 100%);"; $icone = "<i class='fa-solid fa-file-pen'></i>"; }
                else { $bgStyle = "background: linear-gradient(135deg, #64748b 0%, #334155 100%);"; $icone = "<i class='fa-solid fa-align-left'></i>"; }

                // Se for upload, link pro arquivo. Se for discursiva/multipla, não tem arquivo pra ver.
                $linkDestino = ($tipo == 'Video' || $tipo == 'Atividade') ? ($item['conteudo'] ?? '#') : ($item['arquivo_path'] ?? '#');
                $dataPostagem = isset($item['data_postagem']) ? date('d/m', strtotime($item['data_postagem'])) : 'Recente';
                $qtdEntregas = $item['qtd_entregas'] ?? 0;
            ?>
                <div class="mat-card">
                    <a href="actions/excluir_generico.php?tipo=material&id=<?php echo $item['id']; ?>" onclick="return confirm('Excluir este material?');" class="btn-trash-card" title="Excluir"><i class="fa-solid fa-trash"></i></a>
                    <?php if($isAtividade): ?>
                        <a href="ver_entregas.php?id=<?php echo $item['id']; ?>" style="text-decoration: none; color: inherit; display: flex; flex-direction: column; height: 100%;">
                    <?php else: ?>
                        <a href="<?php echo htmlspecialchars($linkDestino); ?>" target="_blank" style="text-decoration: none; color: inherit; display: flex; flex-direction: column; height: 100%;">
                    <?php endif; ?>
                        <div class="mat-cover" style="<?php echo $bgStyle; ?>"><?php echo $icone; ?></div>
                        <div class="mat-body">
                            <div class="mat-title" title="<?php echo htmlspecialchars($item['titulo']); ?>"><?php echo htmlspecialchars($item['titulo']); ?></div>
                            <div class="mat-meta">
                                <span><?php echo ($formato == 'upload' && $isAtividade) ? 'Ativ. Upload' : ucfirst($formato); ?></span>
                                <div>
                                    <?php if($valor): ?><span class="badge-valor">Vale <?php echo $valor; ?></span><?php endif; ?>
                                    <?php if($isAtividade): ?>
                                        <span class="badge-entregas"><i class="fa-solid fa-inbox"></i> <?php echo $qtdEntregas; ?></span>
                                    <?php else: ?>
                                        <span><i class="fa-regular fa-calendar"></i> <?php echo $dataPostagem; ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p style="text-align:center; color: var(--text-muted); margin-top:40px; padding: 30px; border: 2px dashed var(--border-color); border-radius: 10px;">Nenhum material postado ainda.</p>
    <?php endif; ?>
</div>

<div id="tabAlunos" class="tab-content <?php echo ($abaAtiva=='tabAlunos')?'active':''; ?>">
    <div class="card">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h3 style="margin:0; color:var(--text-color);">Alunos Matriculados</h3>
            <button onclick="abrirModalAluno()" class="btn-primary" style="font-size:0.9rem;"><i class="fa-solid fa-user-plus"></i> Adicionar Aluno</button>
        </div>
        <?php if(count($alunos) > 0): ?>
            <?php foreach($alunos as $aluno): ?>
            <div class="student-item">
                <div class="student-info"><strong style="color:var(--text-color);"><?php echo htmlspecialchars($aluno['nome']); ?></strong><span style="color:var(--text-muted); font-size:0.85rem;">Mat: <?php echo htmlspecialchars($aluno['matricula']); ?> | <?php echo htmlspecialchars($aluno['email']); ?></span></div>
                <div><a href="actions/excluir_generico.php?tipo=aluno&id=<?php echo $aluno['id']; ?>" onclick="return confirm('Tem certeza que deseja remover este aluno?');" class="btn-icon btn-delete" title="Remover"><i class="fa-solid fa-trash"></i></a></div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align:center; padding:30px; color:var(--text-muted);">Nenhum aluno cadastrado.</p>
        <?php endif; ?>
    </div>
</div>

<div id="tabProntuario" class="tab-content">
    <div class="card">
        <h3>Histórico de Evolução</h3><br>
        <form action="actions/salvar_anotacao.php" method="POST" style="margin-bottom: 30px;">
            <input type="hidden" name="turma_id" value="<?php echo $id; ?>">
            <div class="form-group"><textarea name="conteudo" rows="3" placeholder="Escreva uma anotação sobre a turma ou aluno..." required style="resize: vertical;"></textarea></div>
            <div style="text-align: right;"><button type="submit" class="btn-primary">Adicionar Nota</button></div>
        </form>
        <div class="timeline">
            <?php if(count($anotacoes) > 0): foreach($anotacoes as $nota): $data = date('d/m/Y - H:i', strtotime($nota['data_registro'])); ?>
                <div style="border-left: 3px solid var(--sidebar-active); padding-left: 20px; margin-bottom: 20px; position: relative;">
                    <div style="position: absolute; left: -8px; top: 0; width: 13px; height: 13px; background: var(--sidebar-active); border-radius: 50%;"></div>
                    <small style="color: var(--text-muted); font-weight: 600;"><?php echo $data; ?></small>
                    <div style="margin-top: 5px; background: var(--bg-color); padding: 15px; border-radius: 8px; border: 1px solid var(--border-color); color: var(--text-color);"><?php echo nl2br(htmlspecialchars($nota['conteudo'])); ?></div>
                </div>
            <?php endforeach; else: ?><p style="text-align: center; color: var(--text-muted);">Nenhuma anotação registrada ainda.</p><?php endif; ?>
        </div>
    </div>
</div>

<div id="modalMaterial" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="fecharModalMaterial()">&times;</span>
        <h2>Postar Material</h2><br>
        <form action="actions/upload_material.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="turma_id" value="<?php echo $id; ?>">
            <div class="form-group"><label>Título</label><input type="text" name="titulo" required></div>
            <div class="form-group"><label>Tipo</label>
                <select name="tipo" id="tipoSelect" onchange="mudarInput()">
                    <option value="Imagem">Imagem / Foto</option>
                    <option value="PDF">Arquivo PDF</option>
                    <option value="Video">Link de Vídeo</option>
                </select>
            </div>
            <div class="form-group" id="inputConteudo" style="display:none;"><label>Link/Texto</label><input type="text" name="conteudo"></div>
            <div class="form-group" id="inputArquivo"><label>Arquivo</label><input type="file" name="arquivo" id="campoArquivo"></div>
            <button type="submit" class="btn-primary" style="width:100%; margin-top:15px;">Salvar</button>
        </form>
    </div>
</div>

<div id="modalParcial" class="modal">
    <div class="modal-content" style="border-left: 5px solid #ea580c; max-width: 500px;">
        <span class="close-btn" onclick="fecharModalParcial()">&times;</span>
        <h2 style="color: #ea580c;">Nova Atividade Parcial</h2>
        
        <form action="actions/upload_material.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="turma_id" value="<?php echo $id; ?>">
            <input type="hidden" name="tipo" value="Atividade">

            <div class="form-group">
                <label>Formato da Questão</label>
                <select name="formato" id="formatoSelect" onchange="mudarFormato()" style="width:100%; padding:10px; border-radius:6px; background:#0f172a; color:#fff; border:1px solid #334155;">
                    <option value="upload">Solicitar Arquivo (Padrão)</option>
                    <option value="discursiva">Discursiva (Texto Livre)</option>
                    <option value="multipla">Múltipla Escolha</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Título</label>
                <input type="text" name="titulo" placeholder="Ex: Avaliação 1" required>
            </div>
            
            <div class="form-group">
                <label id="labelInstrucao">Instruções / Enunciado</label>
                <textarea name="conteudo" rows="3" placeholder="Digite a pergunta ou instrução..." style="width:100%; background:#0f172a; color:#fff; border:1px solid #334155; border-radius:6px; padding:10px;" required></textarea>
            </div>

            <div id="divOpcoes" style="display:none; background: #0f172a; padding: 10px; border-radius: 6px; border: 1px dashed #334155; margin-bottom: 15px;">
                <label style="color:#f59e0b; margin-bottom:5px; display:block;">Alternativas:</label>
                <input type="text" name="opt_a" placeholder="Opção A" style="margin-bottom:5px;">
                <input type="text" name="opt_b" placeholder="Opção B" style="margin-bottom:5px;">
                <input type="text" name="opt_c" placeholder="Opção C" style="margin-bottom:5px;">
                <input type="text" name="opt_d" placeholder="Opção D" style="margin-bottom:5px;">
            </div>

            <div style="display:flex; gap:10px;">
                <div class="form-group" style="flex:1;">
                    <label style="color:#f59e0b;">Valor (Nota)</label>
                    <input type="number" name="valor_nota" step="0.1" min="0" max="10" placeholder="Ex: 2.0">
                </div>
                <div class="form-group" style="flex:1;">
                    <label style="color:#f59e0b;">Entrega Até</label>
                    <input type="date" name="data_limite" style="border-color:#f59e0b;">
                </div>
            </div>

            <button type="submit" class="btn-primary" style="width:100%; margin-top:15px; background-color: #ea580c; border:none;">Criar Atividade</button>
        </form>
    </div>
</div>

<div id="modalAluno" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="fecharModalAluno()">&times;</span>
        <h2>Novo Aluno</h2>
        <form method="POST"><input type="hidden" name="novo_aluno" value="1"><div class="form-group"><label>Nome</label><input type="text" name="nome" required></div><div class="form-group"><label>Matrícula</label><input type="text" name="matricula"></div><div class="form-group"><label>E-mail</label><input type="email" name="email"></div><button type="submit" class="btn-primary" style="width:100%; margin-top:10px;">Cadastrar</button></form>
    </div>
</div>

<script>
    function openTab(evt, tabName) { var i, tabcontent = document.getElementsByClassName("tab-content"), tablinks = document.getElementsByClassName("tab-btn"); for (i = 0; i < tabcontent.length; i++) tabcontent[i].style.display = "none"; for (i = 0; i < tablinks.length; i++) tablinks[i].classList.remove("active"); document.getElementById(tabName).style.display = "block"; evt.currentTarget.classList.add("active"); }
    function filtrarMateriais() { var input = document.getElementById('filtroMateriais'); var filtro = input.value.toLowerCase(); var cards = document.getElementsByClassName('mat-card'); var titulos = document.getElementsByClassName('mat-title'); for (var i = 0; i < cards.length; i++) { var texto = titulos[i].textContent || titulos[i].innerText; if (texto.toLowerCase().indexOf(filtro) > -1) cards[i].style.display = ""; else cards[i].style.display = "none"; } }
    
    // MODAIS
    const modalMat = document.getElementById("modalMaterial"); 
    const modalAlu = document.getElementById("modalAluno");
    const modalPar = document.getElementById("modalParcial");

    function abrirModalMaterial() { modalMat.style.display = "block"; } function fecharModalMaterial() { modalMat.style.display = "none"; }
    function abrirModalAluno() { modalAlu.style.display = "block"; } function fecharModalAluno() { modalAlu.style.display = "none"; }
    function abrirModalParcial() { modalPar.style.display = "block"; } function fecharModalParcial() { modalPar.style.display = "none"; }

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

    function mudarFormato() {
        const fmt = document.getElementById("formatoSelect").value;
        const divOpt = document.getElementById("divOpcoes");
        if (fmt === 'multipla') {
            divOpt.style.display = 'block';
        } else {
            divOpt.style.display = 'none';
        }
    }
    
    function copiarLink(elementId) { var copyText = document.getElementById(elementId); copyText.select(); navigator.clipboard.writeText(copyText.value).then(() => alert("Link copiado!")).catch(() => alert("Erro ao copiar.")); }
    document.addEventListener("DOMContentLoaded", function() { mudarInput(); });
    window.onclick = function(e) { if (e.target == modalMat) fecharModalMaterial(); if (e.target == modalAlu) fecharModalAluno(); if (e.target == modalPar) fecharModalParcial(); }
</script>
<?php include 'includes/footer.php'; ?>