<?php
// get_eventos.php
require_once 'config/db.php';

header('Content-Type: application/json');

try {
    $stmt = $pdo->query("SELECT * FROM agendamentos");
    $eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $lista = [];

    foreach($eventos as $evt) {
        // Define cores baseadas no tipo de evento
        $cor = '#3b82f6'; // Azul padrão (Sessão)
        if ($evt['tipo'] == 'Administrativo') $cor = '#ef4444'; // Vermelho
        if ($evt['tipo'] == 'Avaliação') $cor = '#f59e0b'; // Laranja
        if ($evt['tipo'] == 'Sessão em Grupo') $cor = '#10b981'; // Verde

        $lista[] = [
            'id' => $evt['id'],
            'title' => $evt['titulo'],
            'start' => $evt['data_evento'], // FullCalendar aceita YYYY-MM-DD HH:MM:SS
            'backgroundColor' => $cor,
            'borderColor' => $cor
        ];
    }

    echo json_encode($lista);

} catch (Exception $e) {
    echo json_encode([]);
}
?>