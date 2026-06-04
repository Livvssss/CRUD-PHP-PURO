<?php
session_start();
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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/landing.css">
</head>

<body>

    <!-- Envelope overlay (começa fechado, abre ao carregar, fecha ao navegar) -->
    <div class="envelope-overlay closed" id="envelopeOverlay">
        <div class="envelope-top"></div>
        <div class="envelope-body"></div>
    </div>

    <!-- Blobs decorativos -->
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>
    <div class="blob blob-3"></div>

    <!-- Cards flutuantes decorativos -->
    <div class="floating-card floating-card-1">
        <div class="card-icon">📚</div>
        Sua biblioteca pessoal
    </div>

    <div class="floating-card floating-card-2">
        <div class="card-icon">✍️</div>
        Reviews & Posts
    </div>

    <!-- Hero principal -->
    <section class="hero">

        <div class="badge">
            <span class="badge-dot"></span>
            Sistema de Biblioteca Pessoal
        </div>

        <h1 class="title">
            <span>ShelfHub</span>
        </h1>

        <p class="subtitle">
            Organize seus livros, autores e leituras em um só lugar. Simples, bonito e feito pra você.
        </p>

        <div class="actions">
            <a href="/login.php" class="btn-primary" id="loginBtn">
                Entrar na biblioteca
                <span class="arrow">→</span>
            </a>
        </div>

    </section>

    <p class="footer">© 2025 ShelfHub · Feito com 🧡</p>

    <script>
        const overlay = document.getElementById('envelopeOverlay');
        const loginBtn = document.getElementById('loginBtn');

        // Ao carregar: envelope abre (tampa sobe, corpo desce)
        window.addEventListener('DOMContentLoaded', () => {
            overlay.classList.remove('closed');
            overlay.classList.add('opening');

            // Remove o overlay do fluxo após abrir
            overlay.addEventListener('animationend', () => {
                overlay.style.display = 'none';
            }, { once: true });
        });

        // Ao clicar em Entrar: envelope fecha (tampa desce, corpo sobe) → redireciona
        loginBtn.addEventListener('click', (e) => {
            e.preventDefault();
            const dest = loginBtn.getAttribute('href');

            overlay.style.display = '';
            overlay.classList.remove('opening');
            overlay.classList.add('closing');

            overlay.addEventListener('animationend', () => {
                window.location.href = dest;
            }, { once: true });
        });
    </script>

</body>
</html>