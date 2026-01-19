<?php
// dashboard.php
session_start();

// Segurança
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}

require_once 'config/db.php';

$page_title = "Inicial";

// --- 1. CONSULTAS SQL GERAIS (DADOS REAIS) ---

// Totais
try {
    $total_turmas = $pdo->query("SELECT COUNT(*) FROM turmas")->fetchColumn();
    $proximos_eventos_count = $pdo->query("SELECT COUNT(*) FROM agendamentos WHERE data_evento >= NOW()")->fetchColumn();
} catch (Exception $e) {
    $total_turmas = 0; $proximos_eventos_count = 0;
}

// Média Geral da Escola
try {
    $stmt = $pdo->query("SELECT AVG(nota_final) FROM respostas_alunos");
    $media_geral = number_format((float)$stmt->fetchColumn(), 1);
} catch (Exception $e) { $media_geral = "0.0"; }

// Feed de Atividades Recentes
$sql_feed = "
    (SELECT 'quiz' as tipo, nome_aluno as autor, nota_final as info, data_envio as data, quizzes.titulo as extra 
     FROM respostas_alunos 
     JOIN quizzes ON respostas_alunos.quiz_id = quizzes.id)
    UNION
    (SELECT 'material' as tipo, 'Você' as autor, titulo as info, data_postagem as data, tipo as extra 
     FROM materiais)
    ORDER BY data DESC LIMIT 5
";
try {
    $feed = $pdo->query($sql_feed)->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) { $feed = []; }

// Agenda Rápida (Lista lateral)
try {
    $stmt = $pdo->query("SELECT * FROM agendamentos WHERE data_evento >= NOW() ORDER BY data_evento ASC LIMIT 3");
    $agenda_rapida = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) { $agenda_rapida = []; }


// --- 2. LÓGICA DO CALENDÁRIO COM EVENTOS DO BANCO ---

// Definir mês e ano
$mes_atual = isset($_GET['mes']) ? (int)$_GET['mes'] : date('n');
$ano_atual = isset($_GET['ano']) ? (int)$_GET['ano'] : date('Y');

// Navegação entre meses
$mes_ant = $mes_atual - 1; $ano_ant = $ano_atual;
if ($mes_ant < 1) { $mes_ant = 12; $ano_ant--; }

$mes_prox = $mes_atual + 1; $ano_prox = $ano_atual;
if ($mes_prox > 12) { $mes_prox = 1; $ano_prox++; }

