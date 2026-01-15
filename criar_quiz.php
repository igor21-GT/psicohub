<?php
// criar_quiz.php
include 'config/db.php';
include 'includes/header.php';

if (!isset($_GET['turma_id'])) {
    die("<div style='padding:20px;'>Turma não especificada. <a href='minhas_turmas.php'>Voltar</a></div>");
}
$turma_id = $_GET['turma_id'];
?>

<style>
    /* INTEGRAÇÃO COM STYLE.CSS GLOBAL */
    /* Não definimos cores fixas aqui, usamos as variáveis do seu tema */

    .page-header {
        display: flex; justify-content: space-between; align-items: center;
        margin-bottom: 30px; padding-bottom: 20px; 
        border-bottom: 1px solid var(--border-color);
    }
    
    h1 { font-weight: 700; color: var(--text-color); margin: 0; font-size: 1.8rem; }
    p.subtitle { color: var(--text-muted); margin: 5px 0 0 0; }

    /* Botão Voltar */
    .btn-back {
        background: var(--card-bg); 
        color: var(--text-muted); 
        padding: 10px 20px;
        border-radius: 8px; 
        text-decoration: none; 
        border: 1px solid var(--border-color);
        display: inline-flex; align-items: center; gap: 8px; font-weight: 500; 
        transition: 0.2s;
    }
    .btn-back:hover { 
        background: var(--border-color); 
        color: var(--text-color); 
    }

    /* Cards */
    .custom-card {
        background-color: var(--card-bg); 
        border: 1px solid var(--border-color);
        border-radius: 12px; 
        padding: 25px; 
        margin-bottom: 20px;
        box-shadow: var(--card-shadow);
    }
    .custom-card h3 { color: var(--text-color); margin: 0; }

    /* Inputs */
    label { color: var(--text-muted); font-size: 0.9rem; margin-bottom: 8px; display: block; font-weight: 500; }

    .form-control {
        background-color: var(--input-bg); 
        border: 1px solid var(--border-color);
        color: var(--text-color); 
        border-radius: 8px; padding: 12px; width: 100%;
        font-family: inherit;
    }
    .form-control:focus { border-color: var(--sidebar-active); outline: none; }

    /* Opções de Resposta */
    .option-group {
        display: flex; align-items: center; margin-bottom: 10px;
        background: var(--input-bg); 
        border: 1px solid var(--border-color);
        border-radius: 8px; padding: 5px 15px;
    }
    .option-radio { margin-right: 15px; transform: scale(1.3); accent-color: var(--sidebar-active); }
    .option-input { 
        border: none; background: transparent; padding: 10px 0; width: 100%; 
        color: var(--text-color); outline: none; 
    }

    /* Botões de Ação */
    .btn-action { padding: 12px 20px; font-weight: 600; border-radius: 8px; cursor: pointer; transition: 0.2s; }
    
    .btn-add { 
        background-color: var(--card-bg); 
        color: var(--text-color); 
        border: 1px solid var(--border-color); 
        width: 100%; 
    }
    .btn-add:hover { background-color: var(--border-color); }

    .btn-success { 
        background-color: #10b981; 
        color: white; border: none; width: 100%; 
    }
    .btn-success:hover { background-color: #059669; }

    .btn-remove { 
        color: #ef4444; background: rgba(239, 68, 68, 0.1); 
        border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer; 
    }
    .btn-remove:hover { background: rgba(239, 68, 68, 0.2); }

    @keyframes slideDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
    .questao-box { animation: slideDown 0.3s ease-out; }
</style>

<div class="page-header">
    <div>
        <h1><i class="fa-solid fa-puzzle-piece" style="color:var(--sidebar-active); margin-right: 10px;"></i> Criar Novo Quiz</h1>
        <p class="subtitle">Monte a avaliação para sua turma</p>
    </div>
    <div>
        <a href="ver_turma.php?id=<?php echo $turma_id; ?>" class="btn-back">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<section class="content">
    <form action="actions/salvar_quiz.php" method="POST">
        <input type="hidden" name="turma_id" value="<?php echo $turma_id; ?>">

        <div class="custom-card">
            <div style="border-bottom: 1px solid var(--border-color); padding-bottom: 15px; margin-bottom: 20px;">
                <h3>Configurações da Prova</h3>
            </div>
            
            <div class="form-group" style="margin-bottom: 15px;">
                <label>Título do Quiz</label>
                <input type="text" name="titulo" class="form-control" required placeholder="Ex: Avaliação de História - 1º Bimestre">
            </div>
            <div class="form-group">
                <label>Instruções</label>
                <textarea name="descricao" class="form-control" rows="2" placeholder="Ex: Você tem 50 minutos."></textarea>
            </div>
        </div>

        <div id="container-questoes"></div>

        <div class="row mb-5" style="margin-bottom: 30px;">
            <div class="col-12">
                <button type="button" class="btn btn-add btn-action" onclick="adicionarQuestao()">
                    <i class="fas fa-plus-circle"></i> Adicionar Nova Questão
                </button>
            </div>
        </div>

        <div class="row pb-5" style="padding-bottom: 50px;">
            <div class="col-12">
                <button type="submit" class="btn btn-success btn-action">
                    <i class="fa-solid fa-check"></i> Finalizar e Gerar Link
                </button>
            </div>
        </div>
    </form>
</section>

<script>
    let questaoCount = 0;
    function adicionarQuestao() {
        questaoCount++;
        const html = `
        <div class="custom-card questao-box" id="q_${questaoCount}">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">
                <h3 style="font-size:1.1rem; color: var(--sidebar-active); margin:0;">Questão #${questaoCount}</h3>
                <button type="button" class="btn-remove" onclick="removerQuestao(${questaoCount})"><i class="fas fa-trash-alt"></i> Remover</button>
            </div>
            <div class="form-group">
                <label>Enunciado</label>
                <textarea name="questoes[${questaoCount}][enunciado]" class="form-control" rows="2" required placeholder="Pergunta..."></textarea>
            </div>
            <label style="margin-top:20px; margin-bottom:10px;">Alternativas</label>
            <div class="row">
                ${gerarOpcaoHTML(questaoCount, 'A', 0)}
                ${gerarOpcaoHTML(questaoCount, 'B', 1)}
                ${gerarOpcaoHTML(questaoCount, 'C', 2)}
                ${gerarOpcaoHTML(questaoCount, 'D', 3)}
                ${gerarOpcaoHTML(questaoCount, 'E', 4)}
            </div>
        </div>`;
        document.getElementById('container-questoes').insertAdjacentHTML('beforeend', html);
    }

    function gerarOpcaoHTML(qID, letra, valor) {
        return `
        <div style="margin-bottom: 8px;">
            <div class="option-group">
                <input type="radio" name="questoes[${qID}][correta]" value="${valor}" class="option-radio" required>
                <span style="color: var(--sidebar-active); font-weight:bold; margin-right:10px; min-width: 20px;">${letra})</span>
                <input type="text" name="questoes[${qID}][opcoes][]" class="option-input" placeholder="Opção ${letra}" required>
            </div>
        </div>`;
    }

    function removerQuestao(id) {
        if(confirm('Remover questão?')) {
            document.getElementById('q_' + id).remove();
        }
    }
    window.onload = function() { adicionarQuestao(); };
</script>

<?php include 'includes/footer.php'; ?>