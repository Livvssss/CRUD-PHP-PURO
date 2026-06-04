<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}
$usuarioNome = $_SESSION['usuario_nome'];
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShelfHub | Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/dashboard.css">
</head>

<body>

    <!-- Envelope overlay (abre ao entrar na dashboard) -->
    <div class="envelope-overlay closed" id="envelopeOverlay">
        <div class="envelope-top"></div>
        <div class="envelope-body"></div>
    </div>

    <aside class="sidebar">
        <div class="sidebar-top">
            <div>
                <h1 class="logo"> ShelfHub </h1>
                <p class="logo-subtitle"> Sistema de Biblioteca </p>
            </div>

            <nav class="sidebar-menu">
                <a href="../dashboard.php" class="menu-item">Início</a>
                <a href="pages/authors.php" class="menu-item"> Autores </a>
                <a href="pages/shelves.php" class="menu-item"> Estantes </a>
                <a href="pages/reviews.php" class="menu-item"> Reviews </a>
                <a href="pages/posts.php" class="menu-item"> Posts </a>
            </nav>
        </div>

        <!-- LOGOUT -->
        <form action="logout.php" method="POST">
            <button type="submit" class="logout-btn"> Sair do Sistema </button>
        </form>
    </aside>

    <main class="main-content">
        <div class="topbar">
            <div class="welcome">
                <h1> Olá, <?= htmlspecialchars($usuarioNome) ?>!</h1>
                <p> Bem-vindo ao painel da biblioteca. </p>
            </div>

            <div class="profile-badge"> ShelfHub </div>
        </div>

        <div class="logo-container">
            <img src="img/logo.png" alt="Logo da Biblioteca" class="logo-image">
        </div>

        <section class="cards">

            <div class="card">
                <h2 class="card-title"> Autores </h2>
                <p class="card-text"> Organize os autores e suas obras cadastradas. </p>
            </div>

            <div class="card">
                <h2 class="card-title"> Estantes </h2>
                <p class="card-text"> Controle categorias e estantes da biblioteca. </p>
            </div>

            <div class="card">
                <h2 class="card-title"> Reviews </h2>
                <p class="card-text"> Visualize suas avaliações e comentários. </p>
            </div>

            <div class="card">
                <h2 class="card-title"> Posts </h2>
                <p class="card-text"> Gerencie todos os seus posts feitos. </p>
            </div>
        </section>
    </main>
    <script>
        const overlay = document.getElementById('envelopeOverlay');

        window.addEventListener('DOMContentLoaded', () => {
            overlay.classList.remove('closed');
            overlay.classList.add('opening');

            // Espera as duas animações (top + body) terminarem
            let count = 0;
            overlay.querySelectorAll('.envelope-top, .envelope-body')
                .forEach(el => {
                    el.addEventListener('animationend', () => {
                        count++;
                        if (count === 2) {
                            overlay.style.display = 'none';
                        }
                    }, {
                        once: true
                    });
                });
        });
    </script>
</body>

</html>