// Dados do Calendário
$meses_nomes = [1 => 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
$nome_mes = $meses_nomes[$mes_atual];
$num_dias_mes = cal_days_in_month(CAL_GREGORIAN, $mes_atual, $ano_atual);
$primeiro_dia_sem = date('w', strtotime("$ano_atual-$mes_atual-01")); 
$dia_hoje = date('d');
$mes_real = date('n');
$ano_real = date('Y');

// [NOVO] Buscar dias que têm eventos neste mês específico para pintar no calendário
$dias_com_eventos = [];
try {
    $stmt_cal = $pdo->prepare("SELECT DISTINCT DAY(data_evento) as dia FROM agendamentos WHERE MONTH(data_evento) = :mes AND YEAR(data_evento) = :ano");
    $stmt_cal->execute([':mes' => $mes_atual, ':ano' => $ano_atual]);
    $dias_com_eventos = $stmt_cal->fetchAll(PDO::FETCH_COLUMN); // Retorna array simples: [5, 12, 19...]
} catch (Exception $e) {
    // Falha silenciosa para não quebrar a tela
}

include 'includes/header.php'; 
?>

<style>
    /* Estilos Gerais */
    .cal-nav-btn {
        text-decoration: none;
        color: var(--text-muted);
        padding: 5px 10px;
        border-radius: 5px;
        font-weight: bold;
        transition: 0.2s;
    }
    .cal-nav-btn:hover { background-color: var(--bg-color); color: var(--sidebar-active); }
    
    .feed-item {
        display: flex; gap: 15px; padding: 12px 0; border-bottom: 1px solid var(--border-color);
        align-items: flex-start;
    }
    .feed-item:last-child { border-bottom: none; }
    
    .feed-icon {
        min-width: 35px; height: 35px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 0.9rem;
    }

    /* Estilos Específicos do Calendário Novo */
    .calendar-grid {
        display: grid; 
        grid-template-columns: repeat(7, 1fr); 
        gap: 5px; 
        font-size: 0.85rem;
    }
    .calendar-day {
        padding: 6px;
        color: var(--text-color);
        border-radius: 8px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        position: relative;
        cursor: default;
    }
    /* Dia Atual */
    .day-today { 
        background-color: var(--sidebar-active) !important; 
        color: white !important; 
        box-shadow: 0 4px 6px rgba(139, 92, 246, 0.3);
        font-weight: 600;
    }
    /* Dia com Evento */
    .day-has-event {
        background-color: rgba(16, 185, 129, 0.1); /* Fundo verde bem clarinho */
        color: #065f46;
        font-weight: bold;
    }
    /* Bolinha indicadora de evento */
    .event-dot {
        width: 5px; 
        height: 5px; 
        background-color: #10b981; 
        border-radius: 50%; 
        margin-top: 2px;
    }
    /* Bolinha branca se for hoje */
    .day-today .event-dot { background-color: white; }

    @media (max-width: 768px) { .grid-container { grid-template-columns: 1fr !important; } }
</style>

<div style="margin-bottom: 20px;">
    <h2 style="margin-bottom: 5px;">Olá, Professor(a)!</h2>
    <p style="color: var(--text-muted); font-size: 0.9rem;"><?php echo date('d/m/Y'); ?> | Painel de Controle</p>
</div>

<div class="grid-container" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 20px;">
    
    <div class="stat-card" style="background: var(--card-bg); padding: 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 15px; border-left: 4px solid #8b5cf6;">
        <div class="stat-icon" style="background: #f5f3ff; color: #8b5cf6; width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
            <i class="fa-solid fa-chalkboard-user"></i>
        </div>
        <div class="stat-info">
            <h3 style="margin: 0; font-size: 1.5rem;"><?php echo $total_turmas; ?></h3>
            <p style="margin: 0; color: var(--text-muted); font-size: 0.9rem;">Turmas Ativas</p>
        </div>
    </div>

    <div class="stat-card" style="background: var(--card-bg); padding: 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 15px; border-left: 4px solid #10b981;">
        <div class="stat-icon" style="background: #ecfdf5; color: #10b981; width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
            <i class="fa-solid fa-calendar-check"></i>
        </div>
        <div class="stat-info">
            <h3 style="margin: 0; font-size: 1.5rem;"><?php echo $proximos_eventos_count; ?></h3>
            <p style="margin: 0; color: var(--text-muted); font-size: 0.9rem;">Eventos Futuros</p>
        </div>
    </div>

    <div class="stat-card" style="background: var(--card-bg); padding: 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 15px; border-left: 4px solid #f59e0b;">
        <div class="stat-icon" style="background: #fffbeb; color: #f59e0b; width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
            <i class="fa-solid fa-star"></i>
        </div>
        <div class="stat-info">
            <h3 style="margin: 0; font-size: 1.5rem;"><?php echo $media_geral; ?></h3>
            <p style="margin: 0; color: var(--text-muted); font-size: 0.9rem;">Média Geral</p>
        </div>
    </div>
</div>

<div class="grid-container" style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; align-items: start;">
    
    <div style="display: flex; flex-direction: column; gap: 20px;">
        
        <div class="card" style="background: var(--card-bg); padding: 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h3 style="margin: 0;"><i class="fa-solid fa-bolt" style="color: #f59e0b; margin-right: 8px;"></i> Aconteceu Recentemente</h3>
            </div>

            <?php if(count($feed) > 0): ?>
                <?php foreach($feed as $item): ?>
                    <div class="feed-item">
                        <div class="feed-icon" style="<?php echo ($item['tipo'] == 'quiz') ? 'background: rgba(139, 92, 246, 0.15); color: #8b5cf6;' : 'background: rgba(16, 185, 129, 0.15); color: #10b981;'; ?>">
                            <i class="fa-solid <?php echo ($item['tipo'] == 'quiz') ? 'fa-pen-clip' : 'fa-upload'; ?>"></i>
                        </div>
                        <div style="flex: 1;">
                            <?php if($item['tipo'] == 'quiz'): ?>
                                <span style="font-size: 0.9rem; color: var(--text-color);">
                                    <strong><?php echo htmlspecialchars($item['autor']); ?></strong> entregou 
                                    <span style="color: var(--sidebar-active);"><?php echo htmlspecialchars($item['extra']); ?></span>
                                </span>
                                <div style="font-size: 0.8rem; margin-top: 3px;">
                                    Nota: <strong style="color: <?php echo ($item['info']>=6) ? '#10b981' : '#ef4444'; ?>"><?php echo number_format($item['info'], 1); ?></strong>
                                </div>
                            <?php else: ?>
                                <span style="font-size: 0.9rem; color: var(--text-color);">
                                    <strong>Você</strong> postou novo material:
                                    <span style="color: var(--sidebar-active);"><?php echo htmlspecialchars($item['info']); ?></span>
                                </span>
                                <div style="font-size: 0.8rem; margin-top: 3px; color: var(--text-muted);">
                                    Tipo: <?php echo htmlspecialchars($item['extra']); ?>
                                </div>
                            <?php endif; ?>
                            <span style="font-size: 0.75rem; color: var(--text-muted); display: block; margin-top: 4px;">
                                <i class="fa-regular fa-clock"></i> <?php echo date('d/m H:i', strtotime($item['data'])); ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="text-align:center; color: var(--text-muted); padding: 20px;">Nenhuma atividade recente.</p>
            <?php endif; ?>
        </div>

        <?php
        $dicas_pedagogicas = [
            "🧠 <strong>Dinâmica:</strong> Peça para descreverem o humor hoje usando apenas uma cor.",
            "📚 <strong>Metodologia:</strong> Sala de Aula Invertida - envie o vídeo antes da aula.",
            "🎯 <strong>Foco:</strong> Técnica Pomodoro: 25min de foco, 5min de pausa.",
            "💡 <strong>Debate:</strong> Analisem uma notícia recente sob a ótica da psicologia."
        ];
        $dica_hoje = $dicas_pedagogicas[array_rand($dicas_pedagogicas)];
        ?>
        <div class="card" style="background: var(--card-bg); padding: 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="margin: 0; display:flex; align-items:center; gap: 10px;">
                    <span style="background: #e0e7ff; color: #4338ca; padding: 5px 10px; border-radius: 8px; font-size: 1.2rem;"><i class="fa-solid fa-lightbulb"></i></span>
                    Dica do Dia
                </h3>
            </div>
            <div style="background: linear-gradient(to right, #fdfbf7, #fff7ed); border-left: 4px solid #f59e0b; padding: 15px; border-radius: 4px; color: #78350f; font-size: 0.95rem;">
                <?php echo $dica_hoje; ?>
            </div>
        </div>

        <div class="card" style="background: #fff8c4; padding: 20px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); border-left: 5px solid #fcd34d; position: relative;">
            <div style="position: absolute; top: -10px; left: 50%; transform: translateX(-50%); width: 15px; height: 15px; background: #ef4444; border-radius: 50%; box-shadow: 0 2px 4px rgba(0,0,0,0.2);"></div>
            <h3 style="margin: 0 0 10px 0; font-size: 1rem; color: #92400e; display: flex; justify-content: space-between; align-items: center;">
                <span><i class="fa-solid fa-pen-to-square"></i> Notas Rápidas</span>
                <span id="saveStatus" style="font-size: 0.7rem; color: #b45309; opacity: 0; transition: opacity 0.5s;">Salvo!</span>
            </h3>
            <textarea id="stickyNote" placeholder="Anotações rápidas aqui..." style="width: 100%; height: 80px; background: transparent; border: none; outline: none; resize: none; color: #78350f; font-family: 'Inter', sans-serif; font-size: 0.95rem;"></textarea>
            <script>
                const noteArea = document.getElementById('stickyNote');
                const statusMsg = document.getElementById('saveStatus');
                if (localStorage.getItem('dashboard_sticky_note')) noteArea.value = localStorage.getItem('dashboard_sticky_note');
                noteArea.addEventListener('input', function() {
                    localStorage.setItem('dashboard_sticky_note', this.value);
                    statusMsg.style.opacity = '1'; setTimeout(() => { statusMsg.style.opacity = '0'; }, 2000);
                });
            </script>
        </div>

    </div>

    <div class="card" style="background: var(--card-bg); padding: 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); height: 100%;">
        
        <div style="text-align: center; margin-bottom: 25px; border-bottom: 1px solid var(--border-color); padding-bottom: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <a href="?mes=<?php echo $mes_ant; ?>&ano=<?php echo $ano_ant; ?>" class="cal-nav-btn">&lt;</a>
                <h4 style="margin: 0; text-transform: capitalize; color: var(--sidebar-active);">
                    <?php echo $nome_mes . ' ' . $ano_atual; ?>
                </h4>
                <a href="?mes=<?php echo $mes_prox; ?>&ano=<?php echo $ano_prox; ?>" class="cal-nav-btn">&gt;</a>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 5px; font-size: 0.8rem; margin-bottom: 8px; font-weight: bold;">
                <div style="color: var(--text-muted);">D</div><div style="color: var(--text-muted);">S</div><div style="color: var(--text-muted);">T</div>
                <div style="color: var(--text-muted);">Q</div><div style="color: var(--text-muted);">Q</div><div style="color: var(--text-muted);">S</div><div style="color: var(--text-muted);">S</div>
            </div>

            <div class="calendar-grid">
                <?php
                // Dias Vazios do Início
                for ($i = 0; $i < $primeiro_dia_sem; $i++) {
                    echo "<div></div>";
                }

                // Dias do Mês
                for ($dia = 1; $dia <= $num_dias_mes; $dia++) {
                    // Verifica se é hoje
                    $eh_hoje = ($dia == $dia_hoje && $mes_atual == $mes_real && $ano_atual == $ano_real);
                    
                    // Verifica se tem evento (busca no array que criamos lá em cima)
                    $tem_evento = in_array($dia, $dias_com_eventos);
                    
                    // Define as classes CSS
                    $classe = "calendar-day";
                    if ($eh_hoje) $classe .= " day-today";
                    elseif ($tem_evento) $classe .= " day-has-event";

                    echo "<div class='$classe'>";
                    echo $dia;
                    // Se tiver evento, coloca a bolinha (exceto se for hoje, aí a bolinha já é branca por CSS)
                    if ($tem_evento) {
                        echo "<div class='event-dot'></div>";
                    }
                    echo "</div>";
                }
                ?>
            </div>
        </div>

        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 20px;">
            <h3>Próximos Eventos</h3>
            <a href="planejador.php" style="font-size:0.8rem; color:var(--sidebar-active);">Ver tudo</a>
        </div>

        <?php if(count($agenda_rapida) > 0): ?>
            <div style="display: flex; flex-direction: column; gap: 15px;">
                <?php foreach($agenda_rapida as $evento): 
                    $data = date('d/m', strtotime($evento['data_evento']));
                    $hora = date('H:i', strtotime($evento['data_evento']));
                    $cor = ($evento['tipo'] == 'Administrativo') ? '#ef4444' : '#10b981';
                ?>
                <div style="display: flex; gap: 15px; align-items: center; padding-bottom: 15px; border-bottom: 1px solid var(--border-color);">
                    <div style="background-color: var(--bg-color); padding: 8px 12px; border-radius: 8px; text-align: center; min-width: 50px;">
                        <div style="font-weight: bold; font-size: 1rem;"><?php echo $data; ?></div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);"><?php echo $hora; ?></div>
                    </div>
                    <div>
                        <h4 style="font-size: 0.9rem; margin-bottom: 2px;"><?php echo htmlspecialchars($evento['titulo']); ?></h4>
                        <span style="font-size: 0.65rem; padding: 3px 8px; border-radius: 4px; background: <?php echo $cor; ?>20; color: <?php echo $cor; ?>;"><?php echo $evento['tipo']; ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p style="text-align: center; padding: 20px 0; color: var(--text-muted);">Nenhum evento próximo.</p>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>