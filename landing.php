<?php
// Inicia a sessão para verificar se o usuário já está logado
session_start();

// Se já estiver logado, redireciona para o dashboard (evita ver a landing page)
if (isset($_SESSION['usuario_id'])) {
    header('Location: /dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShelfHub</title>

    <!-- Fonte Poppins importada do Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Estilos específicos desta página -->
    <link rel="stylesheet" href="css/landing.css">
</head>

<body>

    <!--
        Envelope animado que cobre a tela inteira.
        Começa com a classe "closed" (fechado).
        O JS troca as classes para controlar abertura/fechamento.
        - .envelope-top  → tampa do envelope (parte de cima)
        - .envelope-body → corpo do envelope (parte de baixo)
    -->
    <div class="envelope-overlay closed" id="envelopeOverlay">
        <div class="envelope-top"></div>
        <div class="envelope-body"></div>
    </div>

    <!-- Formas orgânicas decorativas posicionadas via CSS -->
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>
    <div class="blob blob-3"></div>

    <!-- Cards decorativos com animação de flutuação (CSS) -->
    <div class="floating-card floating-card-1">
        <div class="card-icon">📚</div>
        Sua biblioteca pessoal
    </div>

    <div class="floating-card floating-card-2">
        <div class="card-icon">✍️</div>
        Reviews & Posts
    </div>

    <!-- Seção principal (Hero) -->
    <section class="hero">

        <!-- Etiqueta de categoria com ponto animado -->
        <div class="badge">
            <span class="badge-dot"></span>
            Sistema de Biblioteca Pessoal
        </div>

        <!-- Nome do produto -->
        <h1 class="title">
            <span>ShelfHub</span>
        </h1>

        <!-- Descrição curta do sistema -->
        <p class="subtitle">
            Organize seus livros, autores e leituras em um só lugar. Simples, bonito e feito pra você.
        </p>

        <!-- Botão de acesso — dispara a animação de fechar o envelope antes de navegar -->
        <div class="actions">
            <a href="/login.php" class="btn-primary" id="loginBtn">
                Entrar na biblioteca
                <span class="arrow">→</span>
            </a>
        </div>

    </section>

    <p class="footer">© 2025 ShelfHub · Feito com 🧡</p>

    <script>
        // Referências aos elementos controlados pelo JS
        const overlay  = document.getElementById('envelopeOverlay');
        const loginBtn = document.getElementById('loginBtn');

        // Quando a página termina de carregar: abre o envelope
        window.addEventListener('DOMContentLoaded', () => {
            overlay.classList.remove('closed');  // Remove estado inicial fechado
            overlay.classList.add('opening');    // Inicia animação de abertura (CSS)

            // Após a animação terminar, esconde o overlay completamente
            overlay.addEventListener('animationend', () => {
                overlay.style.display = 'none';
            }, { once: true }); // "once: true" garante que o listener roda só uma vez
        });

        // Ao clicar em "Entrar": fecha o envelope, depois redireciona
        loginBtn.addEventListener('click', (e) => {
            e.preventDefault(); // Bloqueia a navegação imediata do link

            const dest = loginBtn.getAttribute('href'); // Salva o destino (/login.php)

            overlay.style.display = '';        // Torna o overlay visível novamente
            overlay.classList.remove('opening'); // Remove classe de abertura
            overlay.classList.add('closing');    // Inicia animação de fechamento (CSS)

            // Só redireciona após a animação de fechamento terminar
            overlay.addEventListener('animationend', () => {
                window.location.href = dest;
            }, { once: true });
        });
    </script>

</body>
</html>