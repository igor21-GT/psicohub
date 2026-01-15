<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PsicoHub</title>
    <link rel="stylesheet" href="assets/css/style.css">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script src="assets/js/scripts.js" defer></script>
</head>
<body>

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <header>
            <h1>
                <?php 
                // Exibe título dinâmico ou padrão
                echo isset($page_title) ? $page_title : 'PsicoHub'; 
                ?>
            </h1>
            
            <div class="header-tools">
                <button id="theme-btn" class="theme-toggle" title="Mudar Tema">
                    <i class="fa-solid fa-moon"></i>
                </button>
            </div>
        </header>

        <div class="content-wrapper">