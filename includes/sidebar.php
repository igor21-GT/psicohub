<div class="sidebar">
    <h2><i class="fa-solid fa-brain"></i>Agenda</h2>
    
    <a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
        <i class="fa-solid fa-house"></i> Inicial
    </a>
    
    <a href="minhas_turmas.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'minhas_turmas.php' ? 'active' : ''; ?>">
        <i class="fa-solid fa-users"></i> Minhas Turmas
    </a>
    
    <a href="planejador.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'planejador.php' ? 'active' : ''; ?>">
        <i class="fa-solid fa-calendar-days"></i> Planejador
    </a>

    <a href="perfil.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'perfil.php' ? 'active' : ''; ?>">
        <i class="fa-solid fa-user"></i> Meu Perfil
    </a>
    
    <a href="actions/logout.php" style="margin-top: auto; color: #ef4444;">
        <i class="fa-solid fa-right-from-bracket"></i> Sair
    </a>
</div>