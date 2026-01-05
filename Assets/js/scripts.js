// assets/js/scripts.js

document.addEventListener('DOMContentLoaded', () => {
    const themeBtn = document.getElementById('theme-btn');
    const body = document.body;
    const icon = themeBtn.querySelector('i');

    // 1. Verifica se já existe uma preferência salva
    const savedTheme = localStorage.getItem('psicohub_theme');

    if (savedTheme === 'dark') {
        body.classList.add('dark-mode');
        icon.classList.remove('fa-moon');
        icon.classList.add('fa-sun');
    }

    // 2. Função ao clicar no botão
    themeBtn.addEventListener('click', () => {
        body.classList.toggle('dark-mode');

        // Troca o ícone (Lua <-> Sol)
        if (body.classList.contains('dark-mode')) {
            icon.classList.remove('fa-moon');
            icon.classList.add('fa-sun');
            localStorage.setItem('psicohub_theme', 'dark'); // Salva preferência
        } else {
            icon.classList.remove('fa-sun');
            icon.classList.add('fa-moon');
            localStorage.setItem('psicohub_theme', 'light'); // Salva preferência
        }
    });

    console.log('PsicoHub: Scripts carregados e tema configurado.');
